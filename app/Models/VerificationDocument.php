<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'verification_request_id', 'document_type', 'original_filename', 'storage_path', 'mime_type',
    'size_bytes', 'checksum', 'status', 'rejection_reason',
])]
class VerificationDocument extends Model
{
    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }
}
