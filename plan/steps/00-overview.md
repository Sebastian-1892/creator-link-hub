# Creator Link Hub – Step-Index (MVP)

**Single Source of Truth für den vereinbarten Stack.**

| Bereich | Technologie |
|--------|-------------|
| Backend | PHP 8.3, Laravel 11 |
| App-UI | Livewire 3, Alpine.js, Tailwind CSS |
| Public Pages | Blade SSR |
| Auth | Laravel Breeze (Livewire), E-Mail-Verifizierung |
| Billing | Laravel Cashier (Stripe), Stripe Tax |
| Admin | Filament 3 |
| RBAC | spatie/laravel-permission |
| DB | PostgreSQL 16 |
| Cache/Queue | Redis |
| Mail | Postmark / Resend (env) |
| Storage | S3-kompatibel (Avatare) |
| Tests | Pest 3, Larastan, Pint |
| CI | GitHub Actions |

## Nummerierte Steps

| Step | Datei | Kurzbeschreibung |
|------|--------|------------------|
| 01 | [01-foundation-setup.md](01-foundation-setup.md) | Projekt, Postgres, Redis, CI, Qualitätstools |
| 02 | [02-data-model-migrations.md](02-data-model-migrations.md) | Migrationen, Models, Factories, Theme-Seeder |
| 03 | [03-auth-workspaces-onboarding.md](03-auth-workspaces-onboarding.md) | Breeze, Workspace nach Signup, Onboarding |
| 04 | [04-profile-theme-system.md](04-profile-theme-system.md) | Profil, Slug, Avatar, Themes |
| 05 | [05-link-builder.md](05-link-builder.md) | Links, DnD, Preview |
| 06 | [06-public-profile-page.md](06-public-profile-page.md) | Public SSR, OG, Sitemap, Cache |
| 07 | [07-click-tracking-analytics.md](07-click-tracking-analytics.md) | `/go/`, Analytics-Dashboard |
| 08 | [08-billing-stripe.md](08-billing-stripe.md) | Cashier, Checkout, Portal, Webhooks |
| 09 | [09-plan-limits-rbac.md](09-plan-limits-rbac.md) | Limits, Policies, Branding |
| 10 | [10-marketing-website.md](10-marketing-website.md) | Landing, Pricing, FAQ |
| 11 | [11-admin-panel.md](11-admin-panel.md) | Filament Admin |
| 12 | [12-notifications-mail.md](12-notifications-mail.md) | Mailables, Events |
| 13 | [13-security-legal-dsgvo.md](13-security-legal-dsgvo.md) | Rate-Limits, Legal, DSGVO |
| 14 | [14-tests-quality.md](14-tests-quality.md) | Pest, Larastan, Pint |
| 15 | [15-performance-caching.md](15-performance-caching.md) | Cache-Tags, Indexe |
| 16 | [16-deployment-operations.md](16-deployment-operations.md) | Server, Nginx, Backups |
| 17 | [17-launch-readiness.md](17-launch-readiness.md) | Go-Live, Smoke-Tests |
| 99 | [99-roadmap-post-mvp.md](99-roadmap-post-mvp.md) | Post-MVP Roadmap |

**Reihenfolge:** 01 → 02 → … → 17. Post-MVP: 99 (Referenz).
