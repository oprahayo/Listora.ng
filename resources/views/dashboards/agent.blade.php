<x-layouts.public>
    @section('title', 'Agent dashboard | Listora.ng')
    <section class="dashboard-shell">
        @if(session('status'))<p class="mb-4 rounded-lg bg-[#EAF2FF] p-3 text-sm text-[#0A2856]">{{ session('status') }}</p>@endif
        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm text-[#667085]">Welcome back</p><h1 class="text-[27px] font-semibold text-[#182230]">{{ auth()->user()->name }}</h1></div>@if(auth()->user()->roles()->count()>1)<a href="{{ route('workspace.index') }}" class="btn-secondary">Switch workspace</a>@endif</div>
        <div class="mt-5 rounded-xl border border-[#D7E2F4] bg-white p-4 md:p-5">
            <div class="flex items-start gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="shield-check" /></span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><h2 class="text-lg font-semibold text-[#182230]">Verification</h2><x-status-badge :type="$profile?->verification_status==='verified'?'verified':'neutral'">{{ str($profile?->verification_status ?: 'unverified')->headline() }}</x-status-badge></div><p class="mt-1 text-sm text-[#667085]">{{ $profile?->verification_status === 'verified' ? 'Your public profile is verified.' : ($verification?->status === 'submitted' ? 'Your details are waiting for review.' : 'Complete your short setup to prepare your public profile.') }}</p></div></div>
            @if($profile?->verification_status !== 'verified' && $verification?->status !== 'submitted')<a href="{{ route('onboarding.agent') }}" class="btn-primary mt-4 w-full sm:w-auto">Continue verification</a>@endif
        </div>
        <h2 class="mt-6 text-xl font-semibold text-[#182230]">Quick actions</h2>
        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
            <x-icon-card icon="shield-check" title="Verification" :href="route('onboarding.agent')" />
            <x-icon-card icon="users" title="Invitations" :subtitle="($invitationCounts['pending'] ?? 0).' pending'" :href="route('agent.invitations.index')" />
            <x-icon-card icon="eye" title="Public Profile" :href="$profile ? route('agents.show', $profile) : route('onboarding.agent')" />
            <x-icon-card icon="settings" title="Settings" :href="route('notifications.index')" />
        </div>
        <h2 class="mt-6 text-xl font-semibold text-[#182230]">More tools</h2>
        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">@foreach([['building','Properties'],['users','Tenants'],['key','Payments'],['chat','Complaints']] as [$icon,$title])<x-icon-card :icon="$icon" :title="$title" subtitle="Available after setup" disabled />@endforeach</div>
    </section>
</x-layouts.public>
