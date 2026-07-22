<?php

namespace Tests\Feature;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\OtpService;
use App\Domain\Auth\PhoneNormalizer;
use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Domain\Invitations\InvitationService;
use App\Models\AgentProfile;
use App\Models\Organization;
use App\Models\OtpChallenge;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Models\VerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Tests\Support\CapturingInvitationDelivery;
use Tests\Support\CapturingOtpProvider;
use Tests\TestCase;

class SprintTwoAccountTest extends TestCase
{
    use RefreshDatabase;

    private CapturingOtpProvider $otpProvider;

    private CapturingInvitationDelivery $invitationDelivery;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->otpProvider = new CapturingOtpProvider;
        $this->invitationDelivery = new CapturingInvitationDelivery;
        $this->app->instance(OtpProvider::class, $this->otpProvider);
        $this->app->instance(InvitationDelivery::class, $this->invitationDelivery);
    }

    public function test_registration_creates_one_user_assigns_the_selected_role_and_normalizes_phone(): void
    {
        $this->post('/register', $this->registrationData())->assertRedirect(route('phone.verify'));

        $user = User::query()->where('email', 'new.agent@example.test')->firstOrFail();
        $this->assertSame('2348031234567', $user->phone);
        $this->assertTrue($user->hasRole('agent'));
        $this->assertSame(1, $user->roles()->count());
        $this->assertNotNull($user->agent);
        $this->assertSame('registration', $this->otpProvider->messages[0]['purpose']);
    }

    public function test_duplicate_phone_and_email_registration_are_rejected_without_creating_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.test', 'phone' => '2348031234567']);

        $this->from('/join')->post('/register', $this->registrationData(['email' => 'other@example.test']))
            ->assertRedirect('/join')->assertSessionHasErrors('phone');
        $this->from('/join')->post('/register', $this->registrationData(['email' => 'taken@example.test', 'phone' => '0804 555 6677']))
            ->assertRedirect('/join')->assertSessionHasErrors('email');
        $this->assertSame(1, User::query()->count());
    }

    public function test_nigerian_phone_normalization_accepts_common_formats(): void
    {
        $this->assertSame('2348031234567', PhoneNormalizer::normalize('0803 123 4567'));
        $this->assertSame('2348031234567', PhoneNormalizer::normalize('+234 803 123 4567'));
        $this->assertNull(PhoneNormalizer::normalize('1234'));
    }

    public function test_phone_otp_verifies_account_and_cannot_be_reused(): void
    {
        $this->post('/register', $this->registrationData());
        $user = User::query()->where('email', 'new.agent@example.test')->firstOrFail();
        $code = $this->otpProvider->latestCode();

        $this->actingAs($user)->withSession(['active_role' => 'agent'])
            ->post('/verify-phone/confirm', ['code' => $code])->assertRedirect(route('onboarding.index'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertSame('active', $user->fresh()->status);

        $this->post('/verify-phone/confirm', ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_otp_expiry_attempt_limit_and_request_limits_are_enforced(): void
    {
        $user = $this->roleUser('tenant');
        $service = app(OtpService::class);
        $service->issue($user->phone, 'phone_verification', $user);
        OtpChallenge::query()->latest('id')->first()->update(['expires_at' => now()->subSecond()]);
        $this->expectValidation(fn () => $service->verify($user->phone, 'phone_verification', '000000'), 'expired');

        $this->travel(61)->seconds();
        $service->issue($user->phone, 'phone_verification', $user);
        foreach (range(1, 5) as $attempt) {
            $this->expectValidation(fn () => $service->verify($user->phone, 'phone_verification', '000000'), 'not correct');
        }
        $this->expectValidation(fn () => $service->verify($user->phone, 'phone_verification', '000000'), 'Too many');

        $this->expectValidation(fn () => $service->issue($user->phone, 'phone_verification', $user), 'wait one minute');
        foreach (range(1, 3) as $request) {
            $this->travel(61)->seconds();
            $service->issue($user->phone, 'phone_verification', $user);
        }
        $this->travel(61)->seconds();
        $this->expectValidation(fn () => $service->issue($user->phone, 'phone_verification', $user), 'Too many code requests');
    }

    public function test_business_agent_onboarding_requires_cac_information_and_creates_an_organization(): void
    {
        Storage::fake('local');
        [$user] = $this->agentUser();
        $session = ['active_role' => 'agent'];
        $this->actingAs($user)->withSession($session)->post('/onboarding/agent', ['step' => 1, 'operation_type' => 'registered_business']);
        $this->withSession($session)->post('/onboarding/agent', ['step' => 2, 'display_name' => 'Clear Homes', 'public_slug' => 'clear-homes', 'operating_state' => 'Lagos', 'operating_city' => 'Yaba']);
        $this->withSession($session)->post('/onboarding/agent', ['step' => 3])->assertSessionHasErrors(['cac_registration_type', 'business_name', 'cac_registration_number']);

        $this->withSession($session)->post('/onboarding/agent', [
            'step' => 3, 'cac_registration_type' => 'limited_company', 'business_name' => 'Clear Homes Limited',
            'cac_registration_number' => 'RC123456', 'business_address' => '12 Herbert Macaulay Way, Yaba',
            'cac_certificate' => UploadedFile::fake()->create('cac.pdf', 300, 'application/pdf'),
        ])->assertRedirect(route('onboarding.agent', ['step' => 4]));

        $organization = Organization::query()->firstOrFail();
        $this->assertSame('RC123456', $organization->cac_registration_number);
        $this->assertSame('business', $user->agent->fresh()->account_type);
        $this->assertDatabaseHas('organization_members', ['organization_id' => $organization->id, 'user_id' => $user->id, 'member_role' => 'owner']);
    }

    public function test_individual_agent_onboarding_requires_id_and_keeps_identity_encrypted(): void
    {
        Storage::fake('local');
        [$user] = $this->agentUser();
        $session = ['active_role' => 'agent'];
        $this->actingAs($user)->withSession($session)->post('/onboarding/agent', ['step' => 1, 'operation_type' => 'individual_agent']);
        $this->withSession($session)->post('/onboarding/agent', ['step' => 3, 'government_id_type' => 'nin', 'id_number' => '12345678901'])->assertSessionHasErrors('government_id');
        $this->withSession($session)->post('/onboarding/agent', [
            'step' => 3, 'government_id_type' => 'nin', 'id_number' => '12345678901',
            'government_id' => UploadedFile::fake()->image('identity.jpg', 800, 500),
        ])->assertRedirect(route('onboarding.agent', ['step' => 4]));

        $verification = VerificationRequest::query()->firstOrFail();
        $this->assertSame('12345678901', $verification->identity_data['id_number']);
        $this->assertStringNotContainsString('12345678901', (string) $verification->getRawOriginal('identity_data'));
    }

    public function test_verification_files_are_private_and_unauthorized_users_cannot_download_them(): void
    {
        Storage::fake('local');
        [$owner] = $this->agentUser();
        $verification = VerificationRequest::query()->create(['user_id' => $owner->id, 'verification_type' => 'individual_agent']);
        Storage::disk('local')->put('verification/id.pdf', 'private document');
        $document = VerificationDocument::query()->create([
            'verification_request_id' => $verification->id, 'document_type' => 'government_id',
            'original_filename' => 'id.pdf', 'storage_path' => 'verification/id.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 16, 'checksum' => hash('sha256', 'private document'),
        ]);
        $url = URL::temporarySignedRoute('verification-documents.show', now()->addMinute(), $document);

        $this->actingAs($this->roleUser('tenant'))->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertOk()->assertHeader('cache-control', 'no-store, private');
        $this->assertFalse(Storage::disk('public')->exists('verification/id.pdf'));
    }

    public function test_agent_can_submit_and_admin_can_approve_all_verification_statuses(): void
    {
        [$agent, $profile] = $this->agentUser();
        $verification = VerificationRequest::query()->create([
            'user_id' => $agent->id, 'verification_type' => 'individual_agent', 'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $admin = $this->roleUser('admin');
        $this->actingAs($admin)->withSession(['active_role' => 'admin'])
            ->post(route('admin.verifications.approve', $verification), ['reviewer_note' => 'Checks completed.'])
            ->assertRedirect(route('admin.verifications.index'));

        $this->assertSame('approved', $verification->fresh()->status);
        $this->assertSame('verified', $profile->fresh()->verification_status);
        $this->assertNotNull($profile->fresh()->verified_at);
    }

    public function test_admin_correction_and_rejection_require_clear_messages(): void
    {
        [$agent] = $this->agentUser();
        $verification = VerificationRequest::query()->create(['user_id' => $agent->id, 'verification_type' => 'individual_agent', 'status' => 'submitted']);
        $admin = $this->roleUser('admin');
        $this->actingAs($admin)->withSession(['active_role' => 'admin'])
            ->post(route('admin.verifications.correction', $verification), ['reviewer_note' => 'Upload a clearer ID image.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('action_required', $verification->fresh()->status);

        $verification = $verification->fresh();
        $verification->update(['status' => 'submitted']);
        $this->post(route('admin.verifications.reject', $verification))->assertSessionHasErrors('reviewer_note');
        $this->assertSame('submitted', $verification->fresh()->status);
        $this->post(route('admin.verifications.reject', $verification), ['reviewer_note' => 'The identity details do not match.'])->assertRedirect();
        $this->assertSame('rejected', $verification->fresh()->status);
    }

    public function test_existing_account_invitation_never_creates_a_duplicate_user(): void
    {
        [$inviter] = $this->agentUser();
        $existing = $this->roleUser('tenant', ['email' => 'same@example.test', 'phone' => '2348071234567']);
        $count = User::query()->count();
        $invitation = app(InvitationService::class)->create($inviter, 'landlord', 'SAME@example.test', '0807 123 4567');

        $this->assertSame($count, User::query()->count());
        $this->assertFalse($existing->hasRole('landlord'));
        app(InvitationService::class)->accept($invitation, $existing);
        $this->assertTrue($existing->fresh()->hasRole('landlord'));
    }

    public function test_new_invitee_can_activate_one_account_and_invitation_token_cannot_be_reused(): void
    {
        [$inviter] = $this->agentUser();
        $invitation = app(InvitationService::class)->create($inviter, 'landlord', 'invitee@example.test', null, 'New Landlord');
        $token = $this->invitationDelivery->latestToken();

        $this->post(route('invitations.accept', $token), [
            'name' => 'New Landlord', 'email' => 'invitee@example.test', 'phone' => '0806 234 5678',
            'password' => 'safe-password', 'password_confirmation' => 'safe-password', 'terms' => '1',
        ])->assertRedirect(route('phone.verify'));
        $user = User::query()->where('email', 'invitee@example.test')->firstOrFail();
        $this->actingAs($user)->withSession(['active_role' => 'landlord', 'pending_invitation_token' => $token])
            ->post('/verify-phone/confirm', ['code' => $this->otpProvider->latestCode()])->assertRedirect(route('onboarding.index'));
        $this->assertTrue($user->fresh()->hasRole('landlord'));
        $this->assertSame('accepted', $invitation->fresh()->status);
        $this->assertSame(1, User::query()->where('email', 'invitee@example.test')->count());
        $this->expectValidation(fn () => app(InvitationService::class)->accept($invitation->fresh(), $user), 'no longer available');
    }

    public function test_invitation_expires_after_seven_days(): void
    {
        [$inviter] = $this->agentUser();
        $invitation = app(InvitationService::class)->create($inviter, 'tenant', 'late@example.test', null);
        $invitation->update(['expires_at' => now()->subSecond()]);
        app(InvitationService::class)->findByToken($this->invitationDelivery->latestToken());
        $this->assertSame('expired', $invitation->fresh()->status);
    }

    public function test_role_dashboards_reject_unassigned_users_and_multiple_role_switching_remains_safe(): void
    {
        $tenant = $this->roleUser('tenant');
        $this->actingAs($tenant)->withSession(['active_role' => 'tenant'])->get('/landlord/dashboard')->assertForbidden();
        $tenant->assignRole('landlord');
        $this->post('/workspace/switch', ['role' => 'landlord'])->assertRedirect(route('dashboard'));
        $this->get('/landlord/dashboard')->assertOk();
        $this->post('/workspace/switch', ['role' => 'agent'])->assertForbidden();
    }

    public function test_registration_role_choices_do_not_add_role_tabs_to_universal_login(): void
    {
        $this->get('/join')->assertOk()->assertSee('name="role"', false)->assertSee('Agent or Property Manager');
        $home = (string) $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('loginRole', $home);
        $this->assertStringNotContainsString('role="tablist"', $home);
        $this->assertStringNotContainsString('Choose account role', $home);
    }

    public function test_sensitive_onboarding_routes_are_network_only_and_not_publicly_cached(): void
    {
        $worker = file_get_contents(public_path('service-worker.js'));
        $this->assertStringContainsString("startsWith('/onboarding')", $worker);
        $this->assertStringContainsString("startsWith('/verification-documents')", $worker);
        $this->assertStringContainsString("startsWith('/invitations/')", $worker);
        $this->assertStringNotContainsString('id_number', $worker);
        $this->assertStringNotContainsString('government_id', $worker);
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'role' => 'agent', 'name' => 'New Agent', 'phone' => '0803 123 4567', 'email' => 'new.agent@example.test',
            'password' => 'safe-password', 'password_confirmation' => 'safe-password', 'terms' => '1',
        ], $overrides);
    }

    private function roleUser(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'primary_role' => $role, 'phone_verified_at' => now(), 'status' => 'active',
        ], $attributes));
    }

    /** @return array{0: User, 1: AgentProfile} */
    private function agentUser(): array
    {
        $user = $this->roleUser('agent');
        $profile = AgentProfile::query()->create([
            'user_id' => $user->id, 'display_name' => $user->name.' Properties',
            'public_slug' => str($user->name)->slug().'-'.$user->id, 'account_type' => 'individual',
            'verification_status' => 'unverified',
        ]);

        return [$user, $profile];
    }

    private function expectValidation(callable $callback, string $message): void
    {
        try {
            $callback();
            $this->fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsStringIgnoringCase($message, collect($exception->errors())->flatten()->join(' '));
        }
    }
}
