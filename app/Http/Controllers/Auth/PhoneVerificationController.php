<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\OtpService;
use App\Domain\Invitations\InvitationService;
use App\Http\Controllers\Controller;
use App\Notifications\AccountNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->phone_verified_at) {
            return redirect()->route('onboarding.index');
        }

        return view('auth.verify-phone');
    }

    public function request(Request $request, OtpService $otp): RedirectResponse
    {
        $purpose = $request->session()->has('pending_invitation_token') ? 'invitation_activation' : 'registration';
        $otp->issue($request->user()->phone, $purpose, $request->user());

        return back()->with('status', 'A new six-digit code has been sent.');
    }

    public function confirm(Request $request, OtpService $otp, AuditLogger $audit, InvitationService $invitations): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $pendingToken = $request->session()->get('pending_invitation_token');
        $otp->verify($request->user()->phone, $pendingToken ? 'invitation_activation' : 'registration', $validated['code']);

        $user = $request->user();
        $user->forceFill(['phone_verified_at' => now(), 'status' => 'active'])->save();
        $audit->record('phone_verified', $user, $user);
        $user->notify(new AccountNotification('phone_verified', 'Phone verified', 'Your phone number is now verified.'));

        if (is_string($pendingToken)) {
            $invitations->accept($invitations->findByToken($pendingToken), $user);
            $request->session()->forget('pending_invitation_token');
        }

        return redirect()->route('onboarding.index')->with('status', 'Phone verified.');
    }
}
