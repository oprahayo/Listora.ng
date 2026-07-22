<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'otp', 'code', 'code_hash', 'token', 'token_hash',
        'id_number', 'identity_data', 'document', 'document_path',
    ];

    public function record(
        string $event,
        ?User $user = null,
        ?Model $auditable = null,
        array $metadata = [],
        ?Organization $organization = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'organization_id' => $organization?->id,
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'metadata' => collect($metadata)->except(self::SENSITIVE_KEYS)->all() ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
