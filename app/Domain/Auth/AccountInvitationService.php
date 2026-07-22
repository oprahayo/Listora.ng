<?php

namespace App\Domain\Auth;

use App\Models\AccountInvitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AccountInvitationService
{
    public function invite(
        string $role,
        ?string $email = null,
        ?string $phone = null,
        ?User $invitedBy = null,
        ?string $name = null,
    ): AccountInvitation {
        $roleModel = Role::named($role);
        $email = $email ? Str::lower(trim($email)) : null;
        $normalizedPhone = $phone ? PhoneNormalizer::normalize($phone) : null;

        if (($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) || ($phone && ! $normalizedPhone) || (! $email && ! $normalizedPhone)) {
            throw ValidationException::withMessages([
                'identifier' => 'Enter a valid email address or Nigerian phone number.',
            ]);
        }

        return DB::transaction(function () use ($roleModel, $email, $normalizedPhone, $invitedBy, $name): AccountInvitation {
            $matches = User::query()
                ->where(function ($query) use ($email, $normalizedPhone): void {
                    if ($email) {
                        $query->where('email', $email);
                    }

                    if ($normalizedPhone) {
                        $email ? $query->orWhere('phone', $normalizedPhone) : $query->where('phone', $normalizedPhone);
                    }
                })
                ->get();

            if ($matches->count() > 1) {
                throw ValidationException::withMessages([
                    'identifier' => 'Those contact details belong to different Listora accounts.',
                ]);
            }

            $user = $matches->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => $name ?: 'Invited Listora user',
                    'email' => $email,
                    'phone' => $normalizedPhone,
                    'password' => Hash::make(Str::random(64)),
                    'primary_role' => $roleModel->name,
                    'account_status' => 'pending',
                ]);
            }

            $user->assignRole($roleModel->name);

            $plainToken = Str::random(64);
            $invitation = AccountInvitation::query()->firstOrNew([
                'user_id' => $user->id,
                'role_id' => $roleModel->id,
                'status' => 'pending',
            ]);
            $invitation->fill([
                'invited_by' => $invitedBy?->id,
                'email' => $email,
                'phone' => $normalizedPhone,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(7),
            ])->save();

            return $invitation->load(['user.roles', 'role']);
        });
    }
}
