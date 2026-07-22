<?php

namespace App\Domain\Verification\Contracts;

use App\Models\Organization;

interface CacVerificationProvider
{
    /** @return array{status: string, message: string} */
    public function check(Organization $organization): array;
}
