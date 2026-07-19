<?php

namespace Tests\Unit;

use App\Domain\Auth\PhoneNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    #[DataProvider('phoneFormats')]
    public function test_it_normalizes_common_nigerian_phone_formats(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNormalizer::normalize($input));
    }

    public static function phoneFormats(): array
    {
        return [
            'local' => ['0809 123 4567', '2348091234567'],
            'international' => ['+234 809 123 4567', '2348091234567'],
            'compact international' => ['2348091234567', '2348091234567'],
            'ten digits' => ['8091234567', '2348091234567'],
        ];
    }

    public function test_it_rejects_non_nigerian_phone_numbers(): void
    {
        $this->assertNull(PhoneNormalizer::normalize('+44 20 7946 0958'));
        $this->assertNull(PhoneNormalizer::normalize('123'));
    }
}
