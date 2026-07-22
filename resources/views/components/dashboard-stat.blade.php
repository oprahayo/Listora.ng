@props(['label', 'value'])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-[#E4E7EC] bg-white p-3']) }}><p class="text-[22px] font-semibold text-[#0A2856]">{{ $value }}</p><p class="mt-1 text-xs text-[#667085]">{{ $label }}</p></div>
