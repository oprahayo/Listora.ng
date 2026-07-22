<?php

namespace App\Domain\Invitations\Contracts;

use App\Models\Invitation;

interface InvitationDelivery
{
    public function send(Invitation $invitation, string $url): void;
}
