@props(['activeRole'])
@php($navigation = app(\App\Domain\Navigation\WorkspaceNavigation::class))
<div x-cloak x-show="profileSheet" @keydown.escape.window="profileSheet = false" class="fixed inset-0 z-[85]" role="dialog" aria-modal="true" aria-label="Account menu">
    <button type="button" @click="profileSheet = false" class="absolute inset-0 bg-[#081B38]/65" aria-label="Close account menu"></button>
    <section x-show="profileSheet" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100" class="absolute inset-x-0 bottom-0 max-h-[84vh] overflow-y-auto rounded-t-2xl bg-white px-4 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-3 shadow-2xl md:inset-x-auto md:bottom-5 md:right-5 md:w-[360px] md:rounded-2xl">
        <div class="mx-auto h-1 w-10 rounded-full bg-[#D0D5DD] md:hidden"></div>
        <div class="mt-4 flex items-start justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-lg font-semibold">{{ auth()->user()->name }}</h2><p class="mt-1 truncate text-sm text-[#667085]">{{ auth()->user()->email ?: '+'.auth()->user()->phone }}</p><p class="mt-2 text-xs font-medium text-[#145FCC]">{{ $navigation->label($activeRole) }} workspace</p></div><button type="button" @click="profileSheet = false" class="touch-icon" aria-label="Close"><x-icon name="x" /></button></div>
        <nav class="mt-4 grid gap-1" aria-label="Account menu">
            @if(auth()->user()->roles()->count() > 1)<a href="{{ route('workspace.index') }}" class="menu-link"><x-icon name="users" />Switch workspace</a>@endif
            <a href="{{ route('notifications.index') }}" class="menu-link"><x-icon name="bell" />Notifications</a>
            <a href="{{ route($navigation->moreRoute($activeRole)) }}" class="menu-link"><x-icon name="settings" />Account settings</a>
            <a href="{{ route('properties.index') }}" class="menu-link"><x-icon name="search" />Browse public properties</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="menu-link w-full text-[#C92A2A]"><x-icon name="logout" />Sign out</button></form>
        </nav>
    </section>
</div>
