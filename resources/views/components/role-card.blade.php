@props(['icon', 'title', 'description'])

<button type="button" {{ $attributes->merge(['class' => 'group flex w-full items-center gap-4 rounded-xl border border-[#E4E7EC] bg-white p-4 text-left shadow-[0_1px_3px_rgba(10,40,86,.06)] transition hover:border-[#B8C8E1] hover:bg-[#F8FAFF] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#145FCC]']) }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon :name="$icon" class="size-5" /></span>
    <span class="min-w-0 flex-1">
        <span class="block text-[16px] font-semibold text-[#182230]">{{ $title }}</span>
        <span class="mt-1 block text-sm text-[#667085]">{{ $description }}</span>
    </span>
    <x-icon name="arrow-right" class="size-5 shrink-0 text-[#98A2B3] transition group-hover:translate-x-0.5 group-hover:text-[#145FCC]" />
</button>
