<?php

namespace App\Http\Controllers\Invitations;

use App\Domain\Invitations\InvitationService;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentInvitationController extends Controller
{
    public function index(Request $request): View
    {
        $invitations = Invitation::query()->where('invited_by', $request->user()->id)->latest()->paginate(15);

        return view('invitations.agent-index', compact('invitations'));
    }

    public function store(Request $request, InvitationService $service): RedirectResponse
    {
        $data = $request->validate([
            'intended_role' => ['required', Rule::in(['landlord', 'tenant'])],
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $service->create($request->user(), $data['intended_role'], $data['email'] ?? null, $data['phone'] ?? null, $data['name'] ?? null);

        return back()->with('status', 'Invitation sent.');
    }

    public function resend(Request $request, Invitation $invitation, InvitationService $service): RedirectResponse
    {
        abort_unless($invitation->invited_by === $request->user()->id, 403);
        $service->resend($invitation, $request->user());

        return back()->with('status', 'Invitation resent.');
    }

    public function destroy(Request $request, Invitation $invitation, InvitationService $service): RedirectResponse
    {
        abort_unless($invitation->invited_by === $request->user()->id, 403);
        $service->cancel($invitation, $request->user());

        return back()->with('status', 'Invitation cancelled.');
    }
}
