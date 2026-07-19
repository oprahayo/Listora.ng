<x-layouts.public>
    @section('title', 'Listora.ng — Find a property that fits')
    @section('meta_description', 'Browse clear property listings from agents across Lagos, Abuja, Port Harcourt, Ibadan, Ado-Ekiti and more.')

    <section class="relative overflow-hidden bg-white">
        <div class="absolute inset-x-0 top-0 h-1 bg-[#155EEF]"></div>
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:py-18 lg:grid-cols-[1fr_1.05fr] lg:items-center lg:px-8 lg:py-24">
            <div>
                <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-[#EEF4FF] px-3 py-1.5 text-sm font-semibold text-[#0B2A5B]"><x-icon name="map-pin" class="size-4 text-[#155EEF]" />Properties across Nigeria</p>
                <h1 class="max-w-2xl text-4xl font-bold leading-[1.08] tracking-[-0.035em] text-[#0B2A5B] sm:text-5xl lg:text-6xl">Find a property that fits.</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-[#667085]">Browse verified property listings from agents across Nigeria.</p>
                <div class="mt-6 flex flex-wrap gap-4 text-sm font-medium text-[#475467]">
                    <span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-[#155EEF]" />Clear annual prices</span>
                    <span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-[#155EEF]" />Local area search</span>
                </div>
            </div>

            <form action="{{ route('properties.index') }}" method="GET" class="rounded-2xl border border-[#D7E2F4] bg-[#F7F9FC] p-4 shadow-[0_20px_50px_rgba(11,42,91,0.10)] sm:p-6" aria-label="Search properties">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="hero-location" class="form-label">Location</label>
                        <div class="relative"><x-icon name="map-pin" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-[#667085]" /><select id="hero-location" name="city" class="form-input appearance-none pl-10"><option value="">Anywhere in Nigeria</option>@foreach(['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Ado-Ekiti'] as $city)<option>{{ $city }}</option>@endforeach</select><x-icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /></div>
                    </div>
                    <div>
                        <label for="hero-type" class="form-label">Property type</label>
                        <div class="relative"><x-icon name="building" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-[#667085]" /><select id="hero-type" name="type" class="form-input appearance-none pl-10"><option value="">All property types</option><option value="apartment">Apartments</option><option value="self-contain">Self Contain</option><option value="duplex">Duplexes</option><option value="shared-flat">Shared Flats</option><option value="shop">Shops</option><option value="office">Offices</option></select><x-icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /></div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="hero-q" class="form-label">What are you looking for?</label>
                        <div class="relative"><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-[#667085]" /><input id="hero-q" name="q" class="form-input pl-10" placeholder="Try “2 bedroom in Yaba”"></div>
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-4 w-full"><x-icon name="search" class="size-5" />Search properties</button>
                <p class="mt-3 text-center text-xs text-[#667085]">Simple listings. No hidden platform fee.</p>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-18 lg:px-8" aria-labelledby="categories-title">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-sm font-bold uppercase tracking-[.16em] text-[#155EEF]">Browse by type</p><h2 id="categories-title" class="mt-2 text-2xl font-bold tracking-tight text-[#172033] md:text-3xl">Start with what you need</h2></div>
            <a href="{{ route('properties.index') }}" class="hidden items-center gap-1 text-sm font-semibold text-[#155EEF] hover:text-[#0E4CC9] sm:flex">View all <x-icon name="chevron-right" class="size-4" /></a>
        </div>
        <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 lg:gap-4">
            <x-icon-card icon="building" title="Apartments" :href="route('properties.index', ['type' => 'apartment'])" />
            <x-icon-card icon="key" title="Self Contain" :href="route('properties.index', ['type' => 'self-contain'])" />
            <x-icon-card icon="duplex" title="Duplexes" :href="route('properties.index', ['type' => 'duplex'])" />
            <x-icon-card icon="users" title="Shared Flats" :href="route('properties.index', ['type' => 'shared-flat'])" />
            <x-icon-card icon="shop" title="Shops" :href="route('properties.index', ['type' => 'shop'])" />
            <x-icon-card icon="office" title="Offices" :href="route('properties.index', ['type' => 'office'])" />
        </div>
    </section>

    <section class="border-y border-[#E4E7EC] bg-white py-14 md:py-18" aria-labelledby="featured-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div><p class="text-sm font-bold uppercase tracking-[.16em] text-[#155EEF]">Fresh places</p><h2 id="featured-title" class="mt-2 text-2xl font-bold tracking-tight text-[#172033] md:text-3xl">Featured properties</h2><p class="mt-2 text-sm text-[#667085]">A few well-presented homes and spaces to start with.</p></div>
                <a href="{{ route('properties.index') }}" class="btn-secondary hidden sm:inline-flex">Browse all</a>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 lg:grid-cols-4 lg:gap-5">
                @foreach($featured as $property)<x-property-card :property="$property" />@endforeach
            </div>
            <a href="{{ route('properties.index') }}" class="btn-secondary mt-6 w-full sm:hidden">Browse all properties</a>
        </div>
    </section>

    <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8" aria-labelledby="trust-title">
        <div class="rounded-2xl bg-[#0B2A5B] px-5 py-9 text-white sm:px-8 md:px-10 md:py-12">
            <div class="max-w-xl"><p class="text-sm font-bold uppercase tracking-[.16em] text-[#8FB7FF]">Made for straightforward renting</p><h2 id="trust-title" class="mt-2 text-2xl font-bold tracking-tight md:text-3xl">The important things, kept simple.</h2></div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['shield-check', 'Verified agents', 'See a clear badge when an agent has been verified.'],
                    ['calendar', 'Easy inspections', 'Inspection booking arrives in Sprint 5, right where you need it.'],
                    ['lock', 'Secure communication', 'Sign-in protects future conversations and private details.'],
                    ['settings', 'Property management', 'Simple tools for agents, owners and tenants are on the roadmap.'],
                ] as [$icon, $title, $copy])
                    <div><span class="flex size-11 items-center justify-center rounded-lg bg-white/10 text-[#8FB7FF]"><x-icon :name="$icon" class="size-6" /></span><h3 class="mt-4 font-semibold">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-[#C7D7EF]">{{ $copy }}</p></div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
