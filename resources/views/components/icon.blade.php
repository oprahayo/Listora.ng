@props(['name', 'class' => 'size-5'])

<svg {{ $attributes->merge(['class' => $class, 'aria-hidden' => 'true', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}>
    @switch($name)
        @case('home') <path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/> @break
        @case('search') <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/> @break
        @case('map-pin') <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/> @break
        @case('bookmark') <path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h9A1.5 1.5 0 0 1 18 4.5V21l-6-4-6 4Z"/> @break
        @case('heart') <path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.5 1-1a5.5 5.5 0 0 0 0-7.8Z"/> @break
        @case('chat') <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8M8 13h5"/> @break
        @case('user') <circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('building') <path d="M4 21V6l8-3 8 3v15M8 9h2m4 0h2M8 13h2m4 0h2M8 17h2m4 0h2"/> @break
        @case('key') <circle cx="8" cy="15" r="4"/><path d="m11 12 8-8m-3 3 2 2m-5 1 2 2"/> @break
        @case('duplex') <path d="m3 11 9-8 9 8M5 10v10h14V10M12 3v17M8 14h1m6 0h1"/> @break
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/> @break
        @case('shop') <path d="M4 10v10h16V10M3 4h18l-2 6a3 3 0 0 1-5 1 3 3 0 0 1-4 0 3 3 0 0 1-5-1Z"/><path d="M9 20v-5h6v5"/> @break
        @case('office') <path d="M5 21V4h10v17M15 9h4v12M8 8h4M8 12h4M8 16h4"/> @break
        @case('shield-check') <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/> @break
        @case('lock') <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/> @break
        @case('settings') <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/> @break
        @case('filter') <path d="M4 6h16M7 12h10M10 18h4"/> @break
        @case('sort') <path d="M8 6h10M8 12h7M8 18h4M4 4v16m0 0-2-2m2 2 2-2"/> @break
        @case('grid') <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/> @break
        @case('list') <path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.5" cy="6" r=".5" fill="currentColor"/><circle cx="3.5" cy="12" r=".5" fill="currentColor"/><circle cx="3.5" cy="18" r=".5" fill="currentColor"/> @break
        @case('bed') <path d="M3 19v-8m18 8v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3h18M7 11V7h4a2 2 0 0 1 2 2v2"/> @break
        @case('bath') <path d="M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5ZM5 12V6a3 3 0 0 1 6 0"/> @break
        @case('car') <path d="m5 17-1-5 2-5h12l2 5-1 5M3 17h18M6 17v3M18 17v3"/><circle cx="7" cy="13" r="1"/><circle cx="17" cy="13" r="1"/> @break
        @case('ruler') <path d="m4 18 14-14 3 3L7 21Z"/><path d="m14 8 2 2m-5 1 2 2m-5 1 2 2"/> @break
        @case('arrow-left') <path d="m15 18-6-6 6-6M9 12h12"/> @break
        @case('share') <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('x') <path d="M6 6l12 12M18 6 6 18"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/> @break
        @case('wifi-off') <path d="m2 2 20 20M8.5 16.5A5 5 0 0 1 12 15c1.4 0 2.6.5 3.5 1.5M5 12.5A10 10 0 0 1 8 10.6M3 8.5A15 15 0 0 1 6 6.6M14.5 9.3A10 10 0 0 1 19 12.5M12 21h.01"/> @break
        @case('info') <circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/> @break
        @case('chevron-down') <path d="m6 9 6 6 6-6"/> @break
        @case('chevron-right') <path d="m9 18 6-6-6-6"/> @break
        @case('arrow-right') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
        @case('more-horizontal') <circle cx="5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1" fill="currentColor" stroke="none"/> @break
        @case('eye') <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
        @default <circle cx="12" cy="12" r="9"/> <path d="M12 8v4M12 16h.01"/>
    @endswitch
</svg>
