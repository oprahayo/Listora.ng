<x-layouts.public>
    @section('title', 'Saved properties | Listora.ng')
    @section('meta_description', 'Review the properties saved locally on this device.')

    <section class="mx-auto min-h-[55vh] max-w-[1180px] px-4 py-8 sm:px-6 lg:px-0" x-init="loadSaved()">
        <div class="max-w-2xl"><h1 class="text-[26px] font-semibold tracking-tight text-[#172033] md:text-3xl">Saved properties</h1><p class="mt-2 text-sm text-[#667085]">Your saved properties are stored on this device.</p></div>

        <div x-show="savedLoading" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Loading saved properties">@foreach(range(1,3) as $item)<div class="animate-pulse overflow-hidden rounded-xl border bg-white"><div class="aspect-[3/2] bg-[#E9EEF5]"></div><div class="space-y-3 p-4"><div class="h-5 w-1/2 rounded bg-[#E9EEF5]"></div><div class="h-4 w-4/5 rounded bg-[#E9EEF5]"></div><div class="h-4 w-2/3 rounded bg-[#E9EEF5]"></div></div></div>@endforeach</div>

        <div x-cloak x-show="!savedLoading && savedProperties.length" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="property in savedProperties" :key="property.id">
                <article class="overflow-hidden rounded-lg border border-[#E4E7EC] bg-white shadow-[0_3px_12px_rgba(11,42,91,.04)]"><div class="relative aspect-[3/2] bg-[#EEF4FF]"><a :href="property.url"><img :src="property.image" :alt="property.image_alt" width="720" height="480" class="h-full w-full object-cover"></a><button @click="toggleSaved(property.id)" class="absolute right-2 top-2 flex size-9 items-center justify-center rounded-full bg-white text-[#155EEF] shadow-sm" :aria-label="`Remove ${property.title} from saved`"><x-icon name="bookmark" class="size-[18px]" fill="currentColor" /></button></div><div class="p-3"><div class="flex items-center gap-2"><p class="text-[17px] font-semibold text-[#0B2A5B]" x-text="property.rent"></p><span x-show="property.verified" title="Verified agent" class="text-[#155EEF]"><x-icon name="shield-check" class="size-4" /></span></div><h2 class="mt-1 text-[14px] font-semibold text-[#172033]"><a :href="property.url" x-text="property.title"></a></h2><p class="mt-2 flex items-center gap-1 text-[12px] text-[#667085]"><x-icon name="map-pin" class="size-3.5" /><span x-text="property.location"></span></p></div></article>
            </template>
        </div>

        <div x-show="!savedLoading && !savedProperties.length" class="mt-8"><x-empty-state icon="bookmark" title="No saved properties yet" message="Tap the save icon on any property. It will stay saved on this device."><a href="{{ route('properties.index') }}" class="btn-primary">Browse properties</a></x-empty-state></div>
    </section>
</x-layouts.public>
