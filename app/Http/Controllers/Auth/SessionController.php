<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\DashboardResolver;
use App\Domain\Auth\PhoneNormalizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function store(LoginRequest $request, DashboardResolver $resolver): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $isEmail = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone';
        $identifier = $isEmail
            ? Str::lower($validated['identifier'])
            : PhoneNormalizer::normalize($validated['identifier']);

        $authenticated = Auth::attempt([
            $field => $identifier,
            'password' => $validated['password'],
        ], (bool) ($validated['remember'] ?? false));

        if (! $authenticated) {
            $message = 'We could not sign you in with those details.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The supplied credentials are invalid.',
                    'errors' => ['identifier' => [$message]],
                ], 422);
            }

            return back()->withErrors(['identifier' => $message])->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        /** @var User $user */
        $user = $request->user()->load('roles');

        if ($user->roles->isEmpty() || in_array($user->status, ['suspended', 'deactivated'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'We could not sign you in with those details.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The supplied credentials are invalid.',
                    'errors' => ['identifier' => [$message]],
                ], 422);
            }

            return back()->withErrors(['identifier' => $message])->withInput($request->except('password'));
        }

        $user->forceFill(['last_login_at' => now()])->save();

        if (! $user->phone_verified_at) {
            $redirect = route('phone.verify');

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Verify your phone to continue.', 'redirect' => $redirect]);
            }

            return redirect()->to($redirect);
        }

        $redirect = $resolver->initializeWorkspace(
            $user,
            $request->session(),
            $validated['intent'] ?? null,
            $validated['return_to'] ?? null,
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Signed in.', 'redirect' => $redirect]);
        }

        return redirect()->to($redirect);
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
