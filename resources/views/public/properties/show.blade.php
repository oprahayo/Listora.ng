@php
    $cover = $property->images->firstWhere('is_cover', true) ?? $property->images->first();
    $ogImage = $cover ? url($cover->image_path) : null;
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => $property->title,
        'description' => Str::limit(strip_tags($property->description), 190),
        'url' => route('properties.show', $property),
        'datePosted' => optional($property->published_at)->toDateString(),
        'image' => $ogImage ? [$ogImage] : [],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $property->city,
            'addressRegion' => $property->state,
            'addressCountry' => 'NG',
        ],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'NGN',
            'price' => $property->annual_rent,
            'availability' => $property->availability_status === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/LimitedAvailability',
        ],
    ];
@endphp

<x-layouts.public>
    @section('title', $property->title.' | Listora.ng')
    @section('meta_description', Str::limit(strip_tags($property->description), 155))
    @section('canonical', route('properties.show', $property))
    @section('og_type', 'product')
    @section('og_title', $property->title.' — '.$property->formatted_rent)
    @if($ogImage) @section('og_image', $ogImage) @endif
    @push('head')<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>@endpush

    <div class="mx-auto max-w-[1180px] px-4 pb-28 pt-3 sm:px-6 md:pb-12 lg:px-0 lg:pt-5">
        <a href="{{ route('properties.index') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-[#475467] hover:text-[#145FCC]"><x-icon name="arrow-left" class="size-4" />Back to listings</a>

        <div class="mt-2 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start lg:gap-8">
            <article class="min-w-0">
                <div class="flex snap-x snap-mandatory gap-2 overflow-x-auto rounded-xl [scrollbar-width:none] [&::-webkit-scrollbar]:hidden md:grid md:grid-cols-[2fr_1fr] md:grid-rows-2 md:overflow-hidden" aria-label="Property images">
                    @foreach($property->images->take(3) as $image)
                        @php $avif = preg_replace('/\.webp$/', '.avif', $image->image_path); $jpeg = preg_replace('/\.webp$/', '.jpg', $image->image_path); $imageVersion = @filemtime(public_path(ltrim($image->image_path, '/'))) ?: 1; @endphp
                        <figure class="relative aspect-[16/10] min-w-full snap-center overflow-hidden bg-[#EAF2FF] md:aspect-auto md:min-w-0 {{ $loop->first ? 'md:row-span-2 md:min-h-[440px]' : 'md:min-h-0' }} {{ $property->images->count() === 2 && ! $loop->first ? 'md:row-span-2' : '' }}">
                            <picture><source srcset="{{ $avif }}?v={{ $imageVersion }}" type="image/avif"><source srcset="{{ $image->image_path }}?v={{ $imageVersion }}" type="image/webp"><img src="{{ $jpeg }}?v={{ $imageVersion }}" alt="{{ $image->alt_text }}" width="960" height="600" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async" class="absolute inset-0 h-full w-full object-cover transition hover:scale-[1.01]"></picture>
                        </figure>
                    @endforeach
                    @if($property->images->count() < 2)<div class="hidden min-h-52 bg-[#EAF2FF] md:block"></div>@endif
                </div>

                <section class="mt-5" aria-labelledby="property-title">
                    <div class="flex flex-wrap items-center gap-2"><x-status-badge :type="$property->availability_status === 'available' ? 'available' : 'reserved'">{{ str($property->availability_status)->headline() }}</x-status-badge><x-status-badge>{{ $property->type_label }}</x-status-badge></div>
                    <p class="mt-3 text-[18px] font-semibold tracking-tight text-[#0A2856] lg:hidden">{{ $property->formatted_rent }}</p>
                    <h1 id="property-title" class="mt-1 text-[23px] font-semibold leading-tight tracking-tight text-[#182230] md:text-[28px]">{{ $property->title }}</h1>
                    <p class="mt-2 flex items-center gap-1.5 text-[13px] text-[#667085] md:text-sm"><x-icon name="map-pin" class="size-4 shrink-0 text-[#145FCC]" />{{ $property->display_address }}</p>
                </section>

                <section class="mt-5 flex flex-wrap gap-x-4 gap-y-3 border-y border-[#E4E7EC] py-3" aria-label="Property facts">
                    @foreach([
                        ['bed', $property->bedrooms ? $property->bedrooms.' Beds' : null],
                        ['bath', $property->bathrooms ? $property->bathrooms.' Baths' : null],
                        ['car', $property->parking_spaces !== null ? $property->parking_spaces.' Parking' : null],
                        ['ruler', $property->size_sqm ? number_format((float)$property->size_sqm).' m²' : null],
                    ] as [$icon, $fact]) @if($fact)<div class="flex items-center gap-1.5 text-[13px] font-medium text-[#344054]"><x-icon :name="$icon" class="size-4 shrink-0 text-[#145FCC]" /><span>{{ $fact }}</span></div>@endif @endforeach
                </section>

                <section class="mt-6" aria-labelledby="description-title"><h2 id="description-title" class="text-[20px] font-semibold text-[#182230] md:text-[22px]">About this property</h2><p class="mt-3 whitespace-pre-line text-sm leading-6 text-[#475467] md:text-[15px] md:leading-7">{{ $property->description }}</p></section>

                <section class="mt-6 border-t border-[#E4E7EC] pt-6" aria-labelledby="amenities-title"><h2 id="amenities-title" class="text-[20px] font-semibold text-[#182230] md:text-[22px]">Amenities</h2><div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">@foreach($property->amenities as $amenity)<div class="flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-[13px] font-medium text-[#344054]"><span class="flex size-6 items-center justify-center rounded-full bg-[#EAF2FF] text-[#145FCC]"><x-icon name="check" class="size-3.5" /></span>{{ $amenity->amenity_label }}</div>@endforeach</div></section>
            </article>

            <aside class="min-w-0 lg:sticky lg:top-5">
                <div class="rounded-xl border border-[#D7E2F4] bg-white p-4 shadow-[0_3px_14px_rgba(10,40,86,.07)] lg:p-5">
                    <div class="hidden lg:block">
                        <p class="text-sm font-medium text-[#667085]">Annual rent</p><p class="mt-1 text-[24px] font-semibold text-[#0A2856]">{{ $property->formatted_rent }}</p>
                        <div class="mt-3 flex items-center gap-2 text-sm text-[#475467]"><x-status-badge :type="$property->availability_status === 'available' ? 'available' : 'reserved'">{{ str($property->availability_status)->headline() }}</x-status-badge></div>
                    </div>

                    <x-agent-card :agent="$property->agent" class="border-0 p-0 shadow-none lg:mt-5 lg:border-t lg:pt-5" />

                    <div class="mt-4 hidden lg:block">
                        <button type="button" disabled aria-disabled="true" title="Inspection booking is currently unavailable." class="btn-primary w-full cursor-not-allowed opacity-60"><x-icon name="calendar" class="size-5" />Book Inspection</button>
                        @guest<button type="button" @click="openLogin('tenant')" :disabled="!online" class="btn-secondary mt-3 w-full disabled:cursor-not-allowed disabled:opacity-60"><x-icon name="chat" class="size-5" />Chat with Agent</button>@else<button type="button" @click="showToast('Secure chat is currently unavailable.')" class="btn-secondary mt-3 w-full"><x-icon name="chat" class="size-5" />Chat with Agent</button>@endguest
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button type="button" @click="toggleSaved({{ $property->id }})" :aria-pressed="isSaved({{ $property->id }})" class="btn-secondary min-h-11 px-3" aria-label="Save property">
                                <x-icon name="heart" class="size-5" x-bind:fill="isSaved({{ $property->id }}) ? 'currentColor' : 'none'" />
                                <span x-text="isSaved({{ $property->id }}) ? 'Saved' : 'Save'"></span>
                            </button>
                            <button type="button" @click="shareProperty({ title: @js($property->title), text: @js($property->formatted_rent.' in '.$property->area), url: @js(route('properties.show', $property)) })" class="btn-secondary min-h-11 px-3"><x-icon name="share" class="size-5" />Share</button>
                        </div>
                    </div>

                    <div class="mt-4 flex items-start gap-2 rounded-lg bg-[#FFF4E8] p-3 text-[13px] leading-5 text-[#7A4500]"><x-icon name="shield-check" class="mt-0.5 size-4 shrink-0" /><p>Never pay before viewing the property and confirming the agent’s details.</p></div>
                </div>
            </aside>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-2 gap-2 border-t border-[#E4E7EC] bg-white px-3 py-2 pb-[max(.5rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(10,40,86,.08)] lg:hidden">
        <button type="button" disabled aria-disabled="true" title="Inspection booking is currently unavailable." class="btn-primary min-h-12 cursor-not-allowed px-3 opacity-60"><x-icon name="calendar" class="size-4" />Book Inspection</button>
        @guest<button type="button" @click="openLogin('tenant')" :disabled="!online" class="btn-secondary min-h-12 px-3 disabled:opacity-60"><x-icon name="chat" class="size-4" />Chat</button>@else<button type="button" @click="showToast('Secure chat is currently unavailable.')" class="btn-secondary min-h-12 px-3"><x-icon name="chat" class="size-4" />Chat</button>@endguest
    </div>
</x-layouts.public>
