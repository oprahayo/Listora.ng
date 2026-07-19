@props(['icon', 'title', 'href', 'subtitle' => null])

<a href="{{ $href }}" class="group flex min-h-36 flex-col justify-between rounded-xl border border-[#D7E2F4] bg-white p-5 shadow-[0_8px_24px_rgba(11,42,91,0.05)] transition hover:-translate-y-0.5 hover:border-[#155EEF] hover:shadow-[0_12px_30px_rgba(11,42,91,0.10)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#155EEF]">
    <span class="flex size-12 items-center justify-center rounded-lg bg-[#EEF4FF] text-[#155EEF] transition group-hover:bg-[#155EEF] group-hover:text-white">
        <x-icon :name="$icon" class="size-7" />
    </span>
    <span>
        <span class="block text-base font-semibold text-[#172033]">{{ $title }}</span>
        @if($subtitle)<span class="mt-1 block text-sm text-[#667085]">{{ $subtitle }}</span>@endif
    </span>
</a>
