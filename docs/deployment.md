# Deployment (Kurz-Runbook)

**Interaktive Debian-/Ubuntu-Installation (Skript, fragt alle wichtigen Einstellungen ab):** [`../scripts/install-server.sh`](../scripts/install-server.sh) — Repository: [https://github.com/Sebastian-1892/creator-link-hub.git](https://github.com/Sebastian-1892/creator-link-hub.git)  
Ausführung auf dem Server als root: `sudo bash scripts/install-server.sh` (im geklonten Projektordner). PHP kommt **nur aus den offiziellen Paketquellen** der Distribution (kein PPA). Das Skript legt nach den Migrationen optional einen **Filament-Administrator** an (E-Mail, Anzeigename, Passwort) und lädt zuvor die **Themes** per `ThemeSeeder`.

## Server

- PHP **8.2–8.4** (Projekt-Lock richtet sich nach Plattform **8.3** in `composer.json`; 8.4 auf dem Server ist unkritisch) + Extensions: `pdo_pgsql` bzw. `mysql`, `mbstring`, `openssl`, `curl`, `redis`, `intl`, `bcmath`
- PostgreSQL 16, Redis 7
- Nginx → `public/index.php`
- Supervisor: `php artisan queue:work`
- Cron: `* * * * * php /path/artisan schedule:run`

## Updates (bestehende Installation)

Skript im Repo: [`scripts/update-from-git.sh`](../scripts/update-from-git.sh) — führt `git pull --ff-only`, `composer install`, `npm ci`/`npm run build`, `php artisan migrate --force`, Caches und optional Supervisor-Neustart aus. **`.env` und Datenbankinhalte** werden nicht angepasst; nur ausstehende **Migrationen** werden angewendet.

```bash
cd /pfad/zu/creator-link-hub
bash scripts/update-from-git.sh
```

Bei lokalen, uncommitteten Änderungen bricht das Skript ab, außer mit `--yes`. Für Entwicklungs-Dependencies: `bash scripts/update-from-git.sh --dev`.

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
