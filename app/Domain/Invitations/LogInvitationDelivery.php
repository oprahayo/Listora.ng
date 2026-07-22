<?php

namespace App\Domain\Invitations;

use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Models\Invitation;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LogInvitationDelivery implements InvitationDelivery
{
    public function send(Invitation $invitation, string $url): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Configure an invitation delivery channel for production.');
        }
        Log::info('Listora local invitation', ['invitation_id' => $invitation->id, 'url' => $url]);
    }
}
