<x-layouts.public>
    @section('title', 'Properties for rent in Nigeria | Listora.ng')
    @section('meta_description', 'Search apartments, self contains, duplexes, shared flats, shops and offices across Nigeria.')

    <section class="border-b border-[#E4E7EC] bg-white">
        <div class="mx-auto max-w-[1180px] px-4 py-4 sm:px-6 lg:px-0">
            <form action="{{ route('properties.index') }}" method="GET" class="flex gap-2">
                @foreach(['state', 'city', 'area', 'type', 'min_price', 'max_price', 'bedrooms', 'furnishing', 'sort'] as $key) @if(isset($filters[$key]) && $key !== 'q')<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif @endforeach
                <label class="relative flex-1"><span class="sr-only">Search properties</span><x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-[#667085]" /><input name="q" value="{{ $filters['q'] ?? '' }}" class="form-input pl-11" placeholder="Search location or property"></label>
                <button class="btn-primary px-4 sm:px-6" aria-label="Search properties"><x-icon name="search" class="size-5" /><span class="hidden sm:inline">Search</span></button>
            </form>
            <div class="mt-4 flex gap-2 overflow-x-auto pb-1 md:hidden" aria-label="Property types">
                @foreach(['' => 'All', 'apartment' => 'Apartments', 'self-contain' => 'Self Contain', 'duplex' => 'Duplexes', 'shared-flat' => 'Shared Flats', 'shop' => 'Shops', 'office' => 'Offices'] as $type => $label)
                    <a href="{{ route('properties.index', array_filter(array_merge(request()->except(['type', 'page']), ['type' => $type]))) }}" class="shrink-0 rounded-full border px-3 py-2 text-[13px] font-medium {{ ($filters['type'] ?? '') === $type ? 'border-[#145FCC] bg-[#EAF2FF] text-[#145FCC]' : 'border-[#D0D5DD] bg-white text-[#475467]' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6 lg:px-0 lg:py-8">
        @if($errors->any())<div class="mb-5 rounded-lg border border-[#F3B4AE] bg-[#FFF5F4] p-4 text-sm text-[#C92A2A]" role="alert"><strong>Check the filters and try again.</strong><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="grid gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">
            <aside class="hidden lg:block" aria-label="Property filters">
                <form action="{{ route('properties.index') }}" method="GET" class="sticky top-5 rounded-xl border border-[#E4E7EC] bg-white p-5">
                    <div class="mb-5 flex items-center justify-between"><h2 class="font-semibold text-[#182230]">Filters</h2><a href="{{ route('properties.index') }}" class="text-sm font-medium text-[#145FCC]">Reset</a></div>
                    <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
                    @include('public.properties._filter-fields', ['prefix' => 'desktop'])
                    <button class="btn-primary mt-6 w-full">Apply Filters</button>
                </form>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-[13px] text-[#667085]">{{ $properties->total() }} {{ Str::plural('property', $properties->total()) }}</p><h1 class="text-[20px] font-semibold tracking-tight text-[#182230] sm:text-2xl">{{ ($filters['q'] ?? null) ? 'Results for “'.$filters['q'].'”' : 'Properties for rent' }}</h1></div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openFilter()" class="btn-secondary min-h-11 px-3 lg:hidden"><x-icon name="filter" class="size-4" />Filters</button>
                        <button type="button" @click="openSort()" class="btn-secondary min-h-11 px-3 md:hidden"><x-icon name="sort" class="size-4" />Sort</button>
                        <form action="{{ route('properties.index') }}" method="GET" class="relative hidden md:block">
                            @foreach(request()->except(['sort', 'page']) as $key => $value) @if(is_array($value)) @foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach @else <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endif @endforeach
                            <label class="sr-only" for="sort">Sort properties</label><select id="sort" name="sort" onchange="this.form.submit()" class="h-11 appearance-none rounded-lg border border-[#D0D5DD] bg-white pl-9 pr-8 text-sm font-medium text-[#475467]"><option value="recommended" @selected(($filters['sort'] ?? 'recommended') === 'recommended')>Recommended</option><option value="latest" @selected(($filters['sort'] ?? '') === 'latest')>Newest</option><option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Lowest price</option><option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Highest price</option></select><x-icon name="sort" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#667085]" /><x-icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 size-3.5 -translate-y-1/2 text-[#667085]" />
                        </form>
                        <div class="hidden rounded-lg border border-[#D0D5DD] bg-white p-1 md:flex"><button type="button" @click="setView('grid')" :class="viewMode === 'grid' ? 'bg-[#EAF2FF] text-[#145FCC]' : 'text-[#667085]'" class="touch-icon size-9" aria-label="Grid view"><x-icon name="grid" class="size-4" /></button><button type="button" @click="setView('list')" :class="viewMode === 'list' ? 'bg-[#EAF2FF] text-[#145FCC]' : 'text-[#667085]'" class="touch-icon size-9" aria-label="List view"><x-icon name="list" class="size-4" /></button></div>
                    </div>
                </div>

                @if($properties->isEmpty())
                    <x-empty-state title="No properties match those filters" message="Try removing a filter or searching a nearby area."><a href="{{ route('properties.index') }}" class="btn-secondary">Reset filters</a></x-empty-state>
                @else
                    <div :class="viewMode === 'list' ? 'view-list grid-cols-1' : 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3'" class="grid gap-4 lg:gap-5">@foreach($properties as $property)<x-property-card :property="$property" :eager="$loop->first" />@endforeach</div>
                    <div class="mt-8">{{ $properties->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <div x-cloak x-show="filterOpen" @keydown.escape.window="closeFilter()" class="fixed inset-0 z-[65] lg:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-filter-title">
        <button @click="closeFilter()" class="absolute inset-0 bg-[#06152D]/70" aria-label="Close filters"></button>
        <form action="{{ route('properties.index') }}" method="GET" x-show="filterOpen" @keydown.tab="trapFocus($event, $el)" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="absolute inset-x-0 bottom-0 flex max-h-[90dvh] flex-col rounded-t-2xl bg-white">
            <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-[#D0D5DD]"></div><div class="flex items-center justify-between border-b px-4 py-3"><h2 id="mobile-filter-title" class="text-lg font-semibold">Filters</h2><button x-ref="filterClose" type="button" @click="closeFilter()" class="touch-icon -mr-2" aria-label="Close filters"><x-icon name="x" /></button></div>
            <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
            <div class="overflow-y-auto px-4 py-4">@include('public.properties._filter-fields', ['prefix' => 'mobile'])</div>
            <div class="sticky bottom-0 grid grid-cols-2 gap-3 border-t bg-white px-4 py-3 pb-[max(.75rem,env(safe-area-inset-bottom))]"><a href="{{ route('properties.index') }}" class="btn-secondary">Reset</a><button class="btn-primary">Show properties</button></div>
        </form>
    </div>

    <div x-cloak x-show="sortOpen" @keydown.escape.window="closeSort()" class="fixed inset-0 z-[66] md:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-sort-title">
        <button @click="closeSort()" class="absolute inset-0 bg-[#06152D]/70" aria-label="Close sort options"></button>
        <section x-show="sortOpen" @keydown.tab="trapFocus($event, $el)" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="absolute inset-x-0 bottom-0 rounded-t-2xl bg-white pb-[max(1rem,env(safe-area-inset-bottom))]">
            <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-[#D0D5DD]"></div>
            <div class="flex items-center justify-between border-b px-4 py-3"><h2 id="mobile-sort-title" class="text-lg font-semibold">Sort properties</h2><button x-ref="sortClose" type="button" @click="closeSort()" class="touch-icon -mr-2" aria-label="Close sort options"><x-icon name="x" /></button></div>
            <div class="grid px-4 py-2">
                @foreach(['recommended' => 'Recommended', 'latest' => 'Newest', 'price_asc' => 'Lowest price', 'price_desc' => 'Highest price'] as $value => $label)
                    <a href="{{ route('properties.index', array_merge(request()->except(['sort', 'page']), ['sort' => $value])) }}" class="flex min-h-12 items-center justify-between border-b border-[#EEF0F3] text-sm font-medium text-[#344054]"><span>{{ $label }}</span>@if(($filters['sort'] ?? 'recommended') === $value)<x-icon name="check" class="size-5 text-[#145FCC]" />@endif</a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.public>
