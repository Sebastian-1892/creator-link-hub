# Creator Link Hub — Cloud-Host einrichten (Schritt für Schritt)

Diese Anleitung ist für **dich als Betreiber** eines **Multi-Tenant-App-Hosts**: ein VPS mit MariaDB, Nginx, Tenant-Verzeichnissen und einem kleinen **HTTP-Provisioner** (signierte API → `sudo` → `clh-provision-tenant.sh`). Dein **Marketing-Backend** (z. B. **creatorlinkhub.eu**, separates Repo) löst später Bestellungen aus und ruft diesen Provisioner auf.

> **Nicht** dasselbe wie Self-Host (eine Installation mit `install-server.sh`). Hier entstehen **viele** Laravel-Instanzen unter `/var/www/clh-tenants/{slug}`.

---

## Begriffe und Pfade (merken)

| Was | Typischer Pfad / Port |
|-----|-------------------------|
| Tenant-Installationen | `/var/www/clh-tenants/<slug>/` |
| Release-ZIP für neue Tenants | `/opt/clh-releases/current.zip` |
| Provisioner (PHP builtin server) | `127.0.0.1:9100` (nur lokal) |
| Provisioner-Dateien | `/opt/clh-provisioner/` |
| Secret (HMAC) | `/etc/clh-provisioner/secret` |
| JSON-Konfig | `/etc/clh-provisioner/config.json` |
| Nginx-Beispiel (Provisioner vorn) | `/etc/nginx/sites-available/clh-provisioner.conf` |
| systemd-Dienst | `clh-provisioner.service` |

Ersetze in allen Beispielen **Platzhalter-Domains** durch deine echten Namen (`provision.deinedomain.de`, Zone für Kunden wie `*.app.deinedomain.de`).

---

## Vorarbeit: Checkliste

- [ ] **VPS** mit Debian/Ubuntu LTS, SSH, idealerweise nur du als Admin.
- [ ] Öffentliche **IPv4** (und ggf. IPv6); Firewall: **80** und **443** später von außen erreichbar (für TLS / HTTP-Challenge).
- [ ] **`creator-link-hub`** Repo auf dem Server (Git-Clone oder entpacktes Release mit `scripts/`).
- [ ] Eine fertige **Release-ZIP** der App (mit `composer.json` + `artisan` im Archiv; `vendor`/`node_modules` dürfen fehlen).
- [ ] Entscheidung: **Provisioner-Hostname** (z. B. `provision.app.deinedomain.de`) und **Kunden-Basis** (z. B. `*.app.deinedomain.de`).

---

## Schritt 1 — DNS vorbereiten

1. Lege einen **A-Record** für den **Provisioner-Host** auf die Server-IP (z. B. `provision.app` → `203.0.113.10`).
2. Lege einen **Wildcard** für die **Tenant-Subdomains** auf dieselbe IP (z. B. `*.app` in der Zone `deinedomain.de`, je nach DNS-Anbieter als `*.app` oder explizite Einträge).

> Ohne funktionierendes DNS zu diesem Server schlagen **TLS** und spätere **Kunden-URLs** fehl.

Warte, bis die Auflösung von deinem Rechner aus stimmt (`dig` / `nslookup`).

---

## Schritt 2 — Bootstrap ausführen (einmalig)

Auf dem VPS, im Verzeichnis des Repos:

```bash
cd /pfad/zu/creator-link-hub
sudo bash scripts/bootstrap-cloud-host.sh
```

**Erwartung:** Es erscheint eine nummerierte Schritt-Ausgabe `[bootstrap] Schritt x/11`. Der längste Block ist meist **Schritt 3** (`apt install`) — viele Zeilen von apt/dpkg sind normal.

Das Skript installiert u. a. **nginx**, **mariadb-server**, **PHP-FPM 8.2–8.4**, kopiert **`deployment/cloud-host/provisioner.php`** und **`deployment/cloud-host/router.php`** nach `/opt/clh-provisioner/`, legt User **`clh-provisioner`**, **sudoers**, **systemd** und eine **Nginx-Beispieldatei** an.

> **Hinweis:** `router.php` liest den HTTP-Body einmal aus `php://input` und reicht ihn an `provisioner.php` weiter. Ohne diese Datei kann der PHP Built-in Server beim Signatur‑Check **`401 invalid signature`** liefern, obwohl Secret und Marketing-Code stimmen.

**Ende:** Abschnitt „Bootstrap abgeschlossen. Nächste Schritte:“ — die folgenden Schritte hier in der Doku sind die **ausführliche** Version davon.

---

## Schritt 3 — Release-ZIP ablegen

**ZIP lokal bauen (empfohlen):** Im Repo `creator-link-hub` ausführen:

```bash
bash scripts/build-cloud-release-zip.sh
```

Ergebnis: `distribution/releases/creator-link-hub-cloud-<Zeitstempel>.zip` und Symlink `distribution/releases/current-cloud.zip`.

Der Provisioner liest den Pfad aus `/etc/clh-provisioner/config.json` (Standard: `release_zip` = `/opt/clh-releases/current.zip`).

```bash
sudo mkdir -p /opt/clh-releases
sudo cp /pfad/zu/dein-release.zip /opt/clh-releases/current.zip
sudo chmod 644 /opt/clh-releases/current.zip
```

Prüfen:

```bash
sudo -u clh-provisioner test -r /opt/clh-releases/current.zip && echo "lesbar für clh-provisioner OK"
```

> Ohne diese Datei meldet `clh-provision-tenant.sh` beim Anlegen eines Tenants einen Fehler („release zip not found“).

**Release-ZIP-Inhalt:** Das Archiv soll das **Laravel-Projektroot** enthalten (`composer.json`, `artisan`). Ideal liegt **`.env.example`** im gleichen Ordner wie im Git-Repo. Fehlt die Vorlage oder zippt ihr ohne Punktdateien, durchsucht `clh-provision-tenant.sh` Extrakt/Baum nach einer Vorlage; andernfalls legt das Skript eine **leere `.env`** an — Pflichtvariablen setzen anschließend das eingebaute Python‑Snippet sowie `php artisan key:generate`.

---

## Schritt 4 — Nginx für den Provisioner: Hostname eintragen und aktivieren

1. Öffne die Beispieldatei und setze **`server_name`** auf deinen **echten** Provisioner-Hostnamen (nicht Platzhalter, den du nicht im DNS hast):

   ```bash
   sudo nano /etc/nginx/sites-available/clh-provisioner.conf
   ```

   Ersetze die Zeile `server_name provision.app.creatorlinkhub.eu;` durch z. B. `server_name provision.app.deinedomain.de;`

2. Site aktivieren und Nginx testen:

   ```bash
   sudo ln -sf /etc/nginx/sites-available/clh-provisioner.conf /etc/nginx/sites-enabled/
   sudo nginx -t && sudo systemctl reload nginx
   ```

Der Proxy leitet weiter auf `http://127.0.0.1:9100` — dort läuft der Provisioner-Prozess.

---

## Schritt 5 — TLS einrichten (Let's Encrypt)

### Option A — Nur der Provisioner-Hostname (empfohlen zum Start)

Voraussetzung: DNS aus Schritt 1 zeigt bereits auf diesen Server, Port **80** ist erreichbar.

```bash
sudo apt-get update
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d provision.app.deinedomain.de
```

Certbot ergänzt SSL in der Site und kümmert sich um Erneuerung. Test:

```bash
sudo certbot renew --dry-run
```

### Option B — Wildcard für alle Tenant-Hosts `*.app.deinedomain.de`

Wildcard-Zertifikate bei Let's Encrypt erfordern typischerweise **DNS-01** (TXT-Record), nicht nur Port 80. Vorgehen:

```bash
sudo apt-get install -y certbot
sudo certbot certonly --manual --preferred-challenges dns \
  -d app.deinedomain.de -d '*.app.deinedomain.de'
```

Certbot gibt dir den **TXT-Record** für `_acme-challenge` aus; im DNS eintragen, propagieren lassen, in Certbot bestätigen. Anschließend **Nginx** der Tenant-Server-Blocks (vom Provisioner erzeugt) oder ein gemeinsames `ssl_certificate` auf diese Pfade legen — das ist betrieblicher Feinschliff (meist ein gemeinsamer `listen 443 ssl` mit `fullchain.pem`/`privkey.pem`).

---

## Schritt 6 — Funktion ohne Marketing testen

### 6.1 Dienst und lokaler Health-Check

```bash
sudo systemctl status clh-provisioner.service
curl -sS http://127.0.0.1:9100/health
curl -sS http://127.0.0.1:9100/
```

Erwartung: JSON mit `"ok":true` und `"service":"clh-provisioner"` (GET `/`, `/health` oder Pfade auf `…/health` — gleiche Implementierung in `deployment/cloud-host/provisioner.php`).

### 6.2 Von außen (nach DNS + Nginx + TLS)

```bash
curl -sS https://provision.app.deinedomain.de/health
```

---

## Schritt 7 — Marketing-Backend anbinden

Damit **creatorlinkhub.eu** (oder dein Laravel-Marketing) Tenants **anlegen/löschen/sperren** kann:

1. **Secret synchronisieren**  
   Auf dem App-Host:

   ```bash
   sudo cat /etc/clh-provisioner/secret
   ```

   Diesen **kompletten** Inhalt (typisch eine Zeile mit **64 Hex-Zeichen**) im Marketing unter **`provisioner.hmac_secret`** eintragen. Abweichung **ein** Zeichen → **401 invalid signature**.
   
   Das Marketing signiert mit **`hash_hmac('sha256', <roher_JSON-Body als String>, <Secret als Rohstring>)`** — der Provisioner verwendet denselben Rohstring aus der Datei (kein `hex2bin` auf dem Schlüssel). Ältere Doku/Skriptvarianten mit binärem Schlüssel sind **nicht** kompatibel.

2. **URL des Provisioners**  
   Setze die **öffentliche Basis-URL** (mit HTTPS), unter der dein Nginx den Backend-Port 9100 ausliefert, z. B.:

   `https://provision.app.deinedomain.de`

   Der genaue URL-Pfad spielt keine Rolle, solange Nginx denselben Upstream `127.0.0.1:9100` bedient — der Provisioner verarbeitet **POST mit JSON**, unabhängig vom Pfad.

### Signatur-Vertrag & JSON (Marketing / Debugging)

Identisch zur Referenz‑Implementierung in **`creatorlinkhub.eu`** (`Clh\CloudProvisioner`):

- **Header:** `X-CLH-Signature` = kleingeschriebene Hex‑Ausgabe von `hash_hmac('sha256', $rawBody, $secret)`
- **`$secret`:** exakt Zeichenkette wie in `/etc/clh-provisioner/secret` nach `trim` (bei Standard‑Setup eine **64‑Zeichen‑Hex‑Zeichenkette** als Text, nicht binär dekodiert)
- **`$rawBody`:** exakt dieselben Bytes wie der POST‑Body (**roh**, vor JSON‑Parsing — Reihenfolge der Objekt‑Schlüssel wie vom Sender erzeugt)
- Im JSON zusätzlich: **`ts`** (unix time, ± 300 s), **`nonce`** (32 hex Zeichen), **`action`**: **`create`** | **`delete`** | **`suspend`**, sowie `slug`, `domain` und bei `create` `admin_email`, `admin_name`

Optional: Kopien der Dateien **`provisioner.php`** und **`router.php`** liegen in beiden Repos (**`creator-link-hub`** und **`creatorlinkhub.eu`**) unter `deployment/cloud-host/` und sollen identisch bleiben.

---

## Schritt 8 — Ersten Tenant anlegen

1. Stelle sicher, dass DNS-Wildcard und ggf. TLS für **Kunden-Hostnamen** passen (`server_name` in den Tenant-Configs kommt vom Provision-Skript: **vollständiger Hostname**, den du beim Aufruf übergibst).
2. Löse die Aktion vom **Marketing** aus ( oder testweise ein signiertes POST mit denselben Regeln wie oben ).
3. Prüfen:

   ```bash
   ls /var/www/clh-tenants/
   sudo mysql -e "SHOW DATABASES LIKE 'clh_%';"
   ```

Logs bei Problemen:

```bash
sudo journalctl -u clh-provisioner.service -n 80 --no-pager
sudo tail -n 80 /var/log/nginx/error.log
```

---

## Schritt 9 — Nach einem Release: ZIP aktualisieren

Neue APP-Version:

```bash
sudo cp /pfad/zu/neues-release.zip /opt/clh-releases/current.zip
sudo chmod 644 /opt/clh-releases/current.zip
```

Bestehende Tenants aktualisierst du **nicht** automatisch — das ist eigene Policy (SSH/ZIP/`update-application.sh` pro Tenant).

---

## Architektur (Kurzüberblick)

```text
Internet → Nginx :443 provision.* → Proxy → 127.0.0.1:9100 (router.php → provisioner.php)
                → Nginx :80/443 tenant.* → je Tenant /var/www/clh-tenants/<slug>/public
Marketing (creatorlinkhub.eu) → HTTPS + HMAC POST → Provisioner-Host
MariaDB auf dem VPS → eine DB pro Tenant (clh_<slug>)
```

Skripte (nur per `sudo` vom User `clh-provisioner`):

| Skript | Zweck |
|--------|--------|
| `clh-provision-tenant.sh` | Neuer Tenant: ZIP auspacken, `.env`, Composer/Artisan, Nginx-Site |
| `clh-delete-tenant.sh` | Tenant entfernen (DB, Dateien, Nginx) |
| `clh-suspend-tenant.sh` | Tenant Site aus `sites-enabled` nehmen |

---

## Wo noch nachlesen

| Dokument | Inhalt |
|----------|--------|
| [`README.md`](../../README.md) (Projektroot) | MVP-Features, Verweis Cloud vs. Self-Host |
| [`vps/README.md`](../../vps/README.md) | Komponenten- und Pfadübersicht App-VPS |
| [`docs/deployment.md`](../deployment.md) | Laravel-Betrieb allgemein; Tabelle Self-Host / Cloud |
| [`docs/launch-runbook.md`](../launch-runbook.md) | Go-Live-Checkliste (ein Produkt) |
| [`docs/self-host-installation/README.md`](../self-host-installation/README.md) | **Eine** Installation — nicht die Cloud-Route |
| Marketing-Repo (optional) | `deployment/cloud-host/README.md` |

---

## Self-Host vs. Cloud

| | Self-Host | Cloud App-Host |
|---|-----------|----------------|
| Einstieg | `install-server.sh` | `bootstrap-cloud-host.sh` |
| DB-Produkt | Postgres oder MariaDB (interaktiv) | MariaDB / Tenant-Skript |
| Instanzen | eine | viele unter `tenants_root` |

---

Bei Änderungen an Skripten: die **Kommentarköpfe** in `scripts/*.sh` im Repo sind die technische Kurzreferenz.
