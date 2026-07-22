<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\PhoneNormalizer;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\LandlordProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LandlordOnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()->phone_verified_at) {
            return redirect()->route('phone.verify');
        }
        $profile = LandlordProfile::query()->firstOrCreate(['user_id' => $request->user()->id]);
        $step = max(1, min(2, $request->integer('step', 1)));
        $invitation = $this->invitation($request);

        return view('onboarding.landlord', compact('profile', 'step', 'invitation'));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $profile = LandlordProfile::query()->firstOrCreate(['user_id' => $request->user()->id]);
        $step = max(1, min(2, $request->integer('step')));
        if ($request->string('direction')->toString() === 'back') {
            return redirect()->route('onboarding.landlord', ['step' => 1]);
        }

        if ($step === 1) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($request->user())],
                'phone' => ['required', function ($attribute, $value, $fail): void {
                    if (! PhoneNormalizer::normalize($value)) {
                        $fail('Enter a valid Nigerian phone number.');
                    }
                }, Rule::unique('users')->ignore($request->user())],
            ]);
            $request->user()->update(['name' => $data['name'], 'email' => str($data['email'])->lower(), 'phone' => PhoneNormalizer::normalize($data['phone'])]);

            return redirect()->route('onboarding.landlord', ['step' => 2]);
        }

        $data = $request->validate([
            'preferred_contact_method' => ['required', Rule::in(['app', 'whatsapp', 'sms', 'email'])],
            'invited' => ['required', 'boolean'],
        ]);
        $profile->update(['preferred_name' => $request->user()->name, 'preferred_contact_method' => $data['preferred_contact_method']]);
        $request->user()->forceFill(['onboarding_completed_at' => now()])->save();
        $audit->record('landlord_onboarding_completed', $request->user(), $profile, ['invited' => (bool) $data['invited']]);

        return redirect()->route('landlord.dashboard')->with('status', 'Your account is ready.');
    }

    private function invitation(Request $request): ?Invitation
    {
        return Invitation::query()->where('intended_role', 'landlord')->where('status', 'pending')
            ->where(fn ($query) => $query->where('email', str($request->user()->email)->lower())->orWhere('phone', $request->user()->phone))
            ->latest()->first();
    }
}
