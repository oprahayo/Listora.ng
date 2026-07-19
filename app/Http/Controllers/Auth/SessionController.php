<?php

namespace App\Http\Controllers\Auth;

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
    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $isEmail = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone';
        $identifier = $isEmail
            ? Str::lower($validated['identifier'])
            : PhoneNormalizer::normalize($validated['identifier']);

        $user = User::query()->where($field, $identifier)->where('role', $validated['role'])->first();

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $validated['password']], (bool) ($validated['remember'] ?? false))) {
            $message = 'We could not sign you in with those details and role.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The supplied credentials are invalid.',
                    'errors' => ['identifier' => [$message]],
                ], 422);
            }

            return back()->withErrors(['identifier' => $message])->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $redirect = $validated['return_to'] ?? route('home');

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
