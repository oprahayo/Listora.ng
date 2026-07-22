<?php

namespace App\Domain\Invitations;

use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Models\Invitation;
use RuntimeException;

class ProductionInvitationDelivery implements InvitationDelivery
{
    public function send(Invitation $invitation, string $url): void
    {
        throw new RuntimeException('Configure production email or SMS invitation delivery before sending invitations.');
    }
}
