<?php

namespace App\Domain\Auth;

use App\Models\User;
use Illuminate\Contracts\Session\Session;

final class DashboardResolver
{
    public function initializeWorkspace(
        User $user,
        Session $session,
        ?string $intent = null,
        ?string $returnTo = null,
    ): string {
        $roles = $user->roles()->pluck('name')->all();
        $session->forget(['active_role', 'login_intent', 'login_return_to']);

        $activeRole = match (true) {
            count($roles) === 1 => $roles[0],
            in_array($user->last_active_role, $roles, true) => $user->last_active_role,
            default => null,
        };

        if (! $activeRole) {
            return route('workspace.index');
        }

        $session->put('active_role', $activeRole);

        if ($user->last_active_role !== $activeRole) {
            $user->forceFill(['last_active_role' => $activeRole])->save();
        }

        if ($intent) {
            $session->put('login_intent', $intent);
        }

        if ($returnTo) {
            $session->put('login_return_to', $returnTo);
        }

        return route('dashboard');
    }

    public function destination(User $user, Session $session): string
    {
        $roles = $user->roles()->pluck('name')->all();
        $activeRole = $session->get('active_role');

        if (! in_array($activeRole, $roles, true)) {
            $activeRole = match (true) {
                count($roles) === 1 => $roles[0],
                in_array($user->last_active_role, $roles, true) => $user->last_active_role,
                default => null,
            };
        }

        if (! $activeRole) {
            $session->forget('active_role');

            return route('workspace.index');
        }

        $session->put('active_role', $activeRole);

        $returnTo = $session->pull('login_return_to');
        if (is_string($returnTo) && $this->returnToBelongsToRole($returnTo, $activeRole)) {
            return $returnTo;
        }

        $intent = $session->pull('login_intent');
        if ($intent === 'list-property' && $activeRole === 'agent') {
            return route('agent.properties.index');
        }

        return route($this->routeNameFor($activeRole));
    }

    public function switchTo(User $user, Session $session, string $role): string
    {
        abort_unless($user->hasRole($role), 403);

        $session->put('active_role', $role);
        $session->forget(['login_intent', 'login_return_to']);
        $user->forceFill(['last_active_role' => $role])->save();

        return route('dashboard');
    }

    public function routeNameFor(string $role): string
    {
        return match ($role) {
            'agent' => 'agent.dashboard',
            'landlord' => 'landlord.dashboard',
            'tenant' => 'tenant.dashboard',
            'admin' => 'admin.dashboard',
            default => abort(403),
        };
    }

    private function returnToBelongsToRole(string $returnTo, string $role): bool
    {
        if ($returnTo === '/dashboard') {
            return false;
        }

        return str_starts_with($returnTo, "/{$role}/");
    }
}
