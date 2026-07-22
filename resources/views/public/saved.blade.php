<x-layouts.public>
    @section('title', 'Saved properties | Listora.ng')
    @section('meta_description', 'Review the properties saved locally on this device.')

    <section class="mx-auto min-h-[55vh] max-w-[1180px] px-4 py-8 sm:px-6 lg:px-0" x-init="loadSaved()">
        <div class="max-w-2xl"><h1 class="text-[26px] font-semibold tracking-tight text-[#182230] md:text-3xl">Saved properties</h1><p class="mt-2 text-sm text-[#667085]">Properties saved on this device appear here.</p></div>

        <div x-show="savedLoading" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Loading saved properties">@foreach(range(1,3) as $item)<div class="animate-pulse overflow-hidden rounded-xl border bg-white"><div class="aspect-[3/2] bg-[#E9EEF5]"></div><div class="space-y-3 p-4"><div class="h-5 w-1/2 rounded bg-[#E9EEF5]"></div><div class="h-4 w-4/5 rounded bg-[#E9EEF5]"></div><div class="h-4 w-2/3 rounded bg-[#E9EEF5]"></div></div></div>@endforeach</div>

        <div x-cloak x-show="!savedLoading && savedProperties.length" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="property in savedProperties" :key="property.id">
                <article class="overflow-hidden rounded-lg border border-[#E4E7EC] bg-white shadow-[0_1px_3px_rgba(10,40,86,.08)]"><div class="relative aspect-[16/10] bg-[#EAF2FF]"><a :href="property.url"><img :src="property.image" :alt="property.image_alt" width="640" height="400" loading="lazy" decoding="async" class="h-full w-full object-cover"></a><button @click="toggleSaved(property.id)" class="absolute right-2 top-2 flex size-9 items-center justify-center rounded-full bg-white text-[#145FCC] shadow-sm" :aria-label="`Remove ${property.title} from saved`"><x-icon name="heart" class="size-[18px]" fill="currentColor" /></button></div><div class="p-3"><div class="flex items-center gap-2"><p class="text-[18px] font-semibold text-[#0A2856]" x-text="property.rent"></p><span x-show="property.verified" title="Verified agent" class="text-[#145FCC]"><x-icon name="shield-check" class="size-4" /></span></div><h2 class="mt-1 text-[15px] font-medium text-[#182230]"><a :href="property.url" x-text="property.title"></a></h2><p class="mt-2 flex items-center gap-1 text-[12px] text-[#667085]"><x-icon name="map-pin" class="size-3.5" /><span class="truncate" x-text="property.location"></span></p></div></article>
            </template>
        </div>

        <div x-show="!savedLoading && !savedProperties.length" class="mt-8"><x-empty-state icon="heart" title="No saved properties yet" message="Tap the heart icon on any property to save it here."><a href="{{ route('properties.index') }}" class="btn-primary">Browse properties</a></x-empty-state></div>
    </section>
</x-layouts.public>
