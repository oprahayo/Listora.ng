<div class="grid gap-4">
    <div>
        <label class="filter-label" for="{{ $prefix }}-state">State</label>
        <select id="{{ $prefix }}-state" name="state" class="filter-input"><option value="">All states</option>@foreach(['Lagos', 'FCT', 'Rivers', 'Oyo', 'Ekiti'] as $state)<option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>@endforeach</select>
    </div>
    <div>
        <label class="filter-label" for="{{ $prefix }}-city">City</label>
        <select id="{{ $prefix }}-city" name="city" class="filter-input"><option value="">All cities</option>@foreach(['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Ado-Ekiti'] as $city)<option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>@endforeach</select>
    </div>
    <div>
        <label class="filter-label" for="{{ $prefix }}-area">Area</label>
        <input id="{{ $prefix }}-area" name="area" value="{{ $filters['area'] ?? '' }}" class="filter-input" placeholder="e.g. Yaba">
    </div>
    <div>
        <label class="filter-label" for="{{ $prefix }}-type">Property type</label>
        <select id="{{ $prefix }}-type" name="type" class="filter-input"><option value="">All property types</option>@foreach(['apartment' => 'Apartments', 'self-contain' => 'Self Contain', 'duplex' => 'Duplexes', 'shared-flat' => 'Shared Flats', 'shop' => 'Shops', 'office' => 'Offices'] as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
    </div>
    <div>
        <span class="filter-label">Annual price</span>
        <div class="grid grid-cols-2 gap-2"><label><span class="sr-only">Minimum annual price</span><input name="min_price" value="{{ $filters['min_price'] ?? '' }}" type="number" min="0" step="50000" class="filter-input" placeholder="Min ₦"></label><label><span class="sr-only">Maximum annual price</span><input name="max_price" value="{{ $filters['max_price'] ?? '' }}" type="number" min="0" step="50000" class="filter-input" placeholder="Max ₦"></label></div>
    </div>
    <fieldset>
        <legend class="filter-label">Bedrooms</legend>
        <div class="grid grid-cols-5 gap-1.5">@foreach([1,2,3,4,5] as $bed)<label class="relative"><input class="peer sr-only" type="radio" name="bedrooms" value="{{ $bed }}" @checked(($filters['bedrooms'] ?? null) == $bed)><span class="flex min-h-10 items-center justify-center rounded-md border border-[#D0D5DD] bg-white text-sm font-semibold text-[#475467] peer-checked:border-[#145FCC] peer-checked:bg-[#EAF2FF] peer-checked:text-[#145FCC]">{{ $bed }}{{ $bed === 5 ? '+' : '' }}</span></label>@endforeach</div>
    </fieldset>
    <div>
        <label class="filter-label" for="{{ $prefix }}-furnishing">Furnishing</label>
        <select id="{{ $prefix }}-furnishing" name="furnishing" class="filter-input"><option value="">Any furnishing</option><option value="unfurnished" @selected(($filters['furnishing'] ?? '') === 'unfurnished')>Unfurnished</option><option value="semi-furnished" @selected(($filters['furnishing'] ?? '') === 'semi-furnished')>Semi-furnished</option><option value="furnished" @selected(($filters['furnishing'] ?? '') === 'furnished')>Furnished</option></select>
    </div>
    <fieldset>
        <legend class="filter-label">Amenities</legend>
        <div class="grid gap-2.5">@foreach(['water' => 'Running water', 'security' => 'Security', 'parking' => 'Parking', 'power' => 'Backup power', 'road' => 'Good access road'] as $key => $label)<label class="flex min-h-10 cursor-pointer items-center gap-2 text-sm text-[#475467]"><input type="checkbox" name="amenities[]" value="{{ $key }}" @checked(in_array($key, $filters['amenities'] ?? [])) class="size-5 rounded border-[#98A2B3] text-[#145FCC] focus:ring-[#145FCC]">{{ $label }}</label>@endforeach</div>
    </fieldset>
</div>
