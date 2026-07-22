<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-[#E4E7EC] bg-white/95 pb-[max(.4rem,env(safe-area-inset-bottom))] pt-1.5 shadow-[0_-6px_20px_rgba(11,42,91,.08)] backdrop-blur md:hidden" aria-label="Bottom navigation">
    <div class="grid grid-cols-4">
        <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'text-[#145FCC]' : '' }}"><x-icon name="home" /><span>Home</span></a>
        <a href="{{ route('properties.index') }}" class="mobile-nav-item {{ request()->routeIs('properties.*') ? 'text-[#145FCC]' : '' }}"><x-icon name="search" /><span>Browse</span></a>
        <a href="{{ route('saved') }}" class="mobile-nav-item {{ request()->routeIs('saved') ? 'text-[#145FCC]' : '' }}"><x-icon name="heart" /><span>Saved</span></a>
        @guest
            <button type="button" @click="openLogin()" class="mobile-nav-item"><x-icon name="user" /><span>Account</span></button>
        @else
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="mobile-nav-item w-full"><x-icon name="user" /><span>Account</span></button></form>
        @endguest
    </div>
</nav>
