# VPS-Software — Creator Link Hub (Cloud-/Multi-Tenant-Host)

Übersicht der **Komponenten und Pfade**, die auf dem **dedizierten App-VPS** laufen (*nicht* der Marketing-Server). Die **Einrichtung Schritt für Schritt** steht unter [`docs/cloud-hosting-installation/README.md`](../docs/cloud-hosting-installation/README.md).

---

## Rolle dieses Servers

| Aufgabe | Kurzbeschreibung |
|--------|-------------------|
| **Kundeninstanzen** | Je Subdomain eine Laravel-Installation unter einem gemeinsamen Wurzelverzeichnis (`tenants_root`). |
| **Provisioner-API** | Signierter HTTP-POST löst ohne Web-Login `sudo`-Skripte aus (Tenant anlegen / löschen / sperren). |
| **Datenbank** | Typisch MariaDB/MySQL auf dem gleichen VPS; **eine Datenbank pro Tenant** (`clh_<slug_mit_unterstrich>`). |

Der **Marketing-Host** (z. B. Repo `creatorlinkhub.eu`) triggert das Provisioning per Cron und HTTPS; er hostet keine Tenant-Webroots.

---

## Installierte Basis-Software (typisch nach Bootstrap)

Nach `sudo bash scripts/bootstrap-cloud-host.sh` sind u. a. vorhanden:

| Paket / Dienst | Funktion auf dem VPS |
|----------------|---------------------|
| **UFW** (Firewall) | Nach Bootstrap aktiv: von außen typisch nur **SSH** (Profil „OpenSSH“ / **limit** möglich), **80/tcp**, **443/tcp**. **9100** bleibt **nicht** freigegeben (nur localhost). Eigenen SSH-Port per `ufw allow …` nachrüsten. |
| **Nginx** | TLS-Termination, `proxy_pass` zum Provisioner (`127.0.0.1:9100`), je Tenant eigene Site unter `sites-available`. |
| **MariaDB oder MySQL** | Tenant-Datenbanken; Root-Zugriff fürs Provisioning oft per **`mysql -u root`** (Socket/Policies je nach Bootstrap). |
| **PHP CLI** | `composer`, `php artisan`, Built-in Server des Provisioners. |
| **PHP-FPM** | Auslieferung der Tenant-Laravel-Apps über Nginx ↔ `unix:/run/php/php*-fpm.sock`. |
| **Composer** | Wird beim Provisioning eines neuen Tenants für `composer install --no-dev` verwendet (meist Root/Sudo durch Skriptfluss — siehe Hinweise in den Skripten). |

Konkrete PHP-Minor-Versionen richten sich nach dem Bootstrap und den Ubuntu-/Debian-Paketquellen (z. B. PHP 8.3).

---

## Provisioner-Dienst

| Komponente | Pfad / Adresse |
|------------|----------------|
| systemd-Unit | `clh-provisioner.service` |
| Laufsystem-Prozess | `php -S 127.0.0.1:9100 …/router.php` (Built-in Server, **nicht** öffentlich ohne Nginx binden). |
| Einstieg | [`deployment/cloud-host/router.php`](../deployment/cloud-host/router.php) → [`deployment/cloud-host/provisioner.php`](../deployment/cloud-host/provisioner.php) nach `/opt/clh-provisioner/` |
| HMAC-Secret | `/etc/clh-provisioner/secret` — muss zum Marketing **`provisioner.hmac_secret`** passen (**Rohstring**, kein `hex2bin` auf dem Schlüssel). |
| Git-Klon + Branch (Updates) | `/etc/clh-provisioner/install-paths.env` — `CLH_REPO_ROOT`, `CLH_GIT_REF`; wird beim Bootstrap geschrieben, von [`scripts/clh-cloud-host-update.sh`](../scripts/clh-cloud-host-update.sh) gelesen. |
| Konfiguration (JSON) | `/etc/clh-provisioner/config.json` — u. a. `release_zip`, `tenants_root`, `db_driver`. |
| Nonce-/Replay-Cache | `/var/lib/clh-provisioner/nonces/` |
| systemd-Unit (auf dem VPS) | `/etc/systemd/system/clh-provisioner.service` — wird von [`scripts/bootstrap-cloud-host.sh`](../scripts/bootstrap-cloud-host.sh) geschrieben (keine Kopie im `deployment/cloud-host/`-Ordner nötig) |

**Warum zwei PHP-Dateien?** Der Router puffert **`php://input`** einmal; sonst kann der Signature-Hash vom gelesenen Body abweichen und der Provisioner meldet **`401 invalid signature`**, obwohl Secret und Marketing-Code gleich sind.

---

## Tenant-Laufzeitpfade (nach Bootstrap / Skriptkonvention)

| Was | Typischer Pfad |
|-----|----------------|
| Alle Tenant-Verzeichnisse | `/var/www/clh-tenants/<slug>/` (Anpassbar über `config.json` → `tenants_root`). |
| Release-Artefakt für neue Instanzen | `/opt/clh-releases/current.zip` (`release_zip` in `config.json`). |
| Nginx Tenant-Sites | Von `clh-provision-tenant.sh` erzeugt: `/etc/nginx/sites-available/clh-<slug>.conf`, Symlink unter `sites-enabled/`. |

---

## Skripte auf dem VPS (per `sudo` nur für Nutzer `clh-provisioner`)

| Repo-Datei | Ziel auf dem VPS | Zweck |
|------------|------------------|-------|
| [`scripts/clh-provision-tenant.sh`](../scripts/clh-provision-tenant.sh) | `/usr/local/bin/clh-provision-tenant.sh` | Neuer Tenant aus ZIP (Composer, Migrate, Seeds, Admin), **`chmod 755`** Tenantroot/`public`, **Let’s Encrypt** per **`certbot certonly --webroot`**, Nginx‑Endconfig HTTP/HTTPS, optional **`--no-tls`**. |
| [`scripts/clh-cloud-host-update.sh`](../scripts/clh-cloud-host-update.sh) | `/usr/local/bin/clh-cloud-host-update.sh` | Nach Push ins Repo: `git pull`, Provisioner-PHP + Tenant-Skripte ausrollen, optional `--with-zip`, Dienst + Nginx reload. |
| `scripts/clh-delete-tenant.sh` | `/usr/local/bin/clh-delete-tenant.sh` | Tenant entfernen (Dateien, DB, Sites). |
| `scripts/clh-suspend-tenant.sh` | `/usr/local/bin/clh-suspend-tenant.sh` | Tenant-Site aus `sites-enabled` nehmen. |
| [`scripts/clh-resume-tenant.sh`](../scripts/clh-resume-tenant.sh) | `/usr/local/bin/clh-resume-tenant.sh` | Tenant-Site wieder aktivieren (`sites-available`-Config muss existieren). |

Technische Kurzinfos stehen im **Kommentarkopf** der jeweiligen Shell-Datei im Repo.

**Release-ZIP bauen (lokal, nicht auf dem VPS entwickeln):** [`scripts/build-cloud-release-zip.sh`](../scripts/build-cloud-release-zip.sh) — Ergebnis nach `/opt/clh-releases/current.zip` kopieren. Details in der ausführlichen Cloud-Doku.

---

## Kommunikation Marketing ↔ VPS

- **Transport:** HTTPS POST, JSON-Body, Header **`X-CLH-Signature`** = **`hash_hmac('sha256', <roher Body>, <Secret aus Datei als String>`)** — siehe Implementierung **`Clh\CloudProvisioner`** im Marketing-Repo.
- Öffentliche **Provisioner-URL** (Beispiel): `https://provision.app.<deine-zone>/`.

Parallele Dokumentation (Marketing-Perspektive) liegt im anderen Repo unter `deployment/cloud-host/README.md`; die Dateien **`provisioner.php`** / **`router.php`** in **`deployment/cloud-host/`** sollten dort und hier **inhaltlich synchron** gehalten werden.

---

## Aktualisieren von Provisioner/Skript-Komponenten (vom Entwickler-PC)

**Wichtig:** Befehle wie `scp lokaler_pfad "${VPS}:/tmp/"` immer auf dem Rechner ausführen, **auf dem die Quelldateien liegen** (nicht eingeloggt auf dem VPS mit einem nicht existierenden lokalen Pfad).

Übliche Reihenfolge:

1. Vom PC: **`clh-provision-tenant.sh`**, **`provisioner.php`**, **`router.php`** nach `/tmp/` auf den VPS kopieren.
2. Auf dem VPS: mit **`sudo install …`** nach `/usr/local/bin/` bzw. `/opt/clh-provisioner/` setzen und **`sudo systemctl restart clh-provisioner`** ausführen.
3. **Neuen Tenant** testen (frischer `--slug`), oder bestehendes Verzeichnis manuell entfernen, bevor dasselbe Subdomain-Provisioning wiederholt wird.

---

## Schnelltests auf dem VPS

```bash
# Provisioner-Prozess
sudo systemctl status clh-provisioner --no-pager

# Lokal direkt gegen Built-in Server
curl -fsS http://127.0.0.1:9100/health

# Ursachen bei Provisionier-Fehlern
sudo tail -n 100 /var/log/clh-provisioner.log
sudo journalctl -u clh-provisioner -n 80 --no-pager
```

**Manuelle End-to-End-Prüfung ohne Marketing:** `./scripts/clh-provision-tenant.sh` mit eigener gültiger `--admin-email` und neuem `--slug` (siehe Cloud-Hosting-Anleitung, Abschnitt zu Tests).

---

## Weiterführende Dokumente

| Dokument | Inhalt |
|----------|--------|
| [`docs/cloud-hosting-installation/README.md`](../docs/cloud-hosting-installation/README.md) | Bootstrap, DNS, TLS, erste Tenants |
| [`docs/deployment.md`](../deployment.md) | Allgemeiner Laravel-Betrieb |
| Marketing-Repo `deployment/cloud-host/README.md` | Ablauf Provision-Jobs, `hmac_secret`, Cron |

---

## Self-Host vs. Cloud-VPS

| | Self-Host (ein Mandant) | Cloud-VPS (Multi-Tenant) |
|---|-------------------------|---------------------------|
| Einstieg | `scripts/install-server.sh` | `scripts/bootstrap-cloud-host.sh` |
| Instanzanzahl | eine | viele unter `tenants_root` |

Diese Datei dokumentiert nur den **Cloud-VPS** (Multi-Tenant).
