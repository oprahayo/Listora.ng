<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A2856">
    <meta name="robots" content="noindex">
    <title>You are offline | Listora.ng</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FA] font-sans text-[#182230] antialiased">
    <header class="bg-[#0A2856] px-4 py-4 text-center text-xl font-semibold text-white">Listora<span class="text-[#8FB7FF]">.ng</span></header>
    <main class="mx-auto flex min-h-[75vh] max-w-xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
        <span class="flex size-16 items-center justify-center rounded-2xl bg-[#EAF2FF] text-[#145FCC]"><x-icon name="wifi-off" class="size-8" /></span>
        <h1 class="mt-6 text-[26px] font-semibold tracking-tight text-[#182230] md:text-3xl">You’re offline</h1>
        <p class="mt-4 leading-7 text-[#667085]">Some saved pages are still available. Reconnect for current prices, availability and account features.</p>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row"><button type="button" onclick="window.location.reload()" class="btn-primary">Try again</button><a href="/" class="btn-secondary">Open cached home</a></div>
    </main>
</body>
</html>
