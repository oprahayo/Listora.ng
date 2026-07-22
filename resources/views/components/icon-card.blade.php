@props(['icon', 'title', 'href' => null, 'subtitle' => null, 'disabled' => false])

<a @unless($disabled)href="{{ $href }}"@endunless class="group flex min-h-20 flex-col items-center justify-center gap-2 rounded-lg border border-[#DFE7F3] bg-[#F7FAFF] p-2 text-center transition {{ $disabled ? 'opacity-60' : 'hover:border-[#9BB9EC] hover:bg-[#EAF2FF] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#145FCC]' }} md:min-h-24 md:gap-3 md:p-3" @if($disabled)aria-disabled="true" tabindex="-1"@endif>
    <span class="flex size-9 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC] transition group-hover:bg-[#DCE9FF] md:size-10">
        <x-icon :name="$icon" class="size-8 md:size-9" />
    </span>
    <span>
        <span class="block text-xs font-medium leading-tight text-[#344054] md:text-sm">{{ $title }}</span>
        @if($subtitle)<span class="mt-1 hidden text-xs text-[#667085] md:block">{{ $subtitle }}</span>@endif
    </span>
</a>
