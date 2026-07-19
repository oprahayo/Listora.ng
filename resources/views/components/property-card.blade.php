@props(['property', 'view' => 'grid', 'compact' => false])

@php
    $cover = $property->images->firstWhere('is_cover', true) ?? $property->images->first();
    $webp = $cover?->thumbnail_path ?? '/images/properties/apartment-1-thumb.webp';
    $avif = preg_replace('/\.webp$/', '.avif', $webp);
    $jpeg = preg_replace('/\.webp$/', '.jpg', $webp);
@endphp

<article {{ $attributes->merge(['class' => 'property-card group overflow-hidden rounded-lg border border-[#E4E7EC] bg-white shadow-[0_3px_12px_rgba(11,42,91,0.04)] transition hover:border-[#C9D7EA] hover:shadow-[0_6px_18px_rgba(11,42,91,0.08)]']) }}>
    <div class="relative aspect-[3/2] overflow-hidden bg-[#EEF4FF]">
        <a href="{{ route('properties.show', $property) }}" aria-label="View {{ $property->title }}">
            <picture>
                <source srcset="{{ $avif }}" type="image/avif">
                <source srcset="{{ $webp }}" type="image/webp">
                <img src="{{ $jpeg }}" alt="{{ $cover?->alt_text ?? $property->title }}" width="720" height="480" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-150 group-hover:scale-[1.01]">
            </picture>
        </a>
        <button type="button" @click="toggleSaved({{ $property->id }})" :aria-pressed="isSaved({{ $property->id }})" aria-label="Save {{ $property->title }}" class="absolute right-2 top-2 flex size-9 items-center justify-center rounded-full border border-white/70 bg-white/95 text-[#0B2A5B] shadow-sm transition hover:text-[#155EEF] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#155EEF] md:right-3 md:top-3 md:size-10">
            <x-icon name="bookmark" class="size-[18px] md:size-5" x-bind:fill="isSaved({{ $property->id }}) ? 'currentColor' : 'none'" />
        </button>
        @if(! $compact && $property->availability_status !== 'available')
        <div class="absolute bottom-2 left-2 md:bottom-3 md:left-3">
            <x-status-badge type="reserved">
                {{ str($property->availability_status)->headline() }}
            </x-status-badge>
        </div>
        @endif
    </div>
    <div class="p-3 md:p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[17px] font-semibold tracking-tight text-[#0B2A5B] md:text-lg">{{ $property->formatted_rent }}</p>
                <h3 class="mt-1 line-clamp-2 text-[14px] font-semibold leading-snug text-[#172033] md:text-[15px]">
                    <a href="{{ route('properties.show', $property) }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#155EEF]">{{ $property->title }}</a>
                </h3>
            </div>
            @if($property->agent->isVerified())
                <span title="Verified agent" class="mt-1 shrink-0 text-[#155EEF]"><x-icon name="shield-check" class="size-[18px]" /><span class="sr-only">Verified agent</span></span>
            @endif
        </div>
        <p class="mt-2 flex items-center gap-1 text-[12px] text-[#667085] md:text-[13px]"><x-icon name="map-pin" class="size-3.5" />{{ $property->area }}, {{ $property->city }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 border-t border-[#EEF0F3] pt-2 text-[12px] text-[#475467] md:text-[13px]">
            @if($property->bedrooms)<span class="flex items-center gap-1"><x-icon name="bed" class="size-3.5" />{{ $property->bedrooms }} {{ Str::plural('Bed', $property->bedrooms) }}</span>@endif
            @if($property->bathrooms)<span class="flex items-center gap-1"><x-icon name="bath" class="size-3.5" />{{ $property->bathrooms }} {{ Str::plural('Bath', $property->bathrooms) }}</span>@endif
            @unless($compact)
                @foreach($property->amenities->take(2) as $amenity)
                    <span class="hidden rounded-md bg-[#F7F9FC] px-2 py-1 text-xs md:inline-flex">{{ $amenity->amenity_label }}</span>
                @endforeach
            @endunless
        </div>
    </div>
</article>
