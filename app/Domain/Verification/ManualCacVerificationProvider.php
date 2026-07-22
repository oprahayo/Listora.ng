<?php

namespace App\Domain\Verification;

use App\Domain\Verification\Contracts\CacVerificationProvider;
use App\Models\Organization;

class ManualCacVerificationProvider implements CacVerificationProvider
{
    public function check(Organization $organization): array
    {
        return ['status' => 'pending', 'message' => 'Submitted for review by Listora.'];
    }
}
