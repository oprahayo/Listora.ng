<x-layouts.public>
    @section('title', 'Tenant dashboard | Listora.ng')
    <section class="dashboard-shell"><div class="flex flex-wrap justify-between gap-4"><div><p class="text-sm text-[#667085]">Tenant workspace</p><h1 class="text-[27px] font-semibold text-[#182230]">Hello, {{ auth()->user()->name }}</h1></div>@if(auth()->user()->roles()->count()>1)<a href="{{ route('workspace.index') }}" class="btn-secondary">Switch workspace</a>@endif</div>
        <div class="mt-5 rounded-xl border border-[#D7E2F4] bg-white p-4"><h2 class="text-lg font-semibold text-[#182230]">Property invitation status</h2><p class="mt-1 text-sm text-[#667085]">{{ $invitations->where('status','pending')->count() ? 'You have an invitation waiting.' : 'No invitation needs your attention.' }}</p><p class="mt-3 text-sm text-[#475467]">Contact preference: <span class="font-medium">{{ str($profile?->preferred_contact_method ?: 'app')->headline() }}</span></p></div>
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4"><x-icon-card icon="users" title="Invitations" :href="route('notifications.index')"/><x-icon-card icon="user" title="My Account" :href="route('onboarding.tenant')"/><x-icon-card icon="chat" title="Support" :href="route('notifications.index')"/><x-icon-card icon="settings" title="Settings" :href="route('onboarding.tenant', ['step'=>2])"/></div>
    </section>
</x-layouts.public>
