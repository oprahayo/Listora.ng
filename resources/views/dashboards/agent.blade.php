<x-layouts.app>
    @section('title', 'Agent dashboard | Listora.ng')
    @section('app_title', 'Agent')
    @php
        $status = $profile?->verification_status ?: 'unverified';
        $statusMessage = match(true) {
            $status === 'verified' => 'Your agent profile is verified.',
            $status === 'action_required' => 'Update the requested information.',
            in_array($verification?->status, ['submitted', 'under_review'], true) || $status === 'pending' => 'Your verification is being reviewed.',
            default => 'Complete your profile to list properties.',
        };
        $statusAction = match(true) {
            $status === 'action_required' => 'Update information',
            in_array($status, ['verified', 'pending'], true) || in_array($verification?->status, ['submitted', 'under_review'], true) => null,
            default => 'Complete profile',
        };
        $propertyCount = $profile?->properties()->count() ?? 0;
        $pendingInvitations = (int) ($invitationCounts['pending'] ?? 0);
    @endphp
    <div class="mx-auto max-w-[900px]">
        @if(session('status'))<p class="mb-4 rounded-lg bg-[#EAF2FF] p-3 text-sm text-[#0A2856]">{{ session('status') }}</p>@endif
        <x-dashboard-greeting />
        <x-dashboard-status-card class="mt-4" icon="shield-check" title="Verification" :status="str($status)->headline()" :message="$statusMessage" :action="$statusAction" :href="$statusAction ? route('onboarding.agent') : null" />

        <section class="mt-6" aria-labelledby="agent-actions"><h2 id="agent-actions" class="text-[17px] font-semibold">Quick actions</h2><div class="mt-3 grid grid-cols-4 gap-2 sm:gap-3">
            <x-dashboard-action icon="building" label="Properties" :href="route('agent.properties.index')" />
            <x-dashboard-action icon="key" label="Landlords" :href="route('agent.landlords')" />
            <x-dashboard-action icon="users" label="Tenants" :href="route('agent.tenants')" />
            <x-dashboard-action icon="plus" label="Invitations" :href="route('agent.invitations.index')" :count="$pendingInvitations" />
        </div></section>

        <section class="mt-6" aria-labelledby="agent-summary"><h2 id="agent-summary" class="text-[17px] font-semibold">Summary</h2><div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
            <x-dashboard-stat label="Properties" :value="$propertyCount" />
            <x-dashboard-stat label="Available units" value="0" />
            <x-dashboard-stat label="Tenants" value="0" />
            <x-dashboard-stat label="Pending invitations" :value="$pendingInvitations" />
        </div></section>

        <section class="mt-6" aria-labelledby="agent-activity"><h2 id="agent-activity" class="text-[17px] font-semibold">Recent activity</h2><div class="mt-3 rounded-xl border border-[#E4E7EC] bg-white px-4">
            @forelse($recentActivity as $activity)<div class="flex items-center gap-3 border-b border-[#E4E7EC] py-3 last:border-0"><span class="flex size-8 items-center justify-center rounded-full bg-[#EAF2FF] text-[#145FCC]"><x-icon name="check" class="size-4" /></span><div><p class="text-sm font-medium">{{ match($activity->event) { 'agent_onboarding_submitted' => 'Verification submitted', 'invitation_created' => 'Invitation sent', 'invitation_accepted' => 'Invitation accepted', default => 'Profile updated' } }}</p><p class="text-xs text-[#667085]">{{ $activity->created_at->diffForHumans() }}</p></div></div>
            @empty<p class="py-5 text-sm text-[#667085]">No recent activity.</p>@endforelse
        </div></section>
    </div>
</x-layouts.app>
