<x-layouts.app>
    @section('title', 'Landlord dashboard | Listora.ng')
    @section('app_title', 'Landlord')
    @php($pendingInvitation = $invitations->firstWhere('status', 'pending'))
    <div class="mx-auto max-w-[900px]">
        <x-dashboard-greeting />
        <x-dashboard-status-card class="mt-4" icon="users" title="Agent connection" :status="$pendingInvitation ? 'Pending' : null" :message="$pendingInvitation ? 'An agent invitation is waiting for you.' : 'No agent invitation needs your attention.'" />

        <section class="mt-5 rounded-xl border border-[#E4E7EC] bg-white p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs text-[#667085]">Connected properties</p><p class="mt-1 text-[24px] font-semibold text-[#0A2856]">0</p><p class="mt-1 text-sm text-[#667085]">No properties are connected yet.</p></div><span class="flex size-10 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="building" /></span></div></section>

        <section class="mt-6"><h2 class="text-[17px] font-semibold">Quick actions</h2><div class="mt-3 grid grid-cols-4 gap-2 sm:gap-3">
            <x-dashboard-action icon="building" label="Properties" :href="route('landlord.properties')" />
            <x-dashboard-action icon="users" label="Agents" :href="route('landlord.agents')" />
            <x-dashboard-action icon="check" label="Approvals" :href="route('landlord.approvals')" />
            <x-dashboard-action icon="document" label="Statements" :href="route('landlord.statements')" />
        </div></section>

        <section class="mt-6"><h2 class="text-[17px] font-semibold">Recent updates</h2><div class="mt-3 rounded-xl border border-[#E4E7EC] bg-white px-4">
            @forelse($invitations as $invitation)<div class="flex items-center justify-between gap-3 border-b border-[#E4E7EC] py-3 last:border-0"><div><p class="text-sm font-medium">Invitation from {{ $invitation->inviter?->name ?: 'Listora agent' }}</p><p class="text-xs text-[#667085]">{{ $invitation->created_at->diffForHumans() }}</p></div><x-status-badge>{{ str($invitation->status)->headline() }}</x-status-badge></div>
            @empty<p class="py-5 text-sm text-[#667085]">No recent updates.</p>@endforelse
        </div></section>
    </div>
</x-layouts.app>
