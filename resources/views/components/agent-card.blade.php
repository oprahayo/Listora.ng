@props(['agent'])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-[#D7E2F4] bg-white p-4']) }} aria-labelledby="agent-card-title">
    <div class="flex items-center gap-3">
        <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-[#0A2856] text-base font-semibold text-white">{{ Str::of($agent->display_name)->explode(' ')->map(fn($part) => Str::substr($part, 0, 1))->take(2)->join('') }}</div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 id="agent-card-title" class="truncate text-[15px] font-semibold text-[#182230]">{{ $agent->display_name }}</h2>
                @if($agent->isVerified())<x-status-badge type="verified" icon="shield-check">Verified</x-status-badge>@endif
            </div>
            <p class="mt-1 truncate text-[13px] text-[#667085]">{{ $agent->primary_location }} property professional</p>
        </div>
    </div>
    <button type="button" disabled aria-disabled="true" title="Agent profile is currently unavailable" class="mt-3 inline-flex min-h-10 items-center gap-1 text-sm font-medium text-[#667085]">View profile <x-icon name="chevron-right" class="size-4" /></button>
</section>
