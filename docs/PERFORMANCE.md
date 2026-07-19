# Sprint 1 performance audit

Measured on 19 July 2026 using the Laravel production Vite build, PHP 8.4.5 and the local Artisan server. Local response times are useful regression baselines, not production network claims.

## Production asset budget

| Asset | Raw | Gzip | Sprint 1 budget | Result |
| --- | ---: | ---: | ---: | --- |
| Application JavaScript | 49.99 KB | 17.69 KB | under 120 KB compressed | Pass |
| Application custom CSS | 77.45 KB | 15.60 KB | under 80 KB compressed | Pass |
| Font stylesheet | 2.35 KB | 0.38 KB | — | Informational |

Only the required Alpine runtime and application code ship in the JavaScript bundle. There is no carousel, animation library, icon font or SPA runtime.

## Listing images

| Format | Largest 720×480 thumbnail |
| --- | ---: |
| AVIF | 2.15 KB |
| WebP | 4.91 KB |
| JPEG fallback | 18.06 KB |

All formats remain below the 100 KB thumbnail target. Cards include explicit width and height, use responsive `<picture>` sources and lazy-load noncritical images.

## Server-rendered HTML

| Page | Raw HTML | Gzip equivalent |
| --- | ---: | ---: |
| Homepage with 8 cards | 72.47 KB | 9.77 KB |
| Listings with 12 cards | 92.99 KB | 11.06 KB |

Listings are limited to 12 initial cards and paginate the remaining records.

## Local response timing

Five homepage requests with `curl` produced start-transfer times of 86.5 ms, 15.5 ms, 33.5 ms, 24.5 ms and 16.2 ms. The median local TTFB was **24.5 ms**.

Exact measurement commands:

```bash
npm run build
gzip -c public/build/assets/app-*.js | wc -c
gzip -c public/build/assets/app-*.css | wc -c
for i in 1 2 3 4 5; do curl -sS -o /dev/null -w '%{time_starttransfer} %{time_total}\n' http://127.0.0.1:8000/; done
```

## Browser quality checks

- Desktop viewport: 1440×1000
- Mobile viewport: 390×844
- No console errors or warnings during homepage, listings, saved-property and property-detail checks
- Modal, bottom sheet, guest save and view-switch transitions are CSS-only and capped at 200 ms
- `prefers-reduced-motion` reduces transition and animation duration

Lighthouse was not available because the delivery host had no Chrome/Chromium executable. This audit is the requested equivalent measurement and should be supplemented with field-host Lighthouse runs after deployment over HTTPS.
