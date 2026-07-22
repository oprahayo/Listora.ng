@php
    $workspaceNavigation = app(\App\Domain\Navigation\WorkspaceNavigation::class);
    $activeRole = $workspaceNavigation->activeRole(auth()->user(), session('active_role'));
    $workspaceItems = $workspaceNavigation->items($activeRole);
    $appTitle = trim($__env->yieldContent('app_title')) ?: $workspaceNavigation->label($activeRole);
    $appBack = trim($__env->yieldContent('app_back')) ?: null;
    $hideWorkspaceNavigation = trim($__env->yieldContent('hide_app_navigation')) === '1';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A2856">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', $appTitle.' | Listora.ng')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="listoraApp()" x-init="init()" class="min-h-screen bg-[#F7F8FA] text-[#182230] antialiased">
    <a href="#app-content" class="sr-only z-[100] rounded-md bg-white p-3 text-[#0A2856] focus:not-sr-only focus:fixed focus:left-3 focus:top-3">Skip to content</a>
    <div x-cloak x-show="!online" class="fixed inset-x-0 top-0 z-[80] flex items-center justify-center gap-2 bg-[#FFF4E8] px-4 py-2 text-center text-xs font-medium text-[#B75D00]" role="status"><x-icon name="wifi-off" class="size-4" />You’re offline. Your basic progress is saved on this device.</div>
    <div class="private-app-shell">
        @unless($hideWorkspaceNavigation)<x-app-desktop-nav :items="$workspaceItems" :active-role="$activeRole" />@endunless
        <div class="min-w-0 flex-1">
            <x-app-header :title="$appTitle" :back-url="$appBack" :active-role="$activeRole" />
            <main id="app-content" class="private-app-content">{{ $slot }}</main>
            @unless($hideWorkspaceNavigation)<x-app-bottom-nav :items="$workspaceItems" />@endunless
        </div>
    </div>
    <x-app-profile-sheet :active-role="$activeRole" />
    <div x-cloak x-show="toast" x-transition class="fixed bottom-20 left-1/2 z-[90] -translate-x-1/2 rounded-lg bg-[#182230] px-4 py-3 text-sm font-semibold text-white shadow-xl md:bottom-8" role="status" x-text="toast"></div>
</body>
</html>
