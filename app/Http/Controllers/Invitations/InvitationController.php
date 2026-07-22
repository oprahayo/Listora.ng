<?php

namespace App\Http\Controllers\Invitations;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\OtpService;
use App\Domain\Auth\PhoneNormalizer;
use App\Domain\Invitations\InvitationService;
use App\Http\Controllers\Controller;
use App\Models\AgentProfile;
use App\Models\LandlordProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Request $request, string $token, InvitationService $service): View
    {
        $invitation = $service->findByToken($token);
        $existingUser = $service->existingAccount($invitation);

        return view('invitations.show', compact('invitation', 'existingUser', 'token'));
    }

    public function accept(Request $request, string $token, InvitationService $service, OtpService $otp, AuditLogger $audit): RedirectResponse
    {
        $invitation = $service->findByToken($token);
        if ($request->user()) {
            $service->accept($invitation, $request->user());

            return redirect()->route('dashboard')->with('status', 'Invitation accepted.');
        }

        if ($service->existingAccount($invitation)) {
            throw ValidationException::withMessages(['invitation' => 'Sign in to accept this invitation.']);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);
        $phone = PhoneNormalizer::normalize($data['phone']);
        if (! $phone) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid Nigerian phone number.']);
        }
        if (User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages(['phone' => 'An account already exists with these details.']);
        }

        $user = DB::transaction(function () use ($data, $phone, $invitation, $audit): User {
            $role = $invitation->intended_role === 'staff' ? 'agent' : $invitation->intended_role;
            $user = User::query()->create([
                'name' => $data['name'], 'email' => str($data['email'])->lower(), 'phone' => $phone,
                'password' => $data['password'], 'primary_role' => $role, 'last_active_role' => $role, 'status' => 'pending',
            ]);
            $user->assignRole($role);
            if ($role === 'landlord') {
                LandlordProfile::query()->create(['user_id' => $user->id]);
            }
            if ($role === 'tenant') {
                TenantProfile::query()->create(['user_id' => $user->id]);
            }
            if ($role === 'agent') {
                AgentProfile::query()->create([
                    'user_id' => $user->id, 'display_name' => $user->name.' Properties',
                    'public_slug' => str($user->name)->slug().'-'.$user->id, 'account_type' => 'individual',
                    'verification_status' => 'unverified',
                ]);
            }
            $audit->record('registration', $user, $user, ['source' => 'invitation', 'initial_role' => $role]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put(['active_role' => $user->last_active_role, 'pending_invitation_token' => $token]);
        $user->sendEmailVerificationNotification();
        $otp->issue($user->phone, 'invitation_activation', $user);

        return redirect()->route('phone.verify')->with('status', 'Enter the code sent to your phone to accept the invitation.');
    }
}
