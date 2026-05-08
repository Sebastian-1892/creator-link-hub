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
- **Admin** `/admin` (nur `users.is_admin = true`) — Sprachwahl **EN / DE / FR / IT** im **Benutzermenü** oben rechts (Flaggen + Name, Filament-Oberfläche)
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

## Cloud-Multi-Tenant (Marketing-Server + App-VPS)

Ein **App-Host** (ein VPS) bedient viele Kunden-Installationen unter `/var/www/clh-tenants/{slug}` mit **HTTP-Provisioner** + **MariaDB**. Marketing (z. B. creatorlinkhub.eu) ruft den Provisioner per HMAC auf.

### Direkt vom GitHub installieren (Ubuntu / Debian VPS)

Auf einem frischen Server (als **root** oder mit **sudo**):

1. **DNS:** A-Record für deinen späteren **Provisioner-Hostnamen** (z. B. `provision.app.deinedomain.de`) auf die Server-IP legen — vor **Let’s Encrypt** im Wizard nötig.
2. **Installer starten** — lädt das Skript von `main` und führt den **interaktiven Wizard** aus (Git-Clone oder Update, Bootstrap, optional Release-ZIP mit Node, Nginx, optional Certbot):

```bash
sudo apt-get update && sudo apt-get install -y curl ca-certificates
sudo curl -fsSL -o /tmp/install-cloud-host-interactive.sh \
  https://raw.githubusercontent.com/Sebastian-1892/creator-link-hub/main/scripts/install-cloud-host-interactive.sh
sudo bash /tmp/install-cloud-host-interactive.sh
```

**Alternativ** (Repo z. B. schon geklont oder private Git-URL):

```bash
git clone https://github.com/Sebastian-1892/creator-link-hub.git
cd creator-link-hub
sudo bash scripts/install-cloud-host-interactive.sh
```

Nach dem Wizard: **Wildcard-DNS** für Kunden-Subdomains, **`/etc/clh-provisioner/secret`** im Marketing als `provisioner.hmac_secret` eintragen und die **öffentliche Provisioner-URL** setzen — Details in der Anleitung unten.

---

**Schritt-für-Schritt (manuelle Einzelbefehle, Feinschliff):** [`docs/cloud-hosting-installation/README.md`](docs/cloud-hosting-installation/README.md)  
**Komponenten/Pfade auf dem VPS:** [`vps/README.md`](vps/README.md)

---

Skripte zum Aufsetzen des **App-Hosts** und zum Anlegen/Löschen einzelner **Tenants** liegen unter **`scripts/`** und **`deployment/cloud-host/`**:

| Komponente | Zweck |
|------------|--------|
| [`scripts/bootstrap-cloud-host.sh`](scripts/bootstrap-cloud-host.sh) | Einmalig: **UFW** (SSH, 80, 443), Nginx, MariaDB, PHP-FPM, User `clh-provisioner`, systemd, sudoers, Provisioner |
| [`scripts/install-cloud-host-interactive.sh`](scripts/install-cloud-host-interactive.sh) | Interaktiver Wizard auf dem VPS: Git, Bootstrap, ZIP, Nginx, optional Certbot (siehe Cloud-Doku) |
| [`scripts/clh-cloud-host-update.sh`](scripts/clh-cloud-host-update.sh) | Auf dem VPS: Repo pullen, Provisioner/Skripte aktualisieren, optional Release-ZIP bauen (`sudo /usr/local/bin/clh-cloud-host-update.sh`) |
| [`deployment/cloud-host/router.php`](deployment/cloud-host/router.php) + [`provisioner.php`](deployment/cloud-host/provisioner.php) | HTTP-Provisioner (HMAC, Nonce, `sudo` → Tenant-Skripte); mit Marketing-Repo **creatorlinkhub.eu** unter `deployment/cloud-host/` bei Bedarf **inhaltlich synchron** halten |
| [`scripts/build-cloud-release-zip.sh`](scripts/build-cloud-release-zip.sh) | Release-ZIP mit `npm run build` für `/opt/clh-releases/current.zip` auf dem VPS |
| `clh-provision-tenant.sh`, `clh-delete-tenant.sh`, `clh-suspend-tenant.sh`, `clh-resume-tenant.sh` | Auf dem VPS unter `/usr/local/bin/`, Aufruf durch Provisioner |

Marketing (falls vorhanden): [`../creatorlinkhub.eu/deployment/cloud-host/README.md`](../creatorlinkhub.eu/deployment/cloud-host/README.md)

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

**Kunden:** Release-ZIP einspielen (z. B. über `distribution/install.sh` mit Lizenz) — die Lizenz wird gegen **`https://creatorlinkhub.eu/license/check`** geprüft (`CLH_LICENSE_CHECK_URL` überschreibbar). Danach läuft `install-server.sh` automatisch im entpackten Verzeichnis.

**Manuell:** Anwendung als ZIP entpacken (z. B. nach `/var/www/creator-link-hub`), in diesen Ordner wechseln, dann:

```bash
sudo bash scripts/install-server.sh
```

Das Skript ist **interaktiv** (deutsch) und fragt u. a. ab:

- System-Update (`apt upgrade`)
- Installationsverzeichnis (muss bereits die entpackte Laravel-App enthalten)
- Domain / `APP_URL` / `APP_NAME`
- Datenbank (PostgreSQL oder MariaDB), Benutzer, Passwort, optional Löschen gleichnamiger Test-DB
- Redis, NodeSource (Node 20) für den Frontend-Build
- Stripe- und SMTP-Werte (optional, leer lassen möglich)
- **Administrator** für Filament (`/admin`): E-Mail, Anzeigename, Passwort (min. 8 Zeichen)
- optional Demo-Nutzer `creator@example.com` (nur für Tests)
- Nginx-Site, Supervisor (Queue), Cron (Scheduler), Certbot

**Wichtig:** Datenbank-Passwort ohne einfaches `'` und ohne `"`. Nach dem Setup: Kurzüberblick in [`docs/deployment.md`](docs/deployment.md), Go-Live in [`docs/launch-runbook.md`](docs/launch-runbook.md).

### Updates (bestehende Installation, ohne Git)

Neue Version: **ZIP** einspielen (Dateien ersetzen), dann im Projektroot **Abhängigkeiten, Build und Migrationen**:

```bash
cd /pfad/zu/creator-link-hub   # dein Installationspfad
bash scripts/update-application.sh
```

Optionen: `bash scripts/update-application.sh --help` (u. a. `--dev` für Composer mit Dev-Paketen).

## Umgebungsvariablen

Siehe [`.env.example`](.env.example) — insbesondere `STRIPE_*`, `STRIPE_PRICE_STARTER`, `STRIPE_PRICE_PRO`, Webhook `STRIPE_WEBHOOK_SECRET`.

## CI

GitHub Actions: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

## Lizenz

Proprietär / nach Bedarf — Projekt-MVP.
