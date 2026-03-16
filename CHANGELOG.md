# Changelog

All notable changes to `page-speed` will be documented in this file.

## 1.0.0 - 2026-03-16

### First stable release

A Laravel package for tracking and analyzing web page performance using Google PageSpeed Insights API with test history and an interactive dashboard.

### Added

- **Google PageSpeed Insights API v5 integration** — run tests for mobile and desktop strategies with category selection (Performance, Accessibility, Best Practices, SEO)
- **Dashboard** — interactive panel available at a configurable path (`/page-speed`) with a test form, automatic application route discovery, strategy toggle, and category filter
- **Detailed result view** — animated circular score gauges with color coding (green 90+, orange 50-89, red 0-49), Core Web Vitals metrics (FCP, LCP, TBT, CLS, Speed Index), expandable audits with savings tables
- **Test history** — database-backed result storage with a configurable limit (default 50), sortable history table, full API response stored as JSON
- **`PageSpeedTest` model** — migration with an index on (url, strategy, created_at), score fields 0-100, JSON metrics, and raw Lighthouse response
- **`PageSpeedApiService`** — `runTest()`, `runTests()`, `getTestableUrls()`, `extractAudits()` methods with automatic filtering of internal and parameterized routes
- **`AuditFormatter` helper** — formatting for bytes, milliseconds, and audit cell values
- **Authorization** — support for custom callback (`PageSpeedPanel::auth()`), Laravel Gate (`viewPageSpeed`), and open access as fallback
- **Authorization middleware** — route protection with 403 response for unauthorized users
- **Configuration** — API key, path, middleware, domain, default strategy, URL, history limit, timeout, gate, panel enable/disable via environment variables
- **Service Provider** — auto-loaded migrations, singleton service registration, publishable assets (`config`, `views`)
- **Routes** — `GET /` (dashboard), `POST /run` (AJAX test execution), `GET /{id}` (details), `DELETE /{id}` (delete result)
- **Blade views** — responsive layout with Tailwind CSS and Alpine.js (CDN), components: score cards, score gauges, metric cards, audit items
- **Tests** — Pest PHP with Orchestra Testbench, architecture, model, API service, and formatter tests
