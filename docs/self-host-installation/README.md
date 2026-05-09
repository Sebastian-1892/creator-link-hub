# Creator Link Hub — Installation für Self-Host-Kunden

Schritt-für-Schritt-Anleitung, wenn Sie die Software **auf Ihrem eigenen Server** (VPS oder Dedicated) betreiben. Zielsystem: **Debian 12+** oder **Ubuntu 22.04 / 24.04 LTS** mit **root**- oder **sudo**-Zugriff.

---

## 1. Voraussetzungen prüfen

- **Server** mit öffentlicher IP (oder intern, falls nur für Ihr Netz)
- **Domain** (empfohlen), DNS A/AAAA auf die Server-IP zeigen lassen
- **Lizenz** von [creatorlinkhub.eu](https://creatorlinkhub.eu): Sie erhalten einen **64-stelligen Hex-Lizenzkey** (nach Kauf bzw. Trial)
- **SSH-Zugang** als Benutzer mit `sudo`-Rechten; die Installation läuft abschließend als **root**

Technische Mindestanforderungen orientieren sich am Install-Skript: **PHP 8.2–8.4**, Webserver **Nginx**, Datenbank **PostgreSQL oder MariaDB** (das Skript fragt ab), optional **Redis** und **Node** für den Frontend-Build.

---

## 2. Installationsweg wählen

### Weg A — Empfohlen: Installer mit Lizenzprüfung

So laden Sie das aktuelle Paket nach erfolgreicher Lizenzprüfung und starten die interaktive Server-Installation automatisch.

1. Per SSH auf den Server einloggen.
2. Als **root** ausführen (eine Zeile):

   ```bash
   curl -fsSL https://creatorlinkhub.eu/install.sh | sudo bash
   ```

   Alternativ: `install.sh` speichern und ausführen:

   ```bash
   sudo bash install.sh
   ```

3. **Lizenzkey** eingeben (64 Zeichen Hex, ohne Leerzeichen).
4. **Installationsverzeichnis** bestätigen oder ändern (Standard oft `/var/www/creator-link-hub`). Das Zielverzeichnis muss **leer** sein.
5. Das Skript lädt das ZIP, entpackt die Anwendung und startet **`scripts/install-server.sh`** automatisch (`CLH_INSTALL_TARGET` ist gesetzt).

### Weg B — Manuelles ZIP (ohne curl-Installer)

Wenn Sie das Release-**ZIP** bereits haben (z. B. vom Anbieter-Link):

1. ZIP nach `/var/www/` (oder einen anderen leeren Zielordner) hochladen und entpacken, sodass im Ordner u. a. **`composer.json`**, **`artisan`** und **`scripts/install-server.sh`** liegen.
2. In diesen Ordner wechseln und ausführen:

   ```bash
   cd /var/www/creator-link-hub
   sudo bash scripts/install-server.sh
   ```

Ohne vorkonfiguriertes Ziel fragt das Skript nach dem **Installationsverzeichnis** (Pfad zur entpackten App).

**Hinweis:** Ohne Lizenz-Installer ist ggf. keine automatische Paket-URL hinterlegt — dann liefert Ihnen der Anbieter das ZIP separat.

---

## 3. Interaktive Server-Installation (`install-server.sh`)

Das Skript ist **auf Deutsch** geführt und richtet u. a. ein:

- System-Update (`apt`) optional
- **PHP-FPM** (nur aus den **offiziellen** Paketquellen Ihrer Distribution, kein PPA), **Composer**
- **Nginx**, Site auf `public/index.php`
- **PostgreSQL oder MariaDB** (inkl. Datenbankbenutzer; Passwort **ohne** einfaches `'` und ohne `"`)
- optional **Redis**, **Node** (für `npm ci` / `npm run build`)
- optional **SSL** (z. B. Certbot)
- **Laravel**: `composer install`, `.env`, `php artisan key:generate`, Migrationen, **ThemeSeeder**
- **Filament-Administrator** für `/admin` (E-Mail, Anzeigename, Passwort, min. 8 Zeichen)
- optional Demo-Nutzer (nur für Tests)
- **Supervisor** (Queue), **Cron** (Scheduler)

Folgen Sie den Prompts bis zum Ende. Bei Fehlern stehen Meldungen im Terminal; häufig: falsche DB-Zugangsdaten, nicht-leeres Zielverzeichnis oder Sonderzeichen im DB-Passwort.

---

## 4. Nach der Installation

1. **Browser:** `https://Ihre-Domain/` (Marketing) bzw. App-Login wie in Ihrer Konfiguration.
2. **Admin:** Filament unter **`/admin`** mit dem im Setup angelegten Administrator.
3. Zusätzliche Checks (siehe auch [`docs/deployment.md`](../deployment.md) und [`docs/launch-runbook.md`](../launch-runbook.md)):
   - `APP_ENV=production`, `APP_DEBUG=false` für Live-Betrieb
   - **Stripe** Live-Keys, Webhook `https://Ihre-Domain/stripe/webhook`, `STRIPE_WEBHOOK_SECRET`
   - **E-Mail** (SMTP) für Verifizierung und Benachrichtigungen

Umgebungsvariablen: [`.env.example`](../../.env.example) im Projektroot.

---

## 5. Updates (ohne Git)

Wenn ein **neues Release-ZIP** vorliegt:

1. Dateien der Anwendung im Installationsordner durch die neue Version **ersetzen** (Backup von `.env` und ggf. `storage/` vorher anlegen).
2. Im Projektroot ausführen:

   ```bash
   cd /pfad/zu/creator-link-hub
   bash scripts/update-application.sh
   ```

   Optionen: `bash scripts/update-application.sh --help` (u. a. `--dev` für Composer-Dev-Pakete).

`.env` und Datenbankinhalte bleiben unverändert; es laufen u. a. Composer, Frontend-Build, Migrationen und Cache-Optimierung.

### Updates mit Git auf dem Server

Wenn du die App als **Git-Klon** (z. B. von GitHub) unter einem festen Pfad betreibst — **nicht** der ZIP-Installationsweg oben — dann nach jedem Push den Stand auf dem VPS holen und das Update-Skript ausführen. **`main`** durch euren Branch ersetzen:

```bash
cd /pfad/zu/creator-link-hub
sudo -u www-data git fetch origin
sudo -u www-data git pull --ff-only origin main
sudo -u www-data bash scripts/update-application.sh
```

*(Wenn PHP/Webserver nicht als `www-data` läuft, denselben Benutzer verwenden wie für die Anwendungsdateien.)*

**Multi-Tenant-Cloud-VPS** (Provisioner, viele Mandanten unter `/var/www/clh-tenants/`) ist **nicht** diese Route — dort siehe [`docs/cloud-hosting-installation/server-update-nach-github.md`](../cloud-hosting-installation/server-update-nach-github.md#konsole-vps-nach-github-aktualisieren).

---

## 6. Optional: Umgebungs-URLs überschreiben

| Variable | Bedeutung |
|----------|-----------|
| `CLH_LICENSE_CHECK_URL` | Lizenz-API, falls nicht der Standard-Server genutzt wird |
| `CLH_PACKAGE_URL` | Standard-Download-URL des ZIP (für Installer) |
| `CLH_APP_ROOT` | Absoluter Projektroot (u. a. für Update-Skripte) |
| `CLH_UPDATE_MANIFEST_URL` / `CLH_INSTALLED_VERSION` | Nur relevant, wenn Sie den **öffentlichen Update-Kanal** nutzen |

---

## 7. Hilfe und Dokumentation im Projekt

| Dokument | Inhalt |
|----------|--------|
| [`README.md`](../../README.md) (Projektroot) | MVP-Überblick, Demo-Logins nach Seed |
| [`docs/deployment.md`](../deployment.md) | Kurz-Runbook, Stripe, Backups; Verweis Self-Host vs. Cloud |
| [`docs/launch-runbook.md`](../launch-runbook.md) | Go-Live-Checkliste |
| [`docs/cloud-hosting-installation/README.md`](../cloud-hosting-installation/README.md) | Nur bei **Multi-Tenant-Cloud-VPS** (nicht diese Self-Host-Route) |
| [VPS-Komponenten](../vps-components.md) | Kurzübersicht Komponenten auf dem Cloud-App-Server |

Support-Anfragen richten Sie an den Kanal, den Sie beim Kauf von **creatorlinkhub.eu** erhalten haben.
