<?php

namespace App\Domain\Properties;

final class PropertyTypes
{
    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'apartment' => 'Apartments',
            'self-contain' => 'Self Contain',
            'duplex' => 'Duplexes',
            'shared-flat' => 'Shared Flats',
            'shop' => 'Shops',
            'office' => 'Offices',
        ];
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::options());
    }
}
