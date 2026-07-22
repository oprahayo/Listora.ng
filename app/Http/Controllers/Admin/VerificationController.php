<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\AgentProfile;
use App\Models\VerificationRequest;
use App\Notifications\AccountNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'submitted')->toString();
        $allowed = ['submitted', 'under_review', 'action_required', 'approved', 'rejected'];
        if (! in_array($status, $allowed, true)) {
            $status = 'submitted';
        }
        $requests = VerificationRequest::query()->with(['user.agent', 'organization'])
            ->where('status', $status)->latest('submitted_at')->paginate(15)->withQueryString();
        $counts = VerificationRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.verifications.index', compact('requests', 'counts', 'status'));
    }

    public function show(VerificationRequest $verificationRequest): View
    {
        Gate::authorize('review', $verificationRequest);
        $verificationRequest->load(['user.agent', 'organization', 'documents', 'reviewer']);
        $documentUrls = $verificationRequest->documents->mapWithKeys(fn ($document) => [
            $document->id => URL::temporarySignedRoute('verification-documents.show', now()->addMinutes(10), $document),
        ]);

        return view('admin.verifications.show', compact('verificationRequest', 'documentUrls'));
    }

    public function approve(Request $request, VerificationRequest $verificationRequest, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('review', $verificationRequest);
        $note = $request->validate(['reviewer_note' => ['nullable', 'string', 'max:1000']])['reviewer_note'] ?? null;
        $this->decide($request, $verificationRequest, 'approved', $note, $audit);

        return redirect()->route('admin.verifications.index')->with('status', 'Verification approved.');
    }

    public function correction(Request $request, VerificationRequest $verificationRequest, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('review', $verificationRequest);
        $data = $request->validate([
            'reviewer_note' => ['required', 'string', 'max:1000'],
            'rejected_documents' => ['nullable', 'array'],
            'rejected_documents.*' => [Rule::exists('verification_documents', 'id')->where('verification_request_id', $verificationRequest->id)],
        ]);
        if ($data['rejected_documents'] ?? []) {
            $verificationRequest->documents()->whereKey($data['rejected_documents'])->update([
                'status' => 'rejected', 'rejection_reason' => $data['reviewer_note'],
            ]);
        }
        $this->decide($request, $verificationRequest, 'action_required', $data['reviewer_note'], $audit);

        return back()->with('status', 'Correction request sent.');
    }

    public function reject(Request $request, VerificationRequest $verificationRequest, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('review', $verificationRequest);
        $note = $request->validate(['reviewer_note' => ['required', 'string', 'max:1000']])['reviewer_note'];
        $this->decide($request, $verificationRequest, 'rejected', $note, $audit);

        return redirect()->route('admin.verifications.index', ['status' => 'rejected'])->with('status', 'Verification rejected.');
    }

    private function decide(Request $request, VerificationRequest $verification, string $status, ?string $note, AuditLogger $audit): void
    {
        DB::transaction(function () use ($request, $verification, $status, $note, $audit): void {
            $verification->update([
                'status' => $status, 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id, 'reviewer_note' => $note,
            ]);
            $profileStatus = $status === 'approved' ? 'verified' : $status;
            $profile = AgentProfile::query()->where('user_id', $verification->user_id)->first();
            $profile?->update(['verification_status' => $profileStatus, 'verified_at' => $status === 'approved' ? now() : null]);
            $verification->organization?->update(['verification_status' => $profileStatus]);
            $audit->record('verification_reviewed', $request->user(), $verification, ['decision' => $status], $verification->organization);
            $event = match ($status) {
                'approved' => ['verification_approved', 'Verification approved', 'Your Listora verification has been approved.'],
                'action_required' => ['verification_action_required', 'Update needed', $note],
                default => ['verification_rejected', 'Verification not approved', $note],
            };
            $verification->user->notify(new AccountNotification($event[0], $event[1], (string) $event[2], route('agent.dashboard')));
        });
    }
}
