@props(['type' => 'neutral', 'icon' => null])

@php
    $styles = [
        'verified' => 'bg-[#EAF2FF] text-[#0A2856]',
        'available' => 'bg-[#EAF8F0] text-[#178344]',
        'reserved' => 'bg-[#FFF4E8] text-[#B75D00]',
        'neutral' => 'bg-[#F2F4F7] text-[#475467]',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium '.$styles[$type]]) }}>
    @if($icon)<x-icon :name="$icon" class="size-3.5" />@endif
    {{ $slot }}
</span>
