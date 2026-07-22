<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A2856">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Browse clear, verified property listings from agents across Nigeria with Listora.ng.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="/images/icons/listora-192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="/images/icons/listora-192.png">
    <meta property="og:site_name" content="Listora.ng">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'Listora.ng — Find your next property')">
    <meta property="og:description" content="@yield('meta_description', 'Browse clear, verified property listings from agents across Nigeria with Listora.ng.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <title>@yield('title', 'Listora.ng — Find your next property')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="listoraApp()" x-init="init()" class="min-h-screen bg-[#F7F8FA] text-[#182230] antialiased">
    <a href="#main-content" class="sr-only z-[100] rounded-md bg-white p-3 text-[#0A2856] focus:not-sr-only focus:fixed focus:left-3 focus:top-3">Skip to content</a>
    <div x-cloak x-show="!online" class="sticky top-0 z-[60] flex items-center justify-center gap-2 bg-[#FFF4E8] px-4 py-2 text-center text-sm font-medium text-[#B75D00]" role="status"><x-icon name="wifi-off" class="size-4" />You’re offline. Your basic progress is saved on this device.</div>
    <x-public-header />
    <main id="main-content">{{ $slot }}</main>
    <x-public-footer />
    @unless(request()->routeIs('properties.show'))<x-mobile-nav />@endunless
    <x-login-modal />
    <div x-cloak x-show="toast" x-transition class="fixed bottom-24 left-1/2 z-[80] -translate-x-1/2 rounded-lg bg-[#182230] px-4 py-3 text-sm font-semibold text-white shadow-xl md:bottom-8" role="status" x-text="toast"></div>
</body>
</html>
