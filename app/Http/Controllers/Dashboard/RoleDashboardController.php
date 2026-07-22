<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function agent(Request $request): View
    {
        $profile = $request->user()->agent;
        $verification = VerificationRequest::query()->where('user_id', $request->user()->id)->latest()->first();
        $invitationCounts = Invitation::query()->where('invited_by', $request->user()->id)->selectRaw('status, count(*) total')->groupBy('status')->pluck('total', 'status');
        $recentActivity = AuditLog::query()->where('user_id', $request->user()->id)
            ->whereIn('event', ['agent_onboarding_submitted', 'invitation_created', 'invitation_accepted', 'profile_updated'])
            ->latest()->take(4)->get();

        return view('dashboards.agent', compact('profile', 'verification', 'invitationCounts', 'recentActivity'));
    }

    public function landlord(Request $request): View
    {
        $profile = $request->user()->landlordProfile;
        $invitations = Invitation::query()->where('intended_role', 'landlord')->where(function ($query) use ($request): void {
            $query->where('email', $request->user()->email)->orWhere('phone', $request->user()->phone);
        })->latest()->take(5)->get();

        return view('dashboards.landlord', compact('profile', 'invitations'));
    }

    public function tenant(Request $request): View
    {
        $profile = $request->user()->tenantProfile;
        $invitations = Invitation::query()->where('intended_role', 'tenant')->where(function ($query) use ($request): void {
            $query->where('email', $request->user()->email)->orWhere('phone', $request->user()->phone);
        })->latest()->take(5)->get();

        return view('dashboards.tenant', compact('profile', 'invitations'));
    }

    public function admin(): View
    {
        $counts = VerificationRequest::query()->selectRaw('status, count(*) total')->groupBy('status')->pluck('total', 'status');
        $approvedToday = VerificationRequest::query()->where('status', 'approved')->whereDate('reviewed_at', today())->count();
        $recent = VerificationRequest::query()->with(['user.agent', 'organization'])->whereNotNull('submitted_at')->latest('submitted_at')->take(5)->get();

        return view('dashboards.admin', compact('counts', 'approvedToday', 'recent'));
    }
}
