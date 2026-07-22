<?php

namespace Tests\Support;

use App\Domain\Auth\Contracts\OtpProvider;

class CapturingOtpProvider implements OtpProvider
{
    public array $messages = [];

    public function send(string $identifier, string $code, string $purpose): void
    {
        $this->messages[] = compact('identifier', 'code', 'purpose');
    }

    public function latestCode(): string
    {
        return $this->messages[array_key_last($this->messages)]['code'];
    }
}
