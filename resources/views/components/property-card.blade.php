@props(['property', 'view' => 'grid', 'compact' => false, 'eager' => false])

@php
    $cover = $property->images->firstWhere('is_cover', true) ?? $property->images->first();
    $webp = $cover?->thumbnail_path ?? '/images/properties/apartment-1-thumb.webp';
    $avif = preg_replace('/\.webp$/', '.avif', $webp);
    $jpeg = preg_replace('/\.webp$/', '.jpg', $webp);
    $imageVersion = @filemtime(public_path(ltrim($webp, '/'))) ?: 1;
@endphp

<article {{ $attributes->merge(['class' => 'property-card group overflow-hidden rounded-lg border border-[#E4E7EC] bg-white shadow-[0_1px_3px_rgba(10,40,86,0.08)] transition hover:border-[#C9D7EA] hover:shadow-[0_3px_10px_rgba(10,40,86,0.10)]']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-[#EAF2FF]">
        <a href="{{ route('properties.show', $property) }}" aria-label="View {{ $property->title }}">
            <picture>
                <source srcset="{{ $avif }}?v={{ $imageVersion }}" type="image/avif">
                <source srcset="{{ $webp }}?v={{ $imageVersion }}" type="image/webp">
                <img src="{{ $jpeg }}?v={{ $imageVersion }}" alt="{{ $cover?->alt_text ?? $property->title }}" width="640" height="400" @if($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async" class="h-full w-full object-cover transition duration-150 group-hover:scale-[1.01]">
            </picture>
        </a>
        <button type="button" @click="toggleSaved({{ $property->id }})" :aria-pressed="isSaved({{ $property->id }})" aria-label="Save {{ $property->title }}" class="absolute right-2 top-2 flex size-9 items-center justify-center rounded-full border border-white/70 bg-white/95 text-[#0A2856] shadow-sm transition hover:text-[#145FCC] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#145FCC] md:right-3 md:top-3 md:size-10">
            <x-icon name="heart" class="size-[18px] md:size-5" x-bind:fill="isSaved({{ $property->id }}) ? 'currentColor' : 'none'" />
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
                <p class="text-[18px] font-semibold tracking-tight text-[#0A2856] md:text-[19px]">{{ $property->formatted_rent }}</p>
                <h3 class="mt-1 line-clamp-2 text-[15px] font-medium leading-snug text-[#182230] md:text-[16px]">
                    <a href="{{ route('properties.show', $property) }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#145FCC]">{{ $property->title }}</a>
                </h3>
            </div>
            @if($property->agent->isVerified())
                <span title="Verified agent" class="mt-1 shrink-0 text-[#145FCC]"><x-icon name="shield-check" class="size-[18px]" /><span class="sr-only">Verified agent</span></span>
            @endif
        </div>
        <p class="mt-2 flex items-center gap-1 truncate text-[12px] text-[#667085] md:text-[13px]"><x-icon name="map-pin" class="size-3.5 shrink-0" /><span class="truncate">{{ $property->area }}, {{ $property->city }}</span></p>
        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 border-t border-[#EEF0F3] pt-2 text-[12px] text-[#475467] md:text-[13px]">
            @if($property->bedrooms)<span class="flex items-center gap-1"><x-icon name="bed" class="size-3.5" />{{ $property->bedrooms }} {{ Str::plural('Bed', $property->bedrooms) }}</span>@endif
            @if($property->bathrooms)<span class="flex items-center gap-1"><x-icon name="bath" class="size-3.5" />{{ $property->bathrooms }} {{ Str::plural('Bath', $property->bathrooms) }}</span>@endif
            @unless($compact)
                @foreach($property->amenities->take(2) as $amenity)
                    <span class="hidden rounded-md bg-[#F7F8FA] px-2 py-1 text-xs md:inline-flex">{{ $amenity->amenity_label }}</span>
                @endforeach
            @endunless
        </div>
    </div>
</article>
