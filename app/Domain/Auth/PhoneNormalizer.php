<?php

namespace App\Domain\Auth;

final class PhoneNormalizer
{
    public static function normalize(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (str_starts_with($digits, '0')) {
            $digits = '234'.substr($digits, 1);
        } elseif (strlen($digits) === 10 && preg_match('/^[789]/', $digits)) {
            $digits = '234'.$digits;
        }

        return preg_match('/^234[789]\d{9}$/', $digits) ? $digits : null;
    }
}
