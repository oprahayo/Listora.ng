# Changelog

All notable changes to Listora.ng are documented here.

## [Sprint 1] - 2026-07-19

### Added

- Laravel 13 public platform foundation using Blade, Alpine.js, Tailwind CSS and Vite
- Sprint 1 database schema, factories and 24-property Nigerian demonstration dataset
- Responsive homepage, icon categories, featured property cards and trust section
- Validated public search, filters, sorting, grid/list modes and pagination
- Published-only property details with optimized image gallery, facts, amenities, agent state, save and share actions
- Guest saved properties using versioned browser local storage and a batched public summary endpoint
- Role-aware login modal/bottom sheet with validation, Nigerian phone normalization, real sessions and generic failure language
- Rate-limited development OTP abstraction and local log dispatcher
- PWA manifest, generated icons, service worker, offline fallback and cache privacy boundary
- SEO titles/descriptions, canonical and Open Graph tags, property JSON-LD, sitemap and robots policy
- Automated feature/unit tests and responsive browser acceptance screenshots
- Setup, deployment, PWA and performance documentation

### Security

- Added CSRF protection and network-only CSRF refresh for cached public pages
- Prevented draft and archived property exposure through both list and route binding queries
- Prevented authenticated HTML from entering the public service-worker cache
- Added login/OTP rate limits and role matching
- Kept agent private contact information out of public responses

### Deferred

- Registration, verification, onboarding, dashboards, property management, inspections, payments, chat and all later sprint modules
