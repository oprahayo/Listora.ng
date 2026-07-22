<x-layouts.public>
    @section('title', 'Listora.ng — Find your next property')
    @section('meta_description', 'Browse clear property listings from agents across Lagos, Abuja, Port Harcourt, Ibadan, Ado-Ekiti and more.')

    <section class="border-b border-[#E4E7EC] bg-white">
        <div class="mx-auto max-w-[1180px] px-4 sm:px-6 lg:px-0">
            <div class="py-4 md:hidden">
                <h1 class="sr-only">Find homes and spaces near you.</h1>
                <form action="{{ route('properties.index') }}" method="GET" aria-label="Search properties">
                    <label class="sr-only" for="home-mobile-search">Search location or property</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-[#667085]" />
                        <input id="home-mobile-search" name="q" class="h-12 w-full rounded-lg border border-[#D0D5DD] bg-white pl-11 pr-12 text-sm text-[#182230] placeholder:text-[#98A2B3] focus:border-[#145FCC] focus:outline-none focus:ring-3 focus:ring-[#145FCC]/10" placeholder="Search location or property">
                        <button type="submit" class="absolute right-1 top-1 flex size-10 items-center justify-center rounded-lg text-[#145FCC]" aria-label="Search properties"><x-icon name="search" class="size-5" /></button>
                    </div>
                </form>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <a href="{{ route('properties.index', ['city' => 'Lagos']) }}" class="flex min-h-10 items-center justify-center gap-2 rounded-lg border border-[#D7E2F4] bg-[#F7FAFF] px-3 text-[13px] font-medium text-[#0A2856]"><x-icon name="map-pin" class="size-4 text-[#145FCC]" />Lagos</a>
                    <button type="button" @click="openFilter()" class="flex min-h-10 items-center justify-center gap-2 rounded-lg border border-[#D7E2F4] bg-[#F7FAFF] px-3 text-[13px] font-medium text-[#0A2856]"><x-icon name="filter" class="size-4 text-[#145FCC]" />Filters</button>
                </div>
            </div>

            <div class="hidden py-8 md:block lg:py-10">
                <h1 class="max-w-2xl text-[36px] font-semibold leading-[1.12] tracking-[-0.025em] text-[#0A2856] lg:text-[38px]">Find your next property.</h1>
                <p class="mt-2 text-[15px] text-[#667085]">Browse properties from agents across Nigeria.</p>
                <form action="{{ route('properties.index') }}" method="GET" class="mt-5 flex items-center gap-2 rounded-xl border border-[#D7E2F4] bg-[#F7F8FA] p-2" aria-label="Search properties">
                    <label class="relative w-48 shrink-0"><span class="sr-only">Location</span><x-icon name="map-pin" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /><select name="city" class="h-11 w-full appearance-none rounded-lg border border-[#D0D5DD] bg-white pl-9 pr-8 text-sm text-[#182230]"><option value="">Nigeria</option>@foreach(['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Ado-Ekiti'] as $city)<option>{{ $city }}</option>@endforeach</select><x-icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-[#667085]" /></label>
                    <label class="relative w-48 shrink-0"><span class="sr-only">Property type</span><x-icon name="building" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /><select name="type" class="h-11 w-full appearance-none rounded-lg border border-[#D0D5DD] bg-white pl-9 pr-8 text-sm text-[#182230]"><option value="">All property types</option><option value="apartment">Apartments</option><option value="self-contain">Self Contain</option><option value="duplex">Duplexes</option><option value="shared-flat">Shared Flats</option><option value="shop">Shops</option><option value="office">Offices</option></select><x-icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-[#667085]" /></label>
                    <label class="relative min-w-0 flex-1"><span class="sr-only">Search location or property</span><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /><input name="q" class="h-11 w-full rounded-lg border border-[#D0D5DD] bg-white pl-9 pr-3 text-sm text-[#182230] placeholder:text-[#98A2B3]" placeholder="Search location or property"></label>
                    <button type="submit" class="btn-primary min-h-11 shrink-0 px-5"><x-icon name="search" class="size-4" />Search</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="categories-title">
        <div class="mx-auto max-w-[1180px] px-4 pb-4 pt-3 sm:px-6 md:py-6 lg:px-0">
            <div class="hidden items-center justify-between md:flex">
                <h2 id="categories-title" class="text-xl font-semibold text-[#182230]">Browse by category</h2>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-[#145FCC] hover:text-[#0E4DA9]">See all <x-icon name="chevron-right" class="size-4" /></a>
            </div>
            <h2 class="sr-only md:hidden">Browse by category</h2>
            <div class="grid grid-cols-4 gap-2 md:mt-4 md:grid-cols-8 md:gap-3">
                <x-icon-card icon="building" title="Apartments" :href="route('properties.index', ['type' => 'apartment'])" />
                <x-icon-card icon="key" title="Self Con." :href="route('properties.index', ['type' => 'self-contain'])" />
                <x-icon-card icon="duplex" title="Duplexes" :href="route('properties.index', ['type' => 'duplex'])" />
                <x-icon-card icon="users" title="Shared" :href="route('properties.index', ['type' => 'shared-flat'])" />
                <x-icon-card icon="shop" title="Shops" :href="route('properties.index', ['type' => 'shop'])" />
                <x-icon-card icon="office" title="Offices" :href="route('properties.index', ['type' => 'office'])" />
                <x-icon-card icon="home" title="Houses" :href="route('properties.index', ['q' => 'House'])" />
                <x-icon-card icon="more-horizontal" title="More" :href="route('properties.index')" aria-label="Browse all property categories" />
            </div>
        </div>
    </section>

    <section class="border-y border-[#E4E7EC] bg-[#F7F8FA] py-6 md:py-8" aria-labelledby="featured-title">
        <div class="mx-auto max-w-[1180px] px-4 sm:px-6 lg:px-0">
            <div class="flex items-center justify-between gap-4">
                <h2 id="featured-title" class="text-[20px] font-semibold tracking-tight text-[#182230]">Featured properties</h2>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-1 text-[13px] font-medium text-[#145FCC] hover:text-[#0E4DA9]">See all <x-icon name="chevron-right" class="size-4" /></a>
            </div>
            <div class="home-featured-track mt-3 md:mt-5">
                @foreach($featured as $property)<x-property-card :property="$property" :compact="true" :eager="$loop->first" />@endforeach
            </div>
        </div>
    </section>

    <section id="how-it-works" class="mx-auto hidden max-w-[1180px] px-6 py-8 md:block lg:px-0" aria-labelledby="trust-title">
        <div class="rounded-xl bg-[#0A2856] px-8 py-8 text-white">
            <h2 id="trust-title" class="text-xl font-semibold">Property search, kept simple.</h2>
            <div class="mt-6 grid grid-cols-4 gap-6">
                @foreach([
                    ['shield-check', 'Verified agents', 'Know when an agent has completed verification.'],
                    ['calendar', 'Easy inspection booking', 'Book inspections easily at a convenient time.'],
                    ['lock', 'Secure communication', 'Chat securely with verified agents.'],
                    ['settings', 'Property management', 'Manage your property from one simple account.'],
                ] as [$icon, $title, $copy])
                    <div><span class="flex size-10 items-center justify-center rounded-lg bg-white/10 text-[#8FB7FF]"><x-icon :name="$icon" class="size-5" /></span><h3 class="mt-3 text-sm font-semibold">{{ $title }}</h3><p class="mt-1 text-sm leading-6 text-[#C7D7EF]">{{ $copy }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <div x-cloak x-show="filterOpen" @keydown.escape.window="closeFilter()" class="fixed inset-0 z-[65] md:hidden" role="dialog" aria-modal="true" aria-labelledby="home-filter-title">
        <button @click="closeFilter()" class="absolute inset-0 bg-[#06152D]/70" aria-label="Close filters"></button>
        <form action="{{ route('properties.index') }}" method="GET" x-show="filterOpen" @keydown.tab="trapFocus($event, $el)" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="absolute inset-x-0 bottom-0 flex max-h-[90dvh] flex-col rounded-t-2xl bg-white">
            <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-[#D0D5DD]"></div>
            <div class="flex items-center justify-between border-b px-4 py-3"><h2 id="home-filter-title" class="text-lg font-semibold">Filters</h2><button x-ref="filterClose" type="button" @click="closeFilter()" class="touch-icon -mr-2" aria-label="Close filters"><x-icon name="x" /></button></div>
            <div class="overflow-y-auto px-4 py-4">@include('public.properties._filter-fields', ['prefix' => 'home'])</div>
            <div class="sticky bottom-0 grid grid-cols-2 gap-3 border-t bg-white px-4 py-3 pb-[max(.75rem,env(safe-area-inset-bottom))]"><a href="{{ route('properties.index') }}" class="btn-secondary">Reset</a><button class="btn-primary">Show properties</button></div>
        </form>
    </div>
</x-layouts.public>
