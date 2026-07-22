@props(['items', 'activeRole'])
@php
    $navigation = app(\App\Domain\Navigation\WorkspaceNavigation::class);
    $unread = auth()->user()->unreadNotifications()->count();
@endphp
<aside class="private-desktop-nav hidden lg:flex">
    <a href="{{ route('dashboard') }}" class="px-3 text-xl font-semibold tracking-tight text-[#0A2856]">Listora<span class="text-[#145FCC]">.ng</span></a>
    <p class="mt-2 px-3 text-xs font-medium uppercase tracking-[.12em] text-[#667085]">{{ $navigation->label($activeRole) }}</p>
    <nav class="mt-6 grid gap-1" aria-label="Workspace navigation">
        @foreach($items as $item)
            @php($active = request()->routeIs($item['active']))
            <a href="{{ route($item['route']) }}" class="private-desktop-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif><x-icon :name="$item['icon']" class="size-5" /><span>{{ $item['label'] }}</span></a>
        @endforeach
        <a href="{{ route('notifications.index') }}" class="private-desktop-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"><span class="relative"><x-icon name="bell" class="size-5" /><x-private-notification-badge :count="$unread" /></span><span>Notifications</span></a>
    </nav>
    <div class="mt-auto border-t border-[#E4E7EC] pt-4">
        <button type="button" @click="profileSheet = true" class="flex w-full items-center gap-3 rounded-lg p-3 text-left hover:bg-[#F2F4F7]">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#EAF2FF] text-xs font-medium text-[#145FCC]">{{ collect(explode(' ', auth()->user()->name))->take(2)->map(fn($part)=>mb_strtoupper(mb_substr($part,0,1)))->join('') }}</span>
            <span class="min-w-0"><span class="block truncate text-sm font-medium text-[#182230]">{{ auth()->user()->name }}</span><span class="block truncate text-xs text-[#667085]">{{ auth()->user()->email ?: '+'.auth()->user()->phone }}</span></span>
        </button>
        <form action="{{ route('logout') }}" method="POST">@csrf<button class="private-desktop-link mt-1 w-full"><x-icon name="logout" class="size-5" />Sign out</button></form>
    </div>
</aside>
