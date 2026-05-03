# Creator Link Hub (MVP)

Link-in-Bio-SaaS mit **Laravel 11**, **Livewire/Volt**, **Filament Admin**, **PostgreSQL** (empfohlen) / SQLite (lokal), **Redis** (optional), **Stripe** (Laravel Cashier).

## Funktionen (MVP)

- Registrierung / Login / E-Mail-Verifizierung (Breeze)
- Workspace + Profil + **öffentliche Seite** unter `/p/{slug}`
- **Links** mit Tracking-Redirect `/go/{link}`
- **Profil-Vorlagen** (30 Farb-Themes) + Profilbild (öffentlicher `storage`-Disk)
- **Analytics** (Klicks pro Tag, Top-Links)
- **Pläne & Limits** (Free: max. 10 Links, Plattform-Branding)
- **Stripe** Checkout & Kundenportal (Price-IDs in `.env`)
- **Admin** `/admin` (nur `users.is_admin = true`) — Sprachwahl **English, Deutsch, Français, Italiano** im Benutzermenü (Filament-Oberfläche)
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

## Server-Installation (Debian / Ubuntu)

Ziel: einmaliges Setup mit **Nginx**, **PHP-FPM** (8.2–8.4 aus den **offiziellen Paketquellen**, kein PPA), **PostgreSQL oder MariaDB**, optional **Redis**, **Composer**, **Node/npm** (Vite-Build), Laravel-Migrationen und optional **SSL (Certbot)**.

Repository klonen:

```bash
git clone https://github.com/Sebastian-1892/creator-link-hub.git
cd creator-link-hub
```

Install-Skript **als root** aus dem **Projektroot** starten (nicht aus dem Unterordner `scripts/` mit `scripts/scripts/...`):

```bash
sudo bash scripts/install-debian-server.sh
```

Das Skript ist **interaktiv** (deutsch) und fragt u. a. ab:

- System-Update (`apt upgrade`)
- Installationsverzeichnis, Git-URL und Branch
- Domain / `APP_URL` / `APP_NAME`
- Datenbank (PostgreSQL oder MariaDB), Benutzer, Passwort, optional Löschen gleichnamiger Test-DB
- Redis, NodeSource (Node 20) für den Frontend-Build
- Stripe- und SMTP-Werte (optional, leer lassen möglich)
- **Administrator** für Filament (`/admin`): E-Mail, Anzeigename, Passwort (min. 8 Zeichen)
- optional Demo-Nutzer `creator@example.com` (nur für Tests)
- Nginx-Site, Supervisor (Queue), Cron (Scheduler), Certbot

**Wichtig:** Datenbank-Passwort ohne einfaches `'` und ohne `"`. Nach dem Setup: Kurzüberblick in [`docs/deployment.md`](docs/deployment.md), Go-Live in [`docs/launch-runbook.md`](docs/launch-runbook.md).

### Updates aus Git (bestehende Installation)

Ohne Datenbank oder `.env` zu zerstören — nur Code ziehen, Abhängigkeiten und **ausstehende Migrationen**:

```bash
cd creator-link-hub   # dein Installationspfad
bash scripts/update-from-git.sh
```

Optionen: `bash scripts/update-from-git.sh --help` (u. a. `--dev` für Composer mit Dev-Paketen, `--yes` bei lokalem „dirty“ Git).

## Umgebungsvariablen

Siehe [`.env.example`](.env.example) — insbesondere `STRIPE_*`, `STRIPE_PRICE_STARTER`, `STRIPE_PRICE_PRO`, Webhook `STRIPE_WEBHOOK_SECRET`.

## CI

GitHub Actions: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

## Lizenz

Proprietär / nach Bedarf — Projekt-MVP.
