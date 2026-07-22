<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Contracts\OtpProvider;
use RuntimeException;

class ProductionOtpProvider implements OtpProvider
{
    public function send(string $identifier, string $code, string $purpose): void
    {
        throw new RuntimeException('Configure a production OTP provider before enabling OTP delivery.');
    }
}
