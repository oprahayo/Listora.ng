<?php

namespace Tests\Feature;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CapturingOtpProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_without_a_role_field(): void
    {
        $user = $this->createRoleUser('tenant', [
            'email' => 'tenant@example.test',
            'phone' => '2348012345678',
        ]);

        $this->postJson('/auth/login', [
            'identifier' => 'TENANT@example.test',
            'password' => 'correct-password',
        ])->assertOk()->assertJsonPath('redirect', route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('tenant', session('active_role'));
    }

    public function test_unverified_phone_login_continues_to_phone_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.test',
            'phone_verified_at' => null,
            'status' => 'pending',
            'password' => 'password',
        ]);
        $user->assignRole('tenant');

        $this->postJson('/auth/login', [
            'identifier' => 'unverified@example.test',
            'password' => 'password',
        ])->assertOk()->assertJsonPath('redirect', route('phone.verify'));
    }

    #[DataProvider('roleDashboardProvider')]
    public function test_single_role_users_are_resolved_to_their_dashboard(string $role, string $routeName): void
    {
        $user = $this->createRoleUser($role, ['email' => "{$role}@example.test"]);

        $this->postJson('/auth/login', [
            'identifier' => "{$role}@example.test",
            'password' => 'correct-password',
        ])->assertOk()->assertJsonPath('redirect', route('dashboard'));

        $this->get('/dashboard')->assertRedirect(route($routeName));
        $this->get(route($routeName))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public static function roleDashboardProvider(): array
    {
        return [
            'agent' => ['agent', 'agent.dashboard'],
            'landlord' => ['landlord', 'landlord.dashboard'],
            'tenant' => ['tenant', 'tenant.dashboard'],
            'admin' => ['admin', 'admin.dashboard'],
        ];
    }

    public function test_common_nigerian_phone_format_is_normalized_for_login(): void
    {
        $user = $this->createRoleUser('agent', [
            'email' => null,
            'phone' => '2348091234567',
        ]);

        $this->postJson('/auth/login', [
            'identifier' => '0809 123 4567',
            'password' => 'correct-password',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('agent', session('active_role'));
    }

    public function test_invalid_credentials_use_the_same_generic_error(): void
    {
        $this->createRoleUser('agent', ['email' => 'agent@example.test']);

        $wrongPassword = $this->postJson('/auth/login', [
            'identifier' => 'agent@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('identifier');

        $unknownAccount = $this->postJson('/auth/login', [
            'identifier' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('identifier');

        $this->assertSame(
            $wrongPassword->json('errors.identifier.0'),
            $unknownAccount->json('errors.identifier.0'),
        );
        $this->assertSame('We could not sign you in with those details.', $wrongPassword->json('errors.identifier.0'));
        $this->assertGuest();
    }

    public function test_multiple_role_user_uses_the_last_workspace(): void
    {
        $user = $this->createRoleUser('tenant', [
            'email' => 'multi@example.test',
            'last_active_role' => 'landlord',
        ]);
        $user->assignRole('landlord');

        $this->postJson('/auth/login', [
            'identifier' => 'multi@example.test',
            'password' => 'correct-password',
        ])->assertOk()->assertJsonPath('redirect', route('dashboard'));

        $this->assertSame('landlord', session('active_role'));
        $this->get('/dashboard')->assertRedirect(route('landlord.dashboard'));
    }

    public function test_multiple_role_user_without_a_previous_workspace_sees_only_assigned_choices(): void
    {
        $user = $this->createRoleUser('tenant', ['email' => 'choose@example.test']);
        $user->assignRole('landlord');

        $this->postJson('/auth/login', [
            'identifier' => 'choose@example.test',
            'password' => 'correct-password',
        ])->assertOk()->assertJsonPath('redirect', route('workspace.index'));

        $this->get('/workspace')
            ->assertOk()
            ->assertSee('Choose a workspace')
            ->assertSee('value="tenant"', false)
            ->assertSee('value="landlord"', false)
            ->assertDontSee('value="agent"', false)
            ->assertDontSee('value="admin"', false);
    }

    public function test_user_can_switch_to_an_assigned_workspace_and_last_choice_is_saved(): void
    {
        $user = $this->createRoleUser('tenant');
        $user->assignRole('landlord');

        $this->actingAs($user)
            ->withSession(['active_role' => 'tenant'])
            ->post('/workspace/switch', ['role' => 'landlord'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame('landlord', session('active_role'));
        $this->assertSame('landlord', $user->fresh()->last_active_role);
        $this->get('/dashboard')->assertRedirect(route('landlord.dashboard'));
    }

    public function test_users_cannot_switch_to_or_open_roles_they_do_not_possess(): void
    {
        $user = $this->createRoleUser('tenant');

        $this->actingAs($user)
            ->withSession(['active_role' => 'tenant'])
            ->post('/workspace/switch', ['role' => 'agent'])
            ->assertForbidden();

        $this->get('/agent/dashboard')->assertForbidden();
        $this->get('/tenant/dashboard')->assertOk();
    }

    public function test_active_workspace_must_match_the_protected_route(): void
    {
        $user = $this->createRoleUser('tenant');
        $user->assignRole('landlord');

        $this->actingAs($user)
            ->withSession(['active_role' => 'tenant'])
            ->get('/landlord/dashboard')
            ->assertForbidden();
    }

    public function test_list_property_intent_never_assigns_agent_access(): void
    {
        $tenant = $this->createRoleUser('tenant', ['email' => 'intent@example.test']);

        $this->postJson('/auth/login', [
            'identifier' => 'intent@example.test',
            'password' => 'correct-password',
            'intent' => 'list-property',
        ])->assertOk();

        $this->assertFalse($tenant->hasRole('agent'));
        $this->get('/dashboard')->assertRedirect(route('tenant.dashboard'));
    }

    public function test_agent_list_property_intent_is_authorized_after_login(): void
    {
        $this->createRoleUser('agent', ['email' => 'listing-agent@example.test']);

        $this->postJson('/auth/login', [
            'identifier' => 'listing-agent@example.test',
            'password' => 'correct-password',
            'intent' => 'list-property',
        ])->assertOk();

        $this->get('/dashboard')->assertRedirect(route('agent.properties.index'));
    }

    public function test_public_return_locations_are_not_accepted_for_login(): void
    {
        $this->createRoleUser('tenant', ['email' => 'return@example.test']);

        $this->postJson('/auth/login', [
            'identifier' => 'return@example.test',
            'password' => 'correct-password',
            'return_to' => '/properties',
        ])->assertUnprocessable()->assertJsonPath('errors.return_to.0', 'The return location is invalid.');
    }

    public function test_existing_seed_users_keep_their_relationship_roles_and_can_log_in(): void
    {
        $this->seed();

        foreach ([
            'adaeze@listora.test' => 'agent',
            'tenant@listora.test' => 'tenant',
            'landlord@listora.test' => 'landlord',
            'admin@listora.test' => 'admin',
        ] as $email => $role) {
            $user = User::query()->where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($role));

            $this->postJson('/auth/login', [
                'identifier' => $email,
                'password' => 'password',
            ])->assertOk();

            $this->post('/auth/logout')->assertRedirect('/');
        }
    }

    public function test_otp_request_is_universal_and_does_not_require_a_role(): void
    {
        $provider = new CapturingOtpProvider;
        $this->app->instance(OtpProvider::class, $provider);
        $this->createRoleUser('tenant', ['phone' => '2348091234567']);

        $this->postJson('/auth/otp/request', [
            'identifier' => '0809 123 4567',
        ])->assertOk()->assertJsonPath('message', 'If these details match an account, a six-digit code has been sent.');
        $this->assertSame('2348091234567', $provider->messages[0]['identifier']);
        $this->assertSame('login', $provider->messages[0]['purpose']);
    }

    public function test_public_login_markup_has_no_role_selector(): void
    {
        $markup = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('Choose account role', $markup);
        $this->assertStringNotContainsString('loginRole', $markup);
        $this->assertStringNotContainsString('role="tablist"', $markup);
        $this->assertStringNotContainsString('name="role"', $markup);
    }

    public function test_login_is_rate_limited_without_a_role_field(): void
    {
        $identifier = 'limited@example.test';
        RateLimiter::clear(strtolower($identifier).'|127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/auth/login', [
                'identifier' => $identifier,
                'password' => 'incorrect-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/auth/login', [
            'identifier' => $identifier,
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    private function createRoleUser(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => "{$role}-".uniqid().'@example.test',
            'phone' => null,
            'password' => 'correct-password',
            'primary_role' => $role,
        ], $attributes));
    }
}
