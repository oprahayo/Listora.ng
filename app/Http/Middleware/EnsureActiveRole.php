<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole($role), 403);

        $activeRole = $request->session()->get('active_role');

        if (! $activeRole && $user->roles()->count() === 1) {
            $activeRole = $role;
            $request->session()->put('active_role', $role);
        }

        abort_unless($activeRole === $role && $user->hasRole($activeRole), 403);

        return $next($request);
    }
}
