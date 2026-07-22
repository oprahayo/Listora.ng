<?php

namespace App\Http\Controllers\Verification;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\VerificationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    public function show(Request $request, VerificationDocument $document, AuditLogger $audit): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        Gate::authorize('view', $document);
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);
        if ($request->user()->hasRole('admin')) {
            $audit->record('verification_document_viewed', $request->user(), $document, [
                'document_type' => $document->document_type,
            ], $document->verificationRequest->organization);
        }

        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type, 'Cache-Control' => 'private, no-store'],
        );
    }
}
