<?php

namespace App\Domain\Auth\Contracts;

interface OtpProvider
{
    public function send(string $identifier, string $code, string $purpose): void;
}
