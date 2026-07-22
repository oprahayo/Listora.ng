<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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

        return view('dashboards.agent', compact('profile', 'verification', 'invitationCounts'));
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
}
