@props(['type' => 'neutral', 'icon' => null])

@php
    $styles = [
        'verified' => 'bg-[#EEF4FF] text-[#0B2A5B]',
        'available' => 'bg-[#EAF8F0] text-[#128A4B]',
        'reserved' => 'bg-[#FFF4E8] text-[#8A4B00]',
        'neutral' => 'bg-[#F2F4F7] text-[#475467]',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold '.$styles[$type]]) }}>
    @if($icon)<x-icon :name="$icon" class="size-3.5" />@endif
    {{ $slot }}
</span>
