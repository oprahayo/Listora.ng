<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $role = $request->session()->get('active_role') ?: $request->user()->last_active_role;

        if (! $role || ! $request->user()->hasRole($role)) {
            return redirect()->route('workspace.index');
        }

        return match ($role) {
            'agent' => redirect()->route('onboarding.agent'),
            'landlord' => redirect()->route('onboarding.landlord'),
            'tenant' => redirect()->route('onboarding.tenant'),
            default => redirect()->route('dashboard'),
        };
    }
}
