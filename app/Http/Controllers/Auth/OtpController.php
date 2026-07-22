<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\DashboardResolver;
use App\Domain\Auth\OtpService;
use App\Domain\Auth\PhoneNormalizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\OtpConfirmRequest;
use App\Http\Requests\OtpRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function store(OtpRequest $request, OtpService $otp): JsonResponse
    {
        $validated = $request->validated();
        $identifier = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL)
            ? Str::lower($validated['identifier'])
            : PhoneNormalizer::normalize($validated['identifier']);

        $user = User::query()
            ->where(filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone', $identifier)
            ->first();

        if ($user) {
            $otp->issue($identifier, 'login', $user);
        }

        return response()->json([
            'message' => 'If these details match an account, a six-digit code has been sent.',
        ]);
    }

    public function confirm(OtpConfirmRequest $request, OtpService $otp, DashboardResolver $resolver): JsonResponse
    {
        $data = $request->validated();
        $identifier = filter_var($data['identifier'], FILTER_VALIDATE_EMAIL)
            ? Str::lower($data['identifier'])
            : PhoneNormalizer::normalize($data['identifier']);

        $challenge = $otp->verify($identifier, 'login', $data['code']);
        $user = User::query()->findOrFail($challenge->user_id);
        abort_if(in_array($user->status, ['suspended', 'deactivated'], true), 403, 'This account is not available.');

        Auth::login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'message' => 'Signed in.',
            'redirect' => $resolver->initializeWorkspace($user->load('roles'), $request->session()),
        ]);
    }
}
