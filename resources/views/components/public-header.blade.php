<header class="bg-[#0B2A5B] text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4 lg:h-18">
            <a href="{{ route('home') }}" class="shrink-0 text-xl font-extrabold tracking-tight focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white" aria-label="Listora home">
                Listora<span class="text-[#8FB7FF]">.ng</span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
                <a href="{{ route('properties.index') }}" class="nav-link {{ request()->routeIs('properties.*') ? 'bg-white/12 text-white' : '' }}">Rent</a>
                <a href="{{ route('properties.index', ['type' => 'office']) }}" class="nav-link">Commercial</a>
                <a href="{{ route('join') }}#agents" class="nav-link">For Agents</a>
                <a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a>
            </nav>

            <div class="hidden min-w-0 flex-1 items-center justify-end gap-2 md:flex">
                <form action="{{ route('properties.index') }}" method="GET" class="hidden items-center xl:flex">
                    <label class="sr-only" for="header-location">Location</label>
                    <div class="relative">
                        <x-icon name="map-pin" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#8FB7FF]" />
                        <select id="header-location" name="city" onchange="this.form.submit()" class="h-10 appearance-none rounded-l-lg border border-white/20 bg-white/8 pl-9 pr-8 text-sm text-white focus:border-white focus:outline-none">
                            <option value="" class="text-[#172033]">Nigeria</option>
                            @foreach(['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Ado-Ekiti'] as $city)
                                <option value="{{ $city }}" @selected(request('city') === $city) class="text-[#172033]">{{ $city }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 size-3.5 -translate-y-1/2 text-[#8FB7FF]" />
                    </div>
                    <label class="sr-only" for="header-search">Search properties</label>
                    <div class="relative">
                        <input id="header-search" name="q" value="{{ request('q') }}" class="h-10 w-44 rounded-r-lg border-y border-r border-white/20 bg-white px-3 pr-9 text-sm text-[#172033] placeholder:text-[#98A2B3] focus:outline-2 focus:outline-[#8FB7FF]" placeholder="Search properties">
                        <button class="absolute right-1 top-1 flex size-8 items-center justify-center rounded-md text-[#0B2A5B] hover:bg-[#EEF4FF]" aria-label="Search"><x-icon name="search" class="size-4" /></button>
                    </div>
                </form>

                <a href="{{ route('saved') }}" class="header-icon" aria-label="Saved properties" title="Saved properties">
                    <x-icon name="bookmark" class="size-5" />
                    <span x-show="savedIds.length" x-text="savedIds.length" class="absolute -right-1 -top-1 min-w-4 rounded-full bg-white px-1 text-center text-[10px] font-bold text-[#0B2A5B]"></span>
                </a>

                @guest
                    <button type="button" @click="openLogin()" class="header-icon" aria-label="Sign in to use messages" aria-disabled="true" title="Sign in to use messages"><x-icon name="chat" class="size-5" /></button>
                    <button type="button" @click="openLogin()" class="hidden h-10 items-center rounded-lg border border-white/35 px-4 text-sm font-semibold transition hover:bg-white/10 lg:inline-flex">Sign In</button>
                    <button type="button" @click="openLogin('agent')" class="hidden h-10 items-center gap-2 rounded-lg bg-[#155EEF] px-4 text-sm font-semibold transition hover:bg-[#0E4CC9] xl:inline-flex"><x-icon name="plus" class="size-4" />List Property</button>
                @else
                    <span class="hidden max-w-32 truncate text-sm font-semibold lg:inline">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">@csrf<button class="h-10 rounded-lg border border-white/35 px-3 text-sm font-semibold hover:bg-white/10">Sign out</button></form>
                @endguest
            </div>

            <div class="flex items-center gap-1 md:hidden">
                <a href="{{ route('properties.index') }}" class="header-icon" aria-label="Choose location"><x-icon name="map-pin" class="size-5" /></a>
                @guest
                    <button type="button" @click="openLogin()" class="header-icon" aria-label="Sign in"><x-icon name="user" class="size-5" /></button>
                @else
                    <form action="{{ route('logout') }}" method="POST">@csrf<button class="header-icon" aria-label="Sign out"><x-icon name="user" class="size-5" /></button></form>
                @endguest
                <button type="button" @click="mobileMenuOpen = true" class="header-icon" aria-label="Open menu"><x-icon name="menu" class="size-5" /></button>
            </div>
        </div>

        @unless(request()->routeIs('properties.index'))
            <form action="{{ route('properties.index') }}" method="GET" class="pb-4 md:hidden">
                <label class="sr-only" for="mobile-header-search">Search properties</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-[#667085]" />
                    <input id="mobile-header-search" name="q" class="h-12 w-full rounded-lg border-0 bg-white pl-11 pr-4 text-base text-[#172033] placeholder:text-[#98A2B3] focus:outline-2 focus:outline-offset-2 focus:outline-[#8FB7FF]" placeholder="Area, city or property">
                </div>
            </form>
        @endunless
    </div>

    <div x-cloak x-show="mobileMenuOpen" @keydown.escape.window="mobileMenuOpen = false" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <button @click="mobileMenuOpen = false" class="absolute inset-0 bg-[#081B38]/70" aria-label="Close menu"></button>
        <div x-show="mobileMenuOpen" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" class="absolute inset-y-0 right-0 w-[84%] max-w-sm bg-white p-5 text-[#172033] shadow-2xl">
            <div class="flex items-center justify-between"><span class="text-xl font-extrabold text-[#0B2A5B]">Listora.ng</span><button @click="mobileMenuOpen = false" class="touch-icon" aria-label="Close menu"><x-icon name="x" /></button></div>
            <nav class="mt-8 grid gap-2" aria-label="Mobile menu">
                <a class="menu-link" href="{{ route('properties.index') }}"><x-icon name="search" />Rent a property</a>
                <a class="menu-link" href="{{ route('properties.index', ['type' => 'office']) }}"><x-icon name="office" />Commercial</a>
                <a class="menu-link" href="{{ route('join') }}#agents"><x-icon name="users" />For Agents</a>
                <a class="menu-link" href="{{ route('home') }}#how-it-works"><x-icon name="info" />How It Works</a>
            </nav>
            @guest<button @click="mobileMenuOpen = false; openLogin()" class="btn-primary mt-8 w-full">Sign In</button>@endguest
        </div>
    </div>
</header>
