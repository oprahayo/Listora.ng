<?php

namespace App\Domain\Invitations;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\PhoneNormalizer;
use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Models\Invitation;
use App\Models\LandlordProfile;
use App\Models\OrganizationMember;
use App\Models\TenantProfile;
use App\Models\User;
use App\Notifications\AccountNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(
        private readonly InvitationDelivery $delivery,
        private readonly AuditLogger $audit,
    ) {}

    public function create(User $inviter, string $role, ?string $email, ?string $phone, ?string $name = null): Invitation
    {
        abort_unless(in_array($role, ['landlord', 'tenant', 'staff'], true), 422);
        [$email, $phone] = $this->normalizedContacts($email, $phone);
        $token = Str::random(64);

        $invitation = Invitation::query()->create([
            'organization_id' => $inviter->agent?->organization_id,
            'invited_by' => $inviter->id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'intended_role' => $role,
            'token_hash' => hash('sha256', $token),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);
        $this->audit->record('invitation_created', $inviter, $invitation, ['intended_role' => $role], $invitation->organization);
        $this->notifyExistingAccount($invitation);
        $this->delivery->send($invitation, route('invitations.show', ['token' => $token]));

        return $invitation;
    }

    public function resend(Invitation $invitation, User $actor): Invitation
    {
        $token = Str::random(64);
        $invitation->update([
            'token_hash' => hash('sha256', $token),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'accepted_by' => null,
        ]);
        $this->audit->record('invitation_resent', $actor, $invitation, ['intended_role' => $invitation->intended_role], $invitation->organization);
        $this->delivery->send($invitation, route('invitations.show', ['token' => $token]));

        return $invitation;
    }

    public function cancel(Invitation $invitation, User $actor): void
    {
        $invitation->update(['status' => 'cancelled']);
        $this->audit->record('invitation_cancelled', $actor, $invitation, [], $invitation->organization);
    }

    public function findByToken(string $token): Invitation
    {
        $invitation = Invitation::query()->where('token_hash', hash('sha256', $token))->firstOrFail();
        if ($invitation->status === 'pending' && $invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);
        }

        return $invitation->load(['inviter.agent', 'organization']);
    }

    public function accept(Invitation $invitation, User $user): Invitation
    {
        if ($invitation->status !== 'pending' || $invitation->expires_at->isPast()) {
            throw ValidationException::withMessages(['invitation' => 'This invitation is no longer available.']);
        }
        $matchesContact = ($invitation->email && str($user->email)->lower()->toString() === $invitation->email)
            || ($invitation->phone && $user->phone === $invitation->phone);
        if (! $matchesContact) {
            throw ValidationException::withMessages(['invitation' => 'Sign in with the email or phone number that received this invitation.']);
        }

        return DB::transaction(function () use ($invitation, $user): Invitation {
            $assignedRole = $invitation->intended_role === 'staff' ? 'agent' : $invitation->intended_role;
            $user->assignRole($assignedRole);
            $this->audit->record('role_assigned', $user, $user, ['role' => $assignedRole], $invitation->organization);
            if ($invitation->intended_role === 'landlord') {
                LandlordProfile::query()->firstOrCreate(['user_id' => $user->id]);
            }
            if ($invitation->intended_role === 'tenant') {
                TenantProfile::query()->firstOrCreate(['user_id' => $user->id]);
            }
            if ($invitation->intended_role === 'staff' && $invitation->organization_id) {
                OrganizationMember::query()->firstOrCreate(
                    ['organization_id' => $invitation->organization_id, 'user_id' => $user->id],
                    ['member_role' => 'staff', 'status' => 'active', 'joined_at' => now()],
                );
            }
            $invitation->update(['status' => 'accepted', 'accepted_at' => now(), 'accepted_by' => $user->id]);
            $this->audit->record('invitation_accepted', $user, $invitation, ['intended_role' => $invitation->intended_role], $invitation->organization);
            $invitation->inviter?->notify(new AccountNotification(
                'invitation_accepted',
                'Invitation accepted',
                $user->name.' accepted your invitation.',
                route('agent.invitations.index'),
            ));

            return $invitation->refresh();
        });
    }

    public function existingAccount(Invitation $invitation): ?User
    {
        return User::query()->where(function ($query) use ($invitation): void {
            if ($invitation->email) {
                $query->where('email', $invitation->email);
            }
            if ($invitation->phone) {
                $invitation->email ? $query->orWhere('phone', $invitation->phone) : $query->where('phone', $invitation->phone);
            }
        })->first();
    }

    private function notifyExistingAccount(Invitation $invitation): void
    {
        $this->existingAccount($invitation)?->notify(new AccountNotification(
            'invitation_received',
            'Invitation received',
            'You have been invited to join '.($invitation->organization?->name ?: 'an account').' on Listora.',
        ));
    }

    /** @return array{0: ?string, 1: ?string} */
    private function normalizedContacts(?string $email, ?string $phone): array
    {
        $email = $email ? Str::lower(trim($email)) : null;
        $phone = $phone ? PhoneNormalizer::normalize($phone) : null;
        if (($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) || (! $email && ! $phone)) {
            throw ValidationException::withMessages(['identifier' => 'Enter a valid email address or Nigerian phone number.']);
        }

        return [$email, $phone];
    }
}
