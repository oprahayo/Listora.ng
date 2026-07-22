<x-layouts.app>
    @section('title', 'Administration | Listora.ng')
    @section('app_title', 'Administration')
    <div class="mx-auto max-w-[1000px]">
        <x-dashboard-greeting />
        <section class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4" aria-label="Verification summary">
            <x-dashboard-stat label="Pending" :value="$counts['submitted'] ?? 0" />
            <x-dashboard-stat label="Under review" :value="$counts['under_review'] ?? 0" />
            <x-dashboard-stat label="Action required" :value="$counts['action_required'] ?? 0" />
            <x-dashboard-stat label="Approved today" :value="$approvedToday" />
        </section>
        <div class="mt-6 flex items-center justify-between"><h2 class="text-[17px] font-semibold">Recent submissions</h2><a href="{{ route('admin.verifications.index') }}" class="text-sm font-medium text-[#145FCC]">View queue</a></div>
        <div class="mt-3 grid gap-3">@forelse($recent as $item)<article class="rounded-xl border border-[#E4E7EC] bg-white p-4"><div class="flex items-start justify-between gap-3"><div><h3 class="font-semibold">{{ $item->user->agent?->display_name ?: $item->user->name }}</h3><p class="mt-1 text-sm text-[#667085]">{{ str($item->verification_type)->replace('_', ' ')->title() }} · {{ $item->submitted_at?->format('j M Y') }}</p></div><x-status-badge>{{ str($item->status)->headline() }}</x-status-badge></div><a href="{{ route('admin.verifications.show', $item) }}" class="mt-3 inline-flex min-h-11 items-center text-sm font-medium text-[#145FCC]">Review submission <x-icon name="chevron-right" class="ml-1 size-4" /></a></article>@empty<x-empty-state icon="shield-check" title="No submissions" message="New verification submissions will appear here." />@endforelse</div>
    </div>
</x-layouts.app>
