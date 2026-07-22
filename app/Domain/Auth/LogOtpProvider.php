<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Contracts\OtpProvider;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LogOtpProvider implements OtpProvider
{
    public function send(string $identifier, string $code, string $purpose): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('The local OTP provider cannot run in production.');
        }

        Log::info('Listora local OTP', compact('identifier', 'code', 'purpose'));
    }
}
