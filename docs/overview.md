# Projektüberblick — Creator Link Hub

Link-in-Bio-SaaS mit **Laravel 11**, **Livewire/Volt**, **Filament Admin**, **PostgreSQL** (lokal/dev) bzw. **MariaDB** (Cloud-Tenants), **Redis** (optional), **Stripe** (Laravel Cashier).

Detaillierte Implementierungsschritte im Repo: [`plan/steps/00-overview.md`](../plan/steps/00-overview.md).

---

## Funktionen (MVP)

- Registrierung / Login / E-Mail-Verifizierung (Breeze)
- Workspace + Profil + **öffentliche Seite** unter `/p/{slug}`
- **Links** mit Tracking-Redirect `/go/{link}`
- **Profil-Vorlagen** (Farb-Themes) + Profilbild (öffentlicher `storage`-Disk)
- **Analytics** (Klicks pro Tag, Top-Links)
- **Pläne & Limits** (Free: max. 10 Links, Plattform-Branding)
- **Stripe** Checkout & Kundenportal (Price-IDs in `.env` oder Admin **`/admin/stripe-settings`**)
- **Admin** `/admin` (nur `users.is_admin = true`) — Sprachen **EN / DE / FR / IT** im Benutzermenü; unter **System** **E-Mail/SMTP** (`/admin/mail-settings`) und **Stripe & Abo-Preise** (`/admin/stripe-settings`) — DB-Overrides, `.env` als Fallback
- Marketing: `/`, `/pricing`, `/faq`, Legal-Seiten, Cookie-Banner (Stub)

---

## Lokale Entwicklung

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # oder PostgreSQL konfigurieren
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

**Demo-Logins** (nach `migrate --seed`):

- Admin: `admin@example.com` / `password` → Filament `/admin`
- Creator: `creator@example.com` / `password`

---

## Cloud-Multi-Tenant (App-VPS)

Ein **App-Host** bedient viele Installationen unter `/var/www/clh-tenants/{slug}`. Ausführliche Anleitungen:

- [Cloud-Hosting — Installation & Betrieb](cloud-hosting-installation/README.md)
- [Server-Update nach GitHub](cloud-hosting-installation/server-update-nach-github.md)
- [VPS-Komponenten](vps-components.md)

Installer (interaktiv, vom GitHub-`main`):

```bash
sudo apt-get update && sudo apt-get install -y curl ca-certificates
sudo curl -fsSL -o /tmp/install-cloud-host-interactive.sh \
  https://raw.githubusercontent.com/Sebastian-1892/creator-link-hub/main/scripts/install-cloud-host-interactive.sh
sudo bash /tmp/install-cloud-host-interactive.sh
```

---

## Tests & Qualität

```bash
php artisan test
./vendor/bin/pint
./vendor/bin/phpstan analyse
```

---

## Tenant-Updates

**Aus dem Cloud-Host** (alle Tenants in einem Lauf): `sudo /usr/local/bin/clh-rollout-all-tenants.sh` — siehe [Server-Update nach GitHub](cloud-hosting-installation/server-update-nach-github.md).

**Pro Tenant** (Filament-Admin → „Anwendungs-Update“ oder per SSH):

```bash
cd /var/www/clh-tenants/<slug>
sudo -u www-data bash scripts/update-application.sh
```

---

## Umgebungsvariablen

Siehe [`.env.example`](../.env.example) — u. a. `STRIPE_*`, Price-IDs, Webhook `STRIPE_WEBHOOK_SECRET`.

---

## CI

GitHub Actions: [`.github/workflows/ci.yml`](../.github/workflows/ci.yml).

---

## Lizenz

Proprietär / nach Bedarf — Projekt-MVP.

---

## Externe Repos (optional)

Marketing-Site / Provisioner-Doku kann in einem separaten Repository liegen (z. B. **creatorlinkhub.eu**) — dort eigene `deployment/cloud-host/`-Doku parallel zu diesem Repo pflegen.
