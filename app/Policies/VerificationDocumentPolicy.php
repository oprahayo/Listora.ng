<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VerificationDocument;

class VerificationDocumentPolicy
{
    public function view(User $user, VerificationDocument $document): bool
    {
        return $user->hasRole('admin') || $document->verificationRequest()->where('user_id', $user->id)->exists();
    }
}
