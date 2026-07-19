<?php

namespace App\Domain\Shared;

final class Naira
{
    public static function annual(int|float|null $amount): string
    {
        return $amount ? '₦'.number_format($amount).'/year' : 'Price on request';
    }
}
