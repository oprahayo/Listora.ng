<x-layouts.app>
    @section('title', 'Tenant dashboard | Listora.ng')
    @section('app_title', 'Tenant')
    <div class="mx-auto max-w-[900px]">
        <x-dashboard-greeting />
        <section class="mt-4 rounded-xl border border-[#D7E2F4] bg-white p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-medium text-[#145FCC]">Next payment</p><h2 class="mt-1 text-[18px] font-semibold">No active tenancy yet.</h2><p class="mt-1 max-w-lg text-sm leading-5 text-[#667085]">Your rent details will appear when a property is connected to your account.</p></div><span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="receipt" /></span></div></section>

        <section class="mt-6"><h2 class="text-[17px] font-semibold">Your account</h2><div class="mt-3 grid grid-cols-3 gap-2 sm:gap-3">
            <x-dashboard-action icon="receipt" label="Bills" :href="route('tenant.bills')" />
            <x-dashboard-action icon="document" label="Receipts" :href="route('tenant.receipts')" />
            <x-dashboard-action icon="alert-circle" label="Report Issue" :href="route('tenant.issues')" />
            <x-dashboard-action icon="chat" label="Chat" :href="route('tenant.chat')" />
            <x-dashboard-action icon="document" label="Documents" :href="route('tenant.documents')" />
            <x-dashboard-action icon="bell" label="Notices" :href="route('tenant.notices')" />
        </div></section>

        @if($invitations->where('status', 'pending')->isNotEmpty())<x-dashboard-status-card class="mt-6" icon="users" title="Property invitation" status="Pending" message="You have a property invitation waiting." />@endif
    </div>
</x-layouts.app>
