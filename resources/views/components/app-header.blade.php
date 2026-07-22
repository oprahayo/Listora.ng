@props(['title', 'backUrl' => null, 'activeRole'])
@php
    $initials = collect(preg_split('/\s+/', trim(auth()->user()->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
    $unread = auth()->user()->unreadNotifications()->count();
@endphp
<header class="private-app-header lg:hidden">
    <div class="flex h-14 items-center justify-between gap-2 px-4">
        <div class="flex min-w-11 flex-1 items-center">
            @if($backUrl)
                <a href="{{ $backUrl }}" class="private-header-button" aria-label="Back"><x-icon name="arrow-left" class="size-[22px]" /></a>
            @else
                <a href="{{ route('dashboard') }}" class="text-[17px] font-semibold tracking-tight text-white" aria-label="Listora dashboard">Listora<span class="text-[#8FB7FF]">.ng</span></a>
            @endif
        </div>
        <p class="max-w-[42%] truncate text-center text-sm font-medium text-white">{{ $title }}</p>
        <div class="flex flex-1 items-center justify-end gap-1">
            <a href="{{ route('notifications.index') }}" class="private-header-button relative" aria-label="Notifications"><x-icon name="bell" class="size-[21px]" /><x-private-notification-badge :count="$unread" /></a>
            <button type="button" @click="profileSheet = true" class="ml-1 flex size-9 items-center justify-center rounded-full border border-white/30 bg-white/10 text-xs font-medium text-white" aria-label="Open account menu">{{ $initials ?: 'U' }}</button>
        </div>
    </div>
</header>
