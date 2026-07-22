<?php

namespace App\Domain\Navigation;

use App\Models\Role;
use App\Models\User;

final class WorkspaceNavigation
{
    /** @return array<int, array{label: string, icon: string, route: string, active: string}> */
    public function items(string $role): array
    {
        return match ($role) {
            'agent' => [
                ['label' => 'Home', 'icon' => 'home', 'route' => 'agent.dashboard', 'active' => 'agent.dashboard'],
                ['label' => 'Properties', 'icon' => 'building', 'route' => 'agent.properties.index', 'active' => 'agent.properties.*'],
                ['label' => 'People', 'icon' => 'users', 'route' => 'agent.invitations.index', 'active' => 'agent.invitations.*'],
                ['label' => 'More', 'icon' => 'grid', 'route' => 'agent.more', 'active' => 'agent.more'],
            ],
            'landlord' => [
                ['label' => 'Home', 'icon' => 'home', 'route' => 'landlord.dashboard', 'active' => 'landlord.dashboard'],
                ['label' => 'Properties', 'icon' => 'building', 'route' => 'landlord.properties', 'active' => 'landlord.properties'],
                ['label' => 'Reports', 'icon' => 'chart', 'route' => 'landlord.reports', 'active' => 'landlord.reports'],
                ['label' => 'More', 'icon' => 'grid', 'route' => 'landlord.more', 'active' => 'landlord.more'],
            ],
            'tenant' => [
                ['label' => 'Home', 'icon' => 'home', 'route' => 'tenant.dashboard', 'active' => 'tenant.dashboard'],
                ['label' => 'Bills', 'icon' => 'receipt', 'route' => 'tenant.bills', 'active' => 'tenant.bills'],
                ['label' => 'Support', 'icon' => 'chat', 'route' => 'tenant.support', 'active' => 'tenant.support'],
                ['label' => 'More', 'icon' => 'grid', 'route' => 'tenant.more', 'active' => 'tenant.more'],
            ],
            'admin' => [
                ['label' => 'Overview', 'icon' => 'home', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                ['label' => 'Verifications', 'icon' => 'shield-check', 'route' => 'admin.verifications.index', 'active' => 'admin.verifications.*'],
                ['label' => 'Users', 'icon' => 'users', 'route' => 'admin.users', 'active' => 'admin.users'],
                ['label' => 'More', 'icon' => 'grid', 'route' => 'admin.more', 'active' => 'admin.more'],
            ],
            default => [],
        };
    }

    public function activeRole(User $user, ?string $sessionRole): string
    {
        if (is_string($sessionRole) && in_array($sessionRole, Role::NAMES, true) && $user->hasRole($sessionRole)) {
            return $sessionRole;
        }

        $roles = $user->roles()->pluck('name')->all();
        abort_unless($roles !== [], 403);

        if (in_array($user->last_active_role, $roles, true)) {
            return $user->last_active_role;
        }

        if (in_array($user->primary_role, $roles, true)) {
            return $user->primary_role;
        }

        return $roles[0];
    }

    public function label(string $role): string
    {
        return match ($role) {
            'agent' => 'Agent',
            'landlord' => 'Landlord',
            'tenant' => 'Tenant',
            'admin' => 'Administration',
            default => 'Workspace',
        };
    }

    public function moreRoute(string $role): string
    {
        return match ($role) {
            'agent' => 'agent.more',
            'landlord' => 'landlord.more',
            'tenant' => 'tenant.more',
            'admin' => 'admin.more',
            default => abort(403),
        };
    }
}
