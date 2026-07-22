@php
    $workspace = match($role) {
        'agent' => ['Agent workspace', 'Manage listings, enquiries and property activity.', 'building'],
        'landlord' => ['Landlord workspace', 'Review properties, reports and approvals.', 'key'],
        'tenant' => ['Tenant workspace', 'Manage your tenancy, receipts and requests.', 'user'],
        'admin' => ['Administrator workspace', 'Manage the Listora platform and organizations.', 'shield-check'],
    };
    $heading = ($context ?? null) === 'properties' ? 'Property management' : $workspace[0];
@endphp
<x-layouts.public>
    @section('title', $heading.' | Listora.ng')
    <section class="mx-auto max-w-[1180px] px-4 py-8 sm:px-6 md:py-12 lg:px-0" aria-labelledby="dashboard-title">
        <div class="rounded-xl border border-[#D7E2F4] bg-white p-5 shadow-[0_3px_14px_rgba(10,40,86,.06)] md:p-7">
            <span class="flex size-11 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon :name="$workspace[2]" class="size-5" /></span>
            <h1 id="dashboard-title" class="mt-4 text-[27px] font-semibold tracking-tight text-[#182230] md:text-[32px]">{{ $heading }}</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-[#667085]">{{ $workspace[1] }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('properties.index') }}" class="btn-primary">Browse properties</a>
                @if(auth()->user()->roles()->count() > 1)<a href="{{ route('workspace.index') }}" class="btn-secondary">Switch workspace</a>@endif
            </div>
        </div>
    </section>
</x-layouts.public>
