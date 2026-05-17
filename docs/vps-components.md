# VPS-Software — Creator Link Hub (Cloud-/Multi-Tenant-Host)

Übersicht der **Komponenten und Pfade** auf dem **dedizierten App-VPS** (*nicht* der Marketing-Server). Schritt-für-Schritt-Einrichtung: [Cloud-Hosting-Installation](cloud-hosting-installation/README.md).

---

## Rolle dieses Servers

| Aufgabe | Kurzbeschreibung |
|--------|-------------------|
| **Kundeninstanzen** | Je Subdomain eine Laravel-Installation unter einem gemeinsamen Wurzelverzeichnis (`tenants_root`). |
| **Provisioner-API** | Signierter HTTP-POST löst ohne Web-Login `sudo`-Skripte aus (Tenant anlegen / löschen / sperren). |
| **Datenbank** | Typisch MariaDB/MySQL auf dem gleichen VPS; **eine Datenbank pro Tenant** (`clh_<slug_mit_unterstrich>`). |

Der **Marketing-Host** triggert das Provisioning per Cron und HTTPS; er hostet keine Tenant-Webroots.

---

## Installierte Basis-Software (typisch nach Bootstrap)

Nach `sudo bash scripts/bootstrap-cloud-host.sh` sind u. a. vorhanden:

| Paket / Dienst | Funktion auf dem VPS |
|----------------|---------------------|
| **UFW** (Firewall) | Von außen typisch **SSH**, **80/tcp**, **443/tcp**. **9100** nur localhost (Provisioner). |
| **Nginx** | TLS-Termination, `proxy_pass` zum Provisioner (`127.0.0.1:9100`), je Tenant eigene Site. |
| **MariaDB oder MySQL** | Tenant-Datenbanken. |
| **PHP CLI / PHP-FPM** | Composer, Artisan, Auslieferung der Tenant-Apps. |
| **Composer** | Beim Provisioning typisch `composer install --no-dev`. |
| **Postfix** (`sendmail`) | `/usr/sbin/sendmail` für Tenant-Laravel (`MAIL_MAILER=sendmail`). |

Konkrete PHP-Minor-Versionen richten sich nach Bootstrap und Distribution (z. B. PHP 8.3).

---

## Provisioner-Dienst

| Komponente | Pfad / Adresse |
|------------|----------------|
| systemd-Unit | `clh-provisioner.service` |
| Laufsystem-Prozess | `php -S 127.0.0.1:9100 …/router.php` (Built-in Server, nicht direkt öffentlich). |
| Einstieg (Repo) | [`deployment/cloud-host/router.php`](../deployment/cloud-host/router.php) → [`provisioner.php`](../deployment/cloud-host/provisioner.php) nach `/opt/clh-provisioner/` |
| HMAC-Secret | `/etc/clh-provisioner/secret` — muss zum Marketing **`provisioner.hmac_secret`** passieren (**Rohstring**). |
| Git-Klon + Branch | `/etc/clh-provisioner/install-paths.env` — `CLH_REPO_ROOT`, `CLH_GIT_REF`; gelesen von [`scripts/clh-cloud-host-update.sh`](../scripts/clh-cloud-host-update.sh). |
| Konfiguration (JSON) | `/etc/clh-provisioner/config.json` — u. a. `release_zip`, `tenants_root`, `db_driver`. |
| Nonce-/Replay-Cache | `/var/lib/clh-provisioner/nonces/` |

**Warum zwei PHP-Dateien?** Der Router puffert **`php://input`** einmal; sonst kann der Signatur-Hash vom Body abweichen (**`401 invalid signature`**).

---

## Tenant-Laufzeitpfade

| Was | Typischer Pfad |
|-----|----------------|
| Alle Tenant-Verzeichnisse | `/var/www/clh-tenants/<slug>/` (`tenants_root` in `config.json`). |
| Release-Artefakt für neue Instanzen | `/opt/clh-releases/current.zip` |
| Nginx Tenant-Sites | `/etc/nginx/sites-available/clh-<slug>.conf` |
| Maintenance-Seite (Suspend) | `/var/www/clh-suspended/index.html` |
| Nginx Suspend-vhost | `/etc/nginx/sites-enabled/clh-<slug>-suspended.conf` (nur während Suspend) |

---

## Skripte auf dem VPS

| Repo-Datei | Ziel auf dem VPS | Zweck |
|------------|------------------|-------|
| [`scripts/clh-provision-tenant.sh`](../scripts/clh-provision-tenant.sh) | `/usr/local/bin/clh-provision-tenant.sh` | Neuer Tenant aus ZIP, DB, Nginx, TLS. |
| [`scripts/clh-cloud-host-update.sh`](../scripts/clh-cloud-host-update.sh) | `/usr/local/bin/clh-cloud-host-update.sh` | `git pull`, Provisioner + Skripte, optional `--with-zip`. |
| [`scripts/clh-rollout-all-tenants.sh`](../scripts/clh-rollout-all-tenants.sh) | `/usr/local/bin/clh-rollout-all-tenants.sh` | Host-Update + alle Tenants ([How-to](cloud-hosting-installation/server-update-nach-github.md)). |
| `scripts/clh-delete-tenant.sh` | `/usr/local/bin/clh-delete-tenant.sh` | Tenant entfernen. |
| `scripts/clh-suspend-tenant.sh` / `clh-resume-tenant.sh` | `/usr/local/bin/` | Suspend: Maintenance-vhost + HTTPS; Resume: Original-Symlink wieder aktivieren. |
| `distribution/clh-suspended/index.html` | `/var/www/clh-suspended/index.html` | Statische „Account deaktiviert“-Seite (via bootstrap/update). |

**Release-ZIP bauen:** [`scripts/build-cloud-release-zip.sh`](../scripts/build-cloud-release-zip.sh).

---

## Kommunikation Marketing ↔ VPS

- **Transport:** HTTPS POST, JSON-Body, Header **`X-CLH-Signature`** = `hash_hmac('sha256', <roher Body>, <Secret>)`.
- Parallele Doku oft im Marketing-Repo unter `deployment/cloud-host/` — **`provisioner.php`** / **`router.php`** sollten inhaltlich synchron bleiben.

---

## Schnelltests auf dem VPS

```bash
sudo systemctl status clh-provisioner --no-pager
curl -fsS http://127.0.0.1:9100/health
sudo tail -n 100 /var/log/clh-provisioner.log
```

