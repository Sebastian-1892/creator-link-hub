# Deployment (Kurz-Runbook)

## Zwei Betriebsmodelle

| Modell | Einstieg | Doku |
|--------|----------|------|
| **Ein Mandant (Self-Host)** | ZIP + [`scripts/install-server.sh`](../scripts/install-server.sh) | [`docs/self-host-installation/README.md`](self-host-installation/README.md) |
| **Multi-Tenant Cloud-VPS** | [`scripts/bootstrap-cloud-host.sh`](../scripts/bootstrap-cloud-host.sh) | [`docs/cloud-hosting-installation/README.md`](cloud-hosting-installation/README.md) und [VPS-Komponenten](vps-components.md) (Komponentenübersicht) |

Die folgenden Absätze gelten primär für die **klassische Einzelinstallation** (eine App, eine DB).

---

**Interaktive Debian-/Ubuntu-Installation (Skript, fragt alle wichtigen Einstellungen ab):** [`../scripts/install-server.sh`](../scripts/install-server.sh)  
Ausführung auf dem Server als root: `sudo bash scripts/install-server.sh` im **entpackten** Projektordner (Release-ZIP oder Kunden-`install.sh`). Es gibt **keinen Git-Clone** mehr. PHP kommt **nur aus den offiziellen Paketquellen** der Distribution (kein PPA). Das Skript legt nach den Migrationen optional einen **Filament-Administrator** an (E-Mail, Anzeigename, Passwort) und lädt zuvor die **Themes** per `ThemeSeeder`.

## Server (Self-Host / klassisch)

- PHP **8.2–8.4** (Projekt-Lock richtet sich nach Plattform **8.3** in `composer.json`; 8.4 auf dem Server ist unkritisch) + Extensions: `pdo_pgsql` bzw. `mysql`, `mbstring`, `openssl`, `curl`, `redis`, `intl`, `bcmath`
- PostgreSQL 16, Redis 7
- Nginx → `public/index.php`
- Supervisor: `php artisan queue:work`
- Cron: `* * * * * php /path/artisan schedule:run`

**Cloud-App-Host (viele Tenants):** typisch **MariaDB**, Tenant-Datenbanken und Nginx-Sites werden von den `clh-*.sh`-Skripten angelegt — siehe [`docs/cloud-hosting-installation/README.md`](cloud-hosting-installation/README.md).

## Updates (bestehende Installation)

Skript: [`scripts/update-application.sh`](../scripts/update-application.sh) — führt `composer install`, `npm ci`/`npm run build`, `php artisan migrate --force`, Caches und optional Supervisor-Neustart aus (ohne Git). Zuvor die App-Dateien aus dem **neuen Release-ZIP** ersetzen. **`.env` und Datenbankinhalte** werden nicht angepasst; nur ausstehende **Migrationen** werden angewendet.

```bash
cd /pfad/zu/creator-link-hub
bash scripts/update-application.sh
```

Für Entwicklungs-Dependencies: `bash scripts/update-application.sh --dev`.

**Nach GitHub-Push — welche Konsole-Befehle?**

- **Cloud-Host (Multi-Tenant):** Kopierreferenz in [`docs/cloud-hosting-installation/server-update-nach-github.md`](cloud-hosting-installation/server-update-nach-github.md#konsole-vps-nach-github-aktualisieren) (`clh-cloud-host-update.sh`, optional `clh-rollout-all-tenants.sh`).
- **Self-Host mit Git-Klon:** `git pull` im Projektroot, danach wie oben `bash scripts/update-application.sh` (Details: [`docs/self-host-installation/README.md`](self-host-installation/README.md#updates-mit-git-auf-dem-server)).

## Nach dem Deploy

```bash
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

## Stripe

- Live-Keys in `.env`
- Webhook-Endpoint: `{APP_URL}/stripe/webhook` (Cashier-Standardpfad)
- `STRIPE_WEBHOOK_SECRET` setzen

## Backups

- Täglich PostgreSQL Dump auf Object Storage / Storage Box

## Monitoring

- Sentry DSN optional in `.env` (`SENTRY_LARAVEL_DSN`)
