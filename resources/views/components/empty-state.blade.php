@props(['icon' => 'search', 'title', 'message'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-[#CBD5E1] bg-white px-6 py-12 text-center']) }}>
    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-[#EAF2FF] text-[#145FCC]"><x-icon :name="$icon" class="size-6" /></span>
    <h2 class="mt-4 text-lg font-semibold text-[#182230]">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#667085]">{{ $message }}</p>
    @if(trim($slot))<div class="mt-5">{{ $slot }}</div>@endif
</div>
