# Deployment (Kurz-Runbook, Cloud-Multi-Tenant)

Creator Link Hub wird als **Cloud-Multi-Tenant**-App betrieben: ein App-VPS, viele Tenant-Installationen unter `/var/www/clh-tenants/<slug>/`.

| Bereich | Einstieg |
|---------|----------|
| App-VPS einrichten | [`scripts/bootstrap-cloud-host.sh`](../scripts/bootstrap-cloud-host.sh) bzw. [`scripts/install-cloud-host-interactive.sh`](../scripts/install-cloud-host-interactive.sh) |
| Vollständige Anleitung | [`docs/cloud-hosting-installation/README.md`](cloud-hosting-installation/README.md) |
| Komponenten- & Pfadübersicht | [`docs/vps-components.md`](vps-components.md) |

---

## App-VPS — Basisstack

- PHP **8.2–8.4** + Extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `redis`, `intl`, `bcmath`
- **MariaDB** (eine Datenbank pro Tenant: `clh_<slug>`), Redis 7 optional
- Nginx → je Tenant eigene Site auf `…/public/index.php`
- **Postfix** (`/usr/sbin/sendmail`) für Tenant-Mail (`MAIL_MAILER=sendmail`), optional SMTP-Relay
- Provisioner (PHP Built-in Server) auf `127.0.0.1:9100`, signierte HTTP-API für Marketing → Tenant-Anlage/Löschung

Tenant-Installationen werden vom Provisioner aus einem Release-ZIP unter `/opt/clh-releases/current.zip` ausgerollt (siehe [`scripts/build-cloud-release-zip.sh`](../scripts/build-cloud-release-zip.sh)).

---

## Tenant-Update (bestehende Installation)

Skript: [`scripts/update-application.sh`](../scripts/update-application.sh) — führt `composer install`, `npm ci`/`npm run build`, `php artisan migrate --force`, Caches und optional Supervisor-Neustart aus (ohne Git). Wird vom **Filament-Admin** („Anwendungs-Update“) und vom **Tenant-Rollout** ausgeführt; **`.env` und Datenbankinhalte** werden nicht angepasst, nur ausstehende **Migrationen** angewendet.

```bash
cd /var/www/clh-tenants/<slug>
sudo -u www-data bash scripts/update-application.sh
```

Für Entwicklungs-Dependencies: `bash scripts/update-application.sh --dev`.

**Nach GitHub-Push — welche Konsole-Befehle?** Kopierreferenz in [`docs/cloud-hosting-installation/server-update-nach-github.md`](cloud-hosting-installation/server-update-nach-github.md#konsole-vps-nach-github-aktualisieren) (`clh-cloud-host-update.sh`, optional `clh-rollout-all-tenants.sh`).

---

## Nach dem Deploy (pro Tenant)

```bash
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

`scripts/ensure-laravel-storage.sh` wird automatisch von `update-application.sh` und `clh-provision-tenant.sh` aufgerufen.

---

## Stripe

- Live-Keys in `.env` oder im Filament-Admin unter `/admin/stripe-settings`
- Webhook-Endpoint: `{APP_URL}/stripe/webhook` (Cashier-Standardpfad)
- `STRIPE_WEBHOOK_SECRET` setzen

---

## Backups

- Täglich MariaDB-Dump pro Tenant-DB auf Object Storage / Storage Box

## Monitoring

- Sentry DSN optional in `.env` (`SENTRY_LARAVEL_DSN`)
