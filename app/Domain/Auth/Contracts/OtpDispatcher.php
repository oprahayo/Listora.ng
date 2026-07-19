<?php

namespace App\Domain\Auth\Contracts;

interface OtpDispatcher
{
    public function request(string $identifier, string $role): void;
}
