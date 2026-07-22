<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Contracts\OtpDispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

final class LogOtpDispatcher implements OtpDispatcher
{
    public function request(string $identifier): void
    {
        $code = (string) random_int(100000, 999999);
        $key = 'otp:'.hash('sha256', Str::lower($identifier));

        Cache::put($key, password_hash($code, PASSWORD_DEFAULT), now()->addMinutes(5));
        Log::notice('Listora OTP dispatch', ['identifier' => $identifier, 'code' => $code]);
        RateLimiter::hit('otp-issued:'.hash('sha256', $identifier), 300);
    }
}
