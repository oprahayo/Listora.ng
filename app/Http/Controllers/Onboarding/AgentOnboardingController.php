<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Audit\AuditLogger;
use App\Domain\Profiles\SlugSuggester;
use App\Domain\Verification\Contracts\CacVerificationProvider;
use App\Http\Controllers\Controller;
use App\Models\AgentProfile;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\VerificationDocument;
use App\Models\VerificationRequest;
use App\Notifications\AccountNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AgentOnboardingController extends Controller
{
    private const OPERATIONS = ['individual_agent', 'registered_business', 'property_company', 'caretaker'];

    private const CAC_TYPES = ['business_name', 'limited_company', 'incorporated_trustee', 'other'];

    private const ID_TYPES = ['nin', 'drivers_licence', 'international_passport', 'voters_card', 'other'];

    public function show(Request $request, SlugSuggester $slugs): View|RedirectResponse
    {
        if (! $request->user()->phone_verified_at) {
            return redirect()->route('phone.verify');
        }

        $profile = AgentProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'display_name' => $request->user()->name.' Properties',
                'public_slug' => $slugs->unique($request->user()->name.' Properties'),
                'account_type' => 'individual',
                'verification_status' => 'unverified',
            ],
        );
        $verification = VerificationRequest::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'verification_type' => $profile->account_type === 'business' ? 'business_agent' : 'individual_agent'],
            ['organization_id' => $profile->organization_id, 'status' => 'draft'],
        );
        $step = max(1, min(4, (int) $request->integer('step', $verification->current_step)));
        $verification->load('documents');

        return view('onboarding.agent', compact('profile', 'verification', 'step'));
    }

    public function store(Request $request, SlugSuggester $slugs, AuditLogger $audit, CacVerificationProvider $cac): RedirectResponse
    {
        abort_unless($request->user()->phone_verified_at, 403);
        $profile = AgentProfile::query()->where('user_id', $request->user()->id)->firstOrFail();
        $step = max(1, min(4, $request->integer('step')));

        if ($request->string('direction')->toString() === 'back') {
            return redirect()->route('onboarding.agent', ['step' => max(1, $step - 1)]);
        }

        $verification = VerificationRequest::query()->where('user_id', $request->user()->id)
            ->whereIn('verification_type', ['individual_agent', 'business_agent'])->latest('id')->first();

        DB::transaction(function () use ($request, $profile, &$verification, $step, $slugs, $audit, $cac): void {
            if ($step === 1) {
                $data = $request->validate(['operation_type' => ['required', Rule::in(self::OPERATIONS)]]);
                $accountType = in_array($data['operation_type'], ['registered_business', 'property_company'], true) ? 'business' : 'individual';
                $profile->update(['operation_type' => $data['operation_type'], 'account_type' => $accountType]);
                $verification = $this->verificationFor($request, $verification, $accountType, $profile->organization_id);
            }

            if ($step === 2) {
                $data = $request->validate([
                    'display_name' => ['required', 'string', 'max:120'],
                    'public_slug' => ['nullable', 'string', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('agent_profiles')->ignore($profile)],
                    'operating_state' => ['required', 'string', 'max:80'],
                    'operating_city' => ['required', 'string', 'max:100'],
                    'short_bio' => ['nullable', 'string', 'max:300'],
                ]);
                $data['public_slug'] = $data['public_slug'] ?: $slugs->unique($data['display_name'], $profile);
                $profile->update($data);
                $verification = $this->verificationFor($request, $verification, $profile->account_type, $profile->organization_id);
            }

            if ($step === 3) {
                $verification = $this->verificationFor($request, $verification, $profile->account_type, $profile->organization_id);
                $profile->account_type === 'business'
                    ? $this->saveBusinessVerification($request, $profile, $verification, $audit, $cac)
                    : $this->saveIndividualVerification($request, $verification, $audit);
            }

            if ($step === 4) {
                $verification = $this->verificationFor($request, $verification, $profile->account_type, $profile->organization_id);
                if (! $verification->documents()->where('status', 'uploaded')->exists()) {
                    throw ValidationException::withMessages(['documents' => 'Upload the required verification document before submitting.']);
                }
                $verification->update(['status' => 'submitted', 'submitted_at' => now(), 'current_step' => 4]);
                $profile->update(['verification_status' => 'pending']);
                $profile->organization?->update(['verification_status' => 'pending']);
                $request->user()->forceFill(['onboarding_completed_at' => now()])->save();
                $audit->record('agent_onboarding_submitted', $request->user(), $verification, ['account_type' => $profile->account_type], $profile->organization);
                $request->user()->notify(new AccountNotification(
                    'verification_submitted',
                    'Verification submitted',
                    'We’ll notify you when your account has been reviewed.',
                    route('agent.dashboard'),
                ));
            }
        });

        if ($step === 4) {
            return redirect()->route('agent.dashboard')->with('status', 'Verification submitted. We’ll notify you when your account has been reviewed.');
        }

        $verification?->forceFill(['current_step' => min(4, $step + 1)])->save();

        if ($request->string('direction')->toString() === 'exit') {
            return redirect()->route('agent.dashboard')->with('status', 'Your progress has been saved.');
        }

        return redirect()->route('onboarding.agent', ['step' => min(4, $step + 1)]);
    }

    public function autosave(Request $request): JsonResponse
    {
        abort_unless($request->user()->phone_verified_at, 403);
        $profile = AgentProfile::query()->where('user_id', $request->user()->id)->firstOrFail();
        $verification = VerificationRequest::query()->where('user_id', $request->user()->id)
            ->whereIn('verification_type', ['individual_agent', 'business_agent'])->latest('id')->first()
            ?: VerificationRequest::query()->create([
                'user_id' => $request->user()->id,
                'verification_type' => $profile->account_type === 'business' ? 'business_agent' : 'individual_agent',
                'status' => 'draft',
            ]);
        $safe = $request->only([
            'operation_type', 'display_name', 'public_slug', 'operating_state', 'operating_city', 'short_bio',
            'cac_registration_type', 'business_name', 'business_address', 'government_id_type',
        ]);
        $verification->update([
            'current_step' => max(1, min(4, $request->integer('step', 1))),
            'draft_data' => array_merge($verification->draft_data ?: [], array_filter($safe, fn ($value) => $value !== null)),
        ]);
        if ($request->filled('id_number')) {
            $verification->update(['identity_data' => array_merge($verification->identity_data ?: [], [
                'id_type' => $request->string('government_id_type')->toString(),
                'id_number' => $request->string('id_number')->toString(),
            ])]);
        }

        return response()->json(['message' => 'Progress saved.']);
    }

    private function verificationFor(Request $request, ?VerificationRequest $verification, string $accountType, ?int $organizationId): VerificationRequest
    {
        $type = $accountType === 'business' ? 'business_agent' : 'individual_agent';
        if ($verification && $verification->verification_type !== $type && $verification->status === 'draft') {
            $verification->update(['verification_type' => $type, 'organization_id' => $organizationId]);

            return $verification->refresh();
        }

        return $verification ?: VerificationRequest::query()->create([
            'user_id' => $request->user()->id,
            'organization_id' => $organizationId,
            'verification_type' => $type,
            'status' => 'draft',
        ]);
    }

    private function saveBusinessVerification(Request $request, AgentProfile $profile, VerificationRequest $verification, AuditLogger $audit, CacVerificationProvider $cac): void
    {
        $data = $request->validate([
            'cac_registration_type' => ['required', Rule::in(self::CAC_TYPES)],
            'business_name' => ['required', 'string', 'max:150'],
            'cac_registration_number' => ['required', 'string', 'max:60'],
            'business_address' => ['required', 'string', 'max:500'],
            'cac_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'cac_status_report' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        if (! $request->hasFile('cac_certificate') && ! $verification->documents()->where('document_type', 'cac_certificate')->where('status', 'uploaded')->exists()) {
            throw ValidationException::withMessages(['cac_certificate' => 'Upload your CAC certificate.']);
        }

        $organization = $profile->organization ?: Organization::query()->create([
            'name' => $data['business_name'],
            'slug' => $this->uniqueOrganizationSlug($data['business_name']),
            'type' => 'business',
            'created_by' => $request->user()->id,
        ]);
        $organization->update([
            'name' => $data['business_name'],
            'cac_registration_type' => $data['cac_registration_type'],
            'cac_registration_number' => strtoupper($data['cac_registration_number']),
            'address' => $data['business_address'],
            'primary_email' => $request->user()->email,
            'primary_phone' => $request->user()->phone,
            'state' => $profile->operating_state,
            'city' => $profile->operating_city,
        ]);
        OrganizationMember::query()->firstOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $request->user()->id],
            ['member_role' => 'owner', 'status' => 'active', 'joined_at' => now()],
        );
        $profile->update(['organization_id' => $organization->id]);
        $verification->update(['organization_id' => $organization->id, 'verification_type' => 'business_agent']);
        $this->storeDocument($request->file('cac_certificate'), 'cac_certificate', $verification, $audit);
        $this->storeDocument($request->file('cac_status_report'), 'cac_status_report', $verification, $audit);
        $cac->check($organization);
    }

    private function saveIndividualVerification(Request $request, VerificationRequest $verification, AuditLogger $audit): void
    {
        $data = $request->validate([
            'government_id_type' => ['required', Rule::in(self::ID_TYPES)],
            'id_number' => ['required', 'string', 'max:80'],
            'government_id' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'proof_of_address' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'passport_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
        if (! $request->hasFile('government_id') && ! $verification->documents()->where('document_type', 'government_id')->where('status', 'uploaded')->exists()) {
            throw ValidationException::withMessages(['government_id' => 'Upload your government-issued ID.']);
        }

        $verification->update(['identity_data' => ['id_type' => $data['government_id_type'], 'id_number' => $data['id_number']]]);
        foreach (['government_id', 'proof_of_address', 'passport_photo'] as $type) {
            $this->storeDocument($request->file($type), $type, $verification, $audit);
        }
    }

    private function storeDocument(?UploadedFile $file, string $type, VerificationRequest $verification, AuditLogger $audit): void
    {
        if (! $file) {
            return;
        }
        $mime = $file->getMimeType();
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (! in_array($mime, $allowed, true) || ($mime !== 'application/pdf' && $file->getSize() > 5 * 1024 * 1024)) {
            throw ValidationException::withMessages([$type => 'Use a PDF, JPG or PNG. Images must be 5MB or smaller.']);
        }

        $verification->documents()->where('document_type', $type)->where('status', 'uploaded')
            ->update(['status' => 'rejected', 'rejection_reason' => 'Replaced by the account owner.']);
        $path = $file->store('verification/'.$verification->user_id.'/'.$verification->id, 'local');
        $document = VerificationDocument::query()->create([
            'verification_request_id' => $verification->id,
            'document_type' => $type,
            'original_filename' => basename($file->getClientOriginalName()),
            'storage_path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', Storage::disk('local')->path($path)),
            'status' => 'uploaded',
        ]);
        $audit->record('document_uploaded', request()->user(), $document, ['document_type' => $type]);
    }

    private function uniqueOrganizationSlug(string $name): string
    {
        $base = str($name)->slug()->toString() ?: 'organization';
        $slug = $base;
        $number = 2;
        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
