@props(['count' => 0])
@if((int) $count > 0)<span {{ $attributes->merge(['class' => 'absolute -right-0.5 -top-0.5 min-w-4 rounded-full bg-[#C92A2A] px-1 text-center text-[10px] font-medium leading-4 text-white']) }}>{{ min(99, (int) $count) }}</span>@endif
