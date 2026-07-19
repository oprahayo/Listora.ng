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

    <div class="mx-auto max-w-[1180px] px-4 pb-28 pt-4 sm:px-6 md:pb-14 lg:px-0 lg:pt-6">
        <a href="{{ route('properties.index') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-[#475467] hover:text-[#155EEF]"><x-icon name="arrow-left" class="size-4" />Back to listings</a>

        <div class="mt-3 grid gap-2 overflow-hidden rounded-xl md:grid-cols-[2fr_1fr] md:grid-rows-2">
            @foreach($property->images->take(3) as $image)
                @php $avif = preg_replace('/\.webp$/', '.avif', $image->image_path); $jpeg = preg_replace('/\.webp$/', '.jpg', $image->image_path); @endphp
                <figure class="relative min-h-56 overflow-hidden bg-[#EEF4FF] {{ $loop->first ? 'md:row-span-2 md:min-h-[480px]' : 'hidden md:block' }} {{ $property->images->count() === 2 && ! $loop->first ? 'md:row-span-2' : '' }}">
                    <picture><source srcset="{{ $avif }}" type="image/avif"><source srcset="{{ $image->image_path }}" type="image/webp"><img src="{{ $jpeg }}" alt="{{ $image->alt_text }}" width="1200" height="800" @if(!$loop->first) loading="lazy" @endif class="absolute inset-0 h-full w-full object-cover transition hover:scale-[1.01]"></picture>
                </figure>
            @endforeach
            @if($property->images->count() < 2)<div class="hidden min-h-56 bg-[#EEF4FF] md:block"></div>@endif
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-10">
            <article class="min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><div class="flex flex-wrap items-center gap-2"><x-status-badge :type="$property->availability_status === 'available' ? 'available' : 'reserved'">{{ str($property->availability_status)->headline() }}</x-status-badge><x-status-badge>{{ $property->type_label }}</x-status-badge></div><p class="mt-3 text-[18px] font-semibold tracking-tight text-[#0B2A5B] md:text-[28px]">{{ $property->formatted_rent }}</p><h1 class="mt-1 text-[22px] font-semibold leading-tight tracking-tight text-[#172033] md:text-3xl">{{ $property->title }}</h1><p class="mt-2 flex items-center gap-1.5 text-[13px] text-[#667085] md:text-sm"><x-icon name="map-pin" class="size-4 text-[#155EEF]" />{{ $property->display_address }}</p></div>
                    <div class="flex gap-2"><button type="button" @click="toggleSaved({{ $property->id }})" :aria-pressed="isSaved({{ $property->id }})" class="btn-secondary px-3" aria-label="Save property"><x-icon name="bookmark" class="size-5" x-bind:fill="isSaved({{ $property->id }}) ? 'currentColor' : 'none'" /><span x-text="isSaved({{ $property->id }}) ? 'Saved' : 'Save'"></span></button><button type="button" @click="shareProperty({ title: @js($property->title), text: @js($property->formatted_rent.' in '.$property->area), url: @js(route('properties.show', $property)) })" class="btn-secondary px-3"><x-icon name="share" class="size-5" />Share</button></div>
                </div>

                <section class="mt-6 grid grid-cols-2 gap-2 rounded-xl border border-[#E4E7EC] bg-white p-3 sm:grid-cols-4 md:gap-3 md:p-4" aria-label="Property facts">
                    @foreach([
                        ['bed', $property->bedrooms ? $property->bedrooms.' bedrooms' : null],
                        ['bath', $property->bathrooms ? $property->bathrooms.' bathrooms' : null],
                        ['car', $property->parking_spaces !== null ? $property->parking_spaces.' parking' : null],
                        ['ruler', $property->size_sqm ? number_format((float)$property->size_sqm).' m²' : null],
                    ] as [$icon, $fact]) @if($fact)<div class="flex min-h-12 items-center gap-2 rounded-lg bg-[#F7F9FC] px-2.5 md:min-h-16 md:gap-3 md:px-3"><x-icon :name="$icon" class="size-4 shrink-0 text-[#155EEF] md:size-5" /><span class="text-[13px] font-medium text-[#344054] md:text-sm">{{ $fact }}</span></div>@endif @endforeach
                </section>

                <section class="mt-6 md:mt-8" aria-labelledby="description-title"><h2 id="description-title" class="text-[20px] font-semibold text-[#172033]">About this property</h2><p class="mt-3 whitespace-pre-line text-sm leading-6 text-[#475467] md:text-base md:leading-7">{{ $property->description }}</p><p class="mt-3 text-[13px] text-[#667085] md:text-sm">Always confirm fees, availability and terms directly before making a commitment.</p></section>

                <section class="mt-6 border-t border-[#E4E7EC] pt-6 md:mt-8" aria-labelledby="amenities-title"><h2 id="amenities-title" class="text-[20px] font-semibold text-[#172033]">Amenities</h2><div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 md:gap-3">@foreach($property->amenities as $amenity)<div class="flex min-h-11 items-center gap-2 rounded-lg bg-white px-3 text-[13px] font-medium text-[#344054] md:text-sm"><span class="flex size-6 items-center justify-center rounded-full bg-[#EEF4FF] text-[#155EEF]"><x-icon name="check" class="size-3.5" /></span>{{ $amenity->amenity_label }}</div>@endforeach</div></section>

                <section class="mt-6 rounded-xl border border-[#D7E2F4] bg-white p-4 md:mt-8 md:p-5" aria-labelledby="agent-title">
                    <div class="flex items-start gap-3 md:gap-4"><div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-[#0B2A5B] text-base font-semibold text-white md:size-12">{{ Str::of($property->agent->display_name)->explode(' ')->map(fn($part) => Str::substr($part, 0, 1))->take(2)->join('') }}</div><div><div class="flex flex-wrap items-center gap-2"><h2 id="agent-title" class="font-semibold text-[#172033]">{{ $property->agent->display_name }}</h2>@if($property->agent->isVerified())<x-status-badge type="verified" icon="shield-check">Verified agent</x-status-badge>@endif</div><p class="mt-1 text-[13px] text-[#667085] md:text-sm">{{ $property->agent->primary_location }} property professional</p><p class="mt-2 max-w-2xl text-sm leading-6 text-[#475467]">{{ $property->agent->short_bio }}</p></div></div>
                </section>
            </article>

            <aside class="hidden lg:block">
                <div class="sticky top-5 rounded-xl border border-[#D7E2F4] bg-white p-5 shadow-[0_12px_35px_rgba(11,42,91,.08)]">
                    <p class="text-sm font-medium text-[#667085]">Annual rent</p><p class="mt-1 text-2xl font-semibold text-[#0B2A5B]">{{ $property->formatted_rent }}</p><p class="mt-2 text-sm leading-5 text-[#667085]">Confirm all charges with the agent before payment.</p>
                    <button type="button" @click="infoOpen = true" :disabled="!online" class="btn-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-60"><x-icon name="calendar" class="size-5" />Book Inspection</button>
                    @guest<button type="button" @click="openLogin('tenant')" :disabled="!online" class="btn-secondary mt-3 w-full disabled:cursor-not-allowed disabled:opacity-60"><x-icon name="chat" class="size-5" />Chat with Agent</button>@else<button type="button" @click="showToast('Secure chat is currently unavailable.')" class="btn-secondary mt-3 w-full"><x-icon name="chat" class="size-5" />Chat with Agent</button>@endguest
                    <p x-show="!online" class="mt-3 text-xs font-medium text-[#8A4B00]">Reconnect to book or chat.</p>
                </div>
            </aside>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-2 gap-2 border-t border-[#E4E7EC] bg-white px-3 py-3 pb-[max(.75rem,env(safe-area-inset-bottom))] shadow-[0_-6px_20px_rgba(11,42,91,.08)] lg:hidden">
        <button type="button" @click="infoOpen = true" :disabled="!online" class="btn-primary px-3 disabled:opacity-60"><x-icon name="calendar" class="size-4" />Book Inspection</button>
        @guest<button type="button" @click="openLogin('tenant')" :disabled="!online" class="btn-secondary px-3 disabled:opacity-60"><x-icon name="chat" class="size-4" />Chat with Agent</button>@else<button type="button" @click="showToast('Secure chat is currently unavailable.')" class="btn-secondary px-3"><x-icon name="chat" class="size-4" />Chat with Agent</button>@endguest
    </div>

    <div x-cloak x-show="infoOpen" @keydown.escape.window="infoOpen = false" class="fixed inset-0 z-[75] flex items-end justify-center p-0 md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="inspection-title"><button @click="infoOpen = false" class="absolute inset-0 bg-[#06152D]/75" aria-label="Close"></button><section x-show="infoOpen" x-transition class="relative w-full rounded-t-2xl bg-white p-5 md:max-w-md md:rounded-xl md:p-6"><button @click="infoOpen = false" class="touch-icon absolute right-3 top-3" aria-label="Close"><x-icon name="x" /></button><span class="flex size-11 items-center justify-center rounded-lg bg-[#EEF4FF] text-[#155EEF]"><x-icon name="calendar" class="size-5" /></span><h2 id="inspection-title" class="mt-4 text-xl font-semibold">Inspection booking is currently unavailable</h2><p class="mt-2 text-sm leading-6 text-[#667085]">No booking has been created. Please check back later to choose a convenient inspection time.</p><button @click="infoOpen = false" class="btn-primary mt-5 w-full">Got it</button></section></div>
</x-layouts.public>
