# Listora.ng

Listora.ng is a mobile-first Nigerian property listing and property-management platform. Sprint 1 delivers the public platform foundation with server-rendered Laravel pages, lightweight Alpine interactions, local guest saves, and a secure PWA cache boundary.

## Sprint 1 status

Completed:

- Responsive public homepage, icon categories and featured listings
- Searchable, validated and paginated property listings with desktop/mobile filters
- Public property details, optimized image gallery, facts, amenities and agent verification state
- Guest saves in `localStorage` with one-request hydration on `/saved`
- Desktop login popup and mobile login sheet with focus trap, Escape close and focus restoration
- Validated role-aware email/Nigerian-phone login, remember-me sessions and rate limiting
- Development OTP dispatcher abstraction that stores only a hash and writes the code to the local log
- PWA manifest, icons, service worker, offline fallback and online/offline UI state
- SEO metadata, property JSON-LD, sitemap and robots policy
- Original AVIF/WebP/JPEG demonstration assets and reproducible asset generator
- Factories, 3 agents, 24 Nigerian property listings and automated feature/unit coverage

No Sprint 2 work is included.

## Public frontend preview

The frontend-only GitHub Pages preview is published at:

**https://oprahayo.github.io/Listora.ng/**

GitHub Pages cannot execute PHP, so this preview contains statically exported public pages. Browsing, responsive layouts, property details, modal interactions and device-local saves work. Authentication, OTP, live filters and other Laravel endpoints must be tested locally.

## Requirements

- PHP 8.3 or newer with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` and `gd`
- Composer 2.7+
- Node.js 20+ and npm
- MySQL 8+ or MariaDB 10.6+ for production
- SQLite is supported for local development and automated tests

The application is Laravel 13.20, Blade, Alpine.js, Tailwind CSS 4 and Vite. It does not use React, Vue or an SPA router.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
```

Configure the MySQL values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=listora
DB_USERNAME=root
DB_PASSWORD=
```

Keep these Sprint 1 infrastructure defaults unless the hosting environment needs different drivers:

```dotenv
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Create the schema and demonstration data:

```bash
php artisan migrate --seed
```

The optimized demonstration assets are committed. To reproduce them with PHP GD:

```bash
php scripts/generate_demo_assets.php
```

## Local development

Use two terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

Open [http://localhost:8000](http://localhost:8000).

Seeded development sign-ins all use the password `password`:

| Role | Identifier |
| --- | --- |
| Agent | `adaeze@listora.test` |
| Landlord | `landlord@listora.test` |
| Tenant | `tenant@listora.test` |

The Sprint 1 login endpoint creates a real Laravel session. Dashboards and onboarding remain deferred, so a successful sign-in returns to the same public page.

## Asset compilation

Development assets:

```bash
npm run dev
```

Production assets:

```bash
npm run build
```

Regenerate the committed GitHub Pages preview after public frontend changes:

```bash
php scripts/export_static_preview.php
```

The production build must exist in `public/build` before deployment. Vite is only used to compile local assets.

## Database and queues

Sprint 1 creates only these application tables:

- `users`
- `agents`
- `properties`
- `property_images`
- `property_amenities`

Guest saves intentionally have no database table. Sessions and cache use files, and the queue connection is synchronous. No queue worker is required for Sprint 1.

## Testing

Exact automated test command used for this sprint:

```bash
php artisan test
```

Result: **15 tests passed, 62 assertions**.

Additional verification commands used:

```bash
find app database routes -name '*.php' -print0 | xargs -0 -n1 php -l
npm run build
php artisan migrate:fresh --seed --no-interaction
php artisan route:list --except-vendor
```

Browser QA covered desktop/mobile rendering, modal open/close, role switching, focus restoration, guest save toggle, `/saved` hydration, grid/list switching, mobile filter sheet, the inspection information modal, guest chat gating and console warnings/errors.

## PWA and offline testing

Service workers require HTTPS in production; `localhost` is accepted as a secure development context.

1. Run `npm run build` so production JavaScript registers the service worker.
2. Run `php artisan serve` and visit the homepage, listings and a property detail.
3. Reload once after the service worker activates.
4. In browser developer tools, set the network to offline and revisit those URLs.
5. Confirm cached public content or `/offline` appears and sign-in/booking/chat controls are unavailable.
6. Return online and confirm current availability can refresh.

The service worker applies cache-first behavior to compiled/static assets, stale-while-revalidate to guest public pages and thumbnails, and network-only behavior to authentication and all mutations. Authenticated HTML is marked `private, no-store`; the public cache is cleared after login. A network-only CSRF refresh prevents cached public HTML from submitting an expired token.

## Production deployment

For cPanel-compatible hosting:

1. Point the domain document root to the Laravel `public` directory.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, the final HTTPS `APP_URL`, database credentials and a generated `APP_KEY`.
3. Run `composer install --no-dev --optimize-autoloader`.
4. Run `npm ci && npm run build` during the build stage, or upload the compiled `public/build` output.
5. Run `php artisan migrate --force`.
6. Ensure `storage` and `bootstrap/cache` are writable by PHP.
7. Run `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`.
8. Confirm `public/service-worker.js`, `public/manifest.webmanifest` and PWA icons are served with their correct MIME types.

No scheduler or queue worker is required in Sprint 1.

## Completed routes

| Method | Route | Purpose |
| --- | --- | --- |
| GET | `/` | Homepage |
| GET | `/properties` | Search, filters, sorting and pagination |
| GET | `/properties/{slug}` | Published property details |
| GET | `/saved` | Guest saved-property page |
| GET | `/saved/property-summaries` | Batched public summaries for valid saved IDs |
| POST | `/auth/login` | Validated development session login |
| POST | `/auth/otp/request` | Rate-limited development OTP dispatch |
| GET | `/auth/csrf-token` | Network-only CSRF refresh for cached public pages |
| POST | `/auth/logout` | Authenticated logout |
| GET | `/join` | Sprint 2 onboarding notice |
| GET | `/forgot-password` | Sprint 2 recovery notice |
| GET | `/offline` | Private-data-free offline fallback |
| GET | `/manifest.webmanifest` | PWA manifest |
| GET | `/service-worker.js` | PWA service worker |
| GET | `/sitemap.xml` | Published-property sitemap |

`public/robots.txt` supplies the crawler policy.

## Intentionally deferred

- Full registration, guided onboarding, password recovery and OTP verification: Sprint 2
- Agent verification workflow and role dashboards: Sprint 2+
- Property creation and listing management: Sprints 3–4
- Inspection scheduling and prospect management: Sprint 5
- Tenant onboarding and tenancy dashboard: Sprint 6
- Payments, receipts and reminders: Sprint 7
- Chat, complaints and notifications: Sprint 8
- Landlord dashboards, utilities, SaaS administration and deployment hardening: Sprints 9–12
- Google OAuth remains behind a visible “Coming soon” state; no Apple or Facebook login is included

## Performance results

See [docs/PERFORMANCE.md](docs/PERFORMANCE.md) for the reproducible production budget audit. Current production output is 17.69 KB gzip JavaScript and 15.60 KB gzip custom CSS; the largest WebP listing thumbnail is 4.9 KB.

## Screenshots

All acceptance screenshots are in [`docs/screenshots`](docs/screenshots):

- Desktop and mobile homepage
- Desktop and mobile listings
- Property details
- Desktop login modal and mobile login sheet
- Offline fallback

## Known limitations

- Demonstration images are intentionally original, generated architectural placeholders rather than real property photography.
- OTP delivery is local-log only and does not complete an OTP sign-in; the UI labels this clearly.
- Lighthouse could not run in the delivery environment because no Chrome/Chromium executable was installed. The equivalent asset, page-weight and local response-time measurements are documented instead.
- Offline content reflects its last public cache and explicitly warns that price and availability may be stale.
