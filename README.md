# Creator Link Hub (MVP)

Link-in-Bio-SaaS mit **Laravel 11**, **Livewire/Volt**, **Filament Admin**, **PostgreSQL** (empfohlen) / SQLite (lokal), **Redis** (optional), **Stripe** (Laravel Cashier).

## Funktionen (MVP)

- Registrierung / Login / E-Mail-Verifizierung (Breeze)
- Workspace + Profil + **öffentliche Seite** unter `/p/{slug}`
- **Links** mit Tracking-Redirect `/go/{link}`
- **Themes** (6 Presets) + Profilbild (öffentlicher `storage`-Disk)
- **Analytics** (Klicks pro Tag, Top-Links)
- **Pläne & Limits** (Free: max. 10 Links, Plattform-Branding)
- **Stripe** Checkout & Kundenportal (Price-IDs in `.env`)
- **Admin** `/admin` (nur `users.is_admin = true`)
- Marketing: `/`, `/pricing`, `/faq`, Legal-Seiten, Cookie-Banner (Stub)

Detaillierte Ablauf-Schritte: [`plan/steps/00-overview.md`](plan/steps/00-overview.md)

## Lokale Entwicklung

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # oder PostgreSQL konfigurieren
php artisan migrate --seed
php artisan storage:link
npm install && npm run build     # Vite-Assets (für UI ohne Fehler)
php artisan serve
```

**Demo-Logins (nach `migrate --seed`):**

- Admin: `admin@example.com` / `password` → Filament `/admin`
- Creator: `creator@example.com` / `password`

## Tests & Qualität

```bash
php artisan test
./vendor/bin/pint
./vendor/bin/phpstan analyse
```

## Umgebungsvariablen

Siehe [`.env.example`](.env.example) — insbesondere `STRIPE_*`, `STRIPE_PRICE_STARTER`, `STRIPE_PRICE_PRO`, Webhook `STRIPE_WEBHOOK_SECRET`.

## CI

GitHub Actions: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

## Lizenz

Proprietär / nach Bedarf — Projekt-MVP.
