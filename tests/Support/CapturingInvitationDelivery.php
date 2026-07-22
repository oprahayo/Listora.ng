<?php

namespace Tests\Support;

use App\Domain\Invitations\Contracts\InvitationDelivery;
use App\Models\Invitation;

class CapturingInvitationDelivery implements InvitationDelivery
{
    public array $messages = [];

    public function send(Invitation $invitation, string $url): void
    {
        $this->messages[] = compact('invitation', 'url');
    }

    public function latestToken(): string
    {
        return basename(parse_url($this->messages[array_key_last($this->messages)]['url'], PHP_URL_PATH));
    }
}
