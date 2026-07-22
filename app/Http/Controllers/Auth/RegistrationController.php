<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\OtpService;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\AgentProfile;
use App\Models\LandlordProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('public.join');
    }

    public function store(RegisterRequest $request, OtpService $otp, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $audit): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => $data['password'],
                'primary_role' => $data['role'],
                'last_active_role' => $data['role'],
                'status' => 'pending',
            ]);
            $user->assignRole($data['role']);

            match ($data['role']) {
                'agent' => AgentProfile::query()->create([
                    'user_id' => $user->id,
                    'display_name' => $user->name.' Properties',
                    'public_slug' => str($user->name)->slug().'-'.$user->id,
                    'account_type' => 'individual',
                    'verification_status' => 'unverified',
                ]),
                'landlord' => LandlordProfile::query()->create(['user_id' => $user->id]),
                'tenant' => TenantProfile::query()->create(['user_id' => $user->id]),
            };

            $audit->record('registration', $user, $user, ['initial_role' => $data['role']]);
            $audit->record('role_assigned', $user, $user, ['role' => $data['role']]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('active_role', $data['role']);
        $user->sendEmailVerificationNotification();
        $otp->issue($user->phone, 'registration', $user);

        return redirect()->route('phone.verify')->with('status', 'We sent a six-digit code to your phone.');
    }
}
