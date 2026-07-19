@props(['property', 'view' => 'grid'])

@php
    $cover = $property->images->firstWhere('is_cover', true) ?? $property->images->first();
    $webp = $cover?->thumbnail_path ?? '/images/properties/apartment-1-thumb.webp';
    $avif = preg_replace('/\.webp$/', '.avif', $webp);
    $jpeg = preg_replace('/\.webp$/', '.jpg', $webp);
@endphp

<article {{ $attributes->merge(['class' => 'property-card group overflow-hidden rounded-xl border border-[#E4E7EC] bg-white shadow-[0_6px_20px_rgba(11,42,91,0.05)] transition hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(11,42,91,0.10)]']) }}>
    <div class="relative aspect-[3/2] overflow-hidden bg-[#EEF4FF]">
        <a href="{{ route('properties.show', $property) }}" aria-label="View {{ $property->title }}">
            <picture>
                <source srcset="{{ $avif }}" type="image/avif">
                <source srcset="{{ $webp }}" type="image/webp">
                <img src="{{ $jpeg }}" alt="{{ $cover?->alt_text ?? $property->title }}" width="720" height="480" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-200 group-hover:scale-[1.015]">
            </picture>
        </a>
        <button type="button" @click="toggleSaved({{ $property->id }})" :aria-pressed="isSaved({{ $property->id }})" aria-label="Save {{ $property->title }}" class="absolute right-3 top-3 flex size-11 items-center justify-center rounded-full border border-white/70 bg-white/95 text-[#0B2A5B] shadow-sm transition hover:text-[#155EEF] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#155EEF]">
            <x-icon name="bookmark" class="size-5" x-bind:fill="isSaved({{ $property->id }}) ? 'currentColor' : 'none'" />
        </button>
        <div class="absolute bottom-3 left-3">
            <x-status-badge :type="$property->availability_status === 'available' ? 'available' : 'reserved'">
                {{ str($property->availability_status)->headline() }}
            </x-status-badge>
        </div>
    </div>
    <div class="p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-lg font-bold tracking-tight text-[#0B2A5B]">{{ $property->formatted_rent }}</p>
                <h3 class="mt-1 line-clamp-2 text-base font-semibold leading-snug text-[#172033]">
                    <a href="{{ route('properties.show', $property) }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#155EEF]">{{ $property->title }}</a>
                </h3>
            </div>
            @if($property->agent->isVerified())
                <span title="Verified agent" class="mt-1 shrink-0 text-[#155EEF]"><x-icon name="shield-check" class="size-5" /><span class="sr-only">Verified agent</span></span>
            @endif
        </div>
        <p class="mt-2 flex items-center gap-1.5 text-sm text-[#667085]"><x-icon name="map-pin" class="size-4" />{{ $property->area }}, {{ $property->city }}</p>
        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-[#EEF0F3] pt-3 text-sm text-[#475467]">
            @if($property->bedrooms)<span class="flex items-center gap-1.5"><x-icon name="bed" class="size-4" />{{ $property->bedrooms }} bed</span>@endif
            @if($property->bathrooms)<span class="flex items-center gap-1.5"><x-icon name="bath" class="size-4" />{{ $property->bathrooms }} bath</span>@endif
            @foreach($property->amenities->take(2) as $amenity)
                <span class="rounded-md bg-[#F7F9FC] px-2 py-1 text-xs">{{ $amenity->amenity_label }}</span>
            @endforeach
        </div>
    </div>
</article>
