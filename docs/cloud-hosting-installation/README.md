# Creator Link Hub — Cloud-Host einrichten (Schritt für Schritt)

> **Installation direkt vom GitHub:** Auf der Repository-Startseite im Abschnitt **„Cloud-Multi-Tenant“** → *Direkt vom GitHub installieren* steht der Kopierbefehl mit [`install-cloud-host-interactive.sh`](../../scripts/install-cloud-host-interactive.sh) (Rohdownload von Branch `main`). Details unten unter [Schnellstart: Interaktiver Installer](#schnellstart-interaktiver-installer-empfohlen).

Diese Anleitung ist für **dich als Betreiber** eines **Multi-Tenant-App-Hosts**: ein VPS mit MariaDB, Nginx, Tenant-Verzeichnissen und einem kleinen **HTTP-Provisioner** (signierte API → `sudo` → `clh-provision-tenant.sh`). Dein **Marketing-Backend** (z. B. **creatorlinkhub.eu**, separates Repo) löst später Bestellungen aus und ruft diesen Provisioner auf.

> **Nicht** dasselbe wie Self-Host (eine Installation mit `install-server.sh`). Hier entstehen **viele** Laravel-Instanzen unter `/var/www/clh-tenants/{slug}`.

---

## Begriffe und Pfade (merken)

| Was | Typischer Pfad / Port |
|-----|-------------------------|
| Tenant-Installationen | `/var/www/clh-tenants/<slug>/` |
| Tenant App-Einstellungen (SMTP/Stripe, Admin-UI) | Tabelle `settings` (siehe [Admin-Dashboard](#admin-dashboard-smtp-und-stripe-je-tenant-filament)) |
| Release-ZIP für neue Tenants | `/opt/clh-releases/current.zip` |
| Provisioner (PHP builtin server) | `127.0.0.1:9100` (nur lokal) |
| Provisioner-Dateien | `/opt/clh-provisioner/` |
| Secret (HMAC) | `/etc/clh-provisioner/secret` |
| JSON-Konfig | `/etc/clh-provisioner/config.json` |
| Nginx-Beispiel (Provisioner vorn) | `/etc/nginx/sites-available/clh-provisioner.conf` |
| systemd-Dienst | `clh-provisioner.service` |

Ersetze in allen Beispielen **Platzhalter-Domains** durch deine echten Namen (`provision.deinedomain.de`, Zone für Kunden wie `*.app.deinedomain.de`).

---

## Schnellstart: Interaktiver Installer (empfohlen)

Alles Wesentliche in **einem** Ablauf: Das Skript fragt interaktiv u. a. nach Git-URL, Klonordner, Branch/Tag, **Provisioner-Hostnamen**, ob Nginx aktiviert, **Release-ZIP** gebaut werden soll (npm/Vite/`zip`), und ob **Certbot** laufen soll. Es läuft **als root**.

**Vorgehen A — bereits Repo auf dem VPS:**

```bash
cd /opt/creator-link-hub-src/creator-link-hub    # Pfad zu deinem Klon
sudo bash scripts/install-cloud-host-interactive.sh
```

**Vorgehen B — praktisch nacktes Ubuntu, nur Minimalpakete:**

```bash
sudo apt-get update && sudo apt-get install -y curl ca-certificates
sudo curl -fsSL -o /tmp/install-cloud-host-interactive.sh \
  https://raw.githubusercontent.com/Sebastian-1892/creator-link-hub/main/scripts/install-cloud-host-interactive.sh
sudo bash /tmp/install-cloud-host-interactive.sh
```

(Skript liegt im Repo unter [`scripts/install-cloud-host-interactive.sh`](../../scripts/install-cloud-host-interactive.sh); roher Download nur sinnvoll ab dem Branch/Tag mit diesem Skript.)

**Was der Wizard automatisch mit erledigt (je nach Auswahl):** Git-Clone oder Update unter dem gewünschten Elternordner, [`scripts/bootstrap-cloud-host.sh`](../../scripts/bootstrap-cloud-host.sh) (inkl. **UFW**: von außen typisch nur **SSH, 80, 443**), `server_name` in `clh-provisioner.conf` setzen, Site in `sites-enabled` und `nginx reload`, optional [`scripts/build-cloud-release-zip.sh`](../../scripts/build-cloud-release-zip.sh) sowie Kopie nach `/opt/clh-releases/current.zip`, optional **TLS** für den einen Provisioner-Host.

**Nach dem Wizard:** wildcard-DNS für Kunden-Hostnamen, Marketing (`provisioner.hmac_secret`, URL) — weiter wie [Schritt 7](#schritt-7--marketing-backend-anbinden). Wildcard-/Tenant-TLS sind weiterhin eigener Feinschliff (gleiche Grundlagen wie in dieser Doku).

---

## Vorarbeit: Checkliste

- [ ] **VPS** mit Debian/Ubuntu LTS, SSH, idealerweise nur du als Admin.
- [ ] Öffentliche **IPv4** (und ggf. IPv6); **Bootstrap** aktiviert **UFW**: eingehend u. a. **SSH (OpenSSH)**, **80**, **443** — **9100** bleibt nur **loopback** (Provisioner). Eigenen **SSH-Port** ggf. nach dem Bootstrap mit `ufw allow …/tcp` ergänzen.
- [ ] **`creator-link-hub`** auf dem Server: **[Schnellstart: Interaktiver Installer](#schnellstart-interaktiver-installer-empfohlen)** oder manuell [Installation über Git](#installation-über-git) — Alternativ: entpacktes Archiv mit `scripts/`.
- [ ] Eine fertige **Release-ZIP** der App für neue Tenants, oder Build aus dem geklonten Repo (siehe [Schritt 3](#schritt-3--release-zip-ablegen)).
- [ ] Entscheidung: **Provisioner-Hostname** (z. B. `provision.app.deinedomain.de`) und **Kunden-Basis** (z. B. `*.app.deinedomain.de`).

---

## Installation über Git (manuelle Einzelschritte)

Die folgenden Schritte entsprechen dem, was der [interaktive Installer](#schnellstart-interaktiver-installer-empfohlen) gebündelt kann — wenn du lieber jeden Befehl selbst steuerst.

So richtest du den Cloud-Host **vollständig aus dem Git-Repository** ein: Quellcode per `git clone`, anschließend wie gewohnt Bootstrap und DNS/TLS. Du brauchst **kein** separates Hochladen des Projektroots per SCP, wenn du diesen Weg gehst.

### 1. Repo klonen

Auf dem VPS ein **festes Arbeitsverzeichnis** wählen (lesbar für deinen SSH-User; `root` ist für `sudo` nicht zwingend nötig):

```bash
sudo mkdir -p /opt/creator-link-hub-src
sudo chown "$USER":"$USER" /opt/creator-link-hub-src
cd /opt/creator-link-hub-src
```

**Öffentliches Repository** (HTTPS):

```bash
git clone https://github.com/Sebastian-1892/creator-link-hub.git
cd creator-link-hub
```

**Privates Repository** nutzt du am sichersten mit **SSH-Deploy-Key** (nur Lesezugriff auf dieses Repo) oder einem persönlichen SSH-Key des Deploy-Users — dann:

```bash
git clone git@github.com:Sebastian-1892/creator-link-hub.git
cd creator-link-hub
```

### 2. Revisionsstand festlegen (empfohlen)

Für reproduzierbare Installationen einen **Tag** oder Branch auschecken:

```bash
git fetch --tags
git checkout v1.2.3    # Beispiel: ersetzen durch euren echten Tag
# oder: git checkout main
```

### 3. Bootstrap starten

Das Bootstrap-Skript **muss** aus dem Repo-Root laufen (dort liegen `scripts/bootstrap-cloud-host.sh` und `deployment/cloud-host/`):

```bash
cd /opt/creator-link-hub-src/creator-link-hub   # Pfad anpassen
sudo bash scripts/bootstrap-cloud-host.sh
```

Es installiert u. a. **UFW** (Firewall), **Git** (falls noch nicht da), Nginx, MariaDB, PHP-FPM, kopiert `provisioner.php` / `router.php` nach `/opt/clh-provisioner/` und richtet systemd ein — wie in [Schritt 2](#schritt-2--bootstrap-ausführen-einmalig) beschrieben.

### 4. Release-ZIP aus demselben Clone

Neue Tenants bekommen immer eine **ZIP** unter `/opt/clh-releases/current.zip`. Du kannst sie **auf dem VPS** aus dem geklonten Repo bauen — **Voraussetzung:** `node` und `npm` im `PATH` (das Bootstrap-Skript installiert **kein** Node.js). Installationsbeispiele stehen in [Schritt 3](#schritt-3--release-zip-ablegen) („Node.js auf dem VPS“).

```bash
cd /opt/creator-link-hub-src/creator-link-hub
bash scripts/build-cloud-release-zip.sh
sudo cp distribution/releases/current-cloud.zip /opt/clh-releases/current.zip
sudo chmod 644 /opt/clh-releases/current.zip
```

**Alternative:** ZIP lokal oder in CI bauen und nur **`current.zip`** nach `/opt/clh-releases/` kopieren — das Repository muss auf dem VPS dann trotzdem für **Bootstrap und Skripte** vorliegen, wenn du „alles über Git“ fahren willst.

### 5. Spätere Updates

**Empfohlen (nach Bootstrap):** Auf dem VPS als root:

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh
```

Optional mit neuem Release-ZIP für **zukünftige** Tenant-Neuanlagen (benötigt `node`/`npm` und `zip` auf dem Server):

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip
```

Das Skript liest den **Git-Klon-Pfad** und den **Branch** aus `/etc/clh-provisioner/install-paths.env` (wird beim Bootstrap angelegt). Bei Umzug des Repos diesen Wert von `CLH_REPO_ROOT` anpassen.

**Manuell (Fallback):** `git pull` im Klon-Verzeichnis; `deployment/cloud-host/provisioner.php` und `router.php` nach `/opt/clh-provisioner/` kopieren und Dienst neu starten — siehe [vps/README.md](../../vps/README.md). ZIP neu bauen und nach `/opt/clh-releases/current.zip` legen ([Schritt 9](#schritt-9--nach-einem-release-zip-aktualisieren)).

> **Hinweis:** Bestehende Tenant-Instanzen unter `/var/www/clh-tenants/` werden durch das Update-Skript **nicht** automatisch angehoben.

Danach wie unten: **DNS** → **Nginx/TLS** → **Tests** → Marketing.

---

## Schritt 1 — DNS vorbereiten

1. Lege einen **A-Record** für den **Provisioner-Host** auf die Server-IP (z. B. `provision.app` → `203.0.113.10`).
2. Lege einen **Wildcard** für die **Tenant-Subdomains** auf dieselbe IP (z. B. `*.app` in der Zone `deinedomain.de`, je nach DNS-Anbieter als `*.app` oder explizite Einträge).

> Ohne funktionierendes DNS zu diesem Server schlagen **TLS** und spätere **Kunden-URLs** fehl.

Warte, bis die Auflösung von deinem Rechner aus stimmt (`dig` / `nslookup`).

---

## Schritt 2 — Bootstrap ausführen (einmalig)

**Voraussetzung:** Du stehst im **Repository-Root** (bei Installation über Git z. B. `/opt/creator-link-hub-src/creator-link-hub`), siehe [Installation über Git](#installation-über-git).

Auf dem VPS:

```bash
cd /pfad/zu/creator-link-hub
sudo bash scripts/bootstrap-cloud-host.sh
```

**Erwartung:** farbige Ausgabe mit **Schritt x / 13** (über **13** Arbeitsschritte). Der längste Block ist meist **`apt install`** — viele Zeilen von apt/dpkg sind normal.

Das Skript installiert u. a. **`ufw`** (öffentlich typisch nur **SSH, 80/tcp, 443/tcp**), **nginx**, **mariadb-server**, **PHP-FPM 8.2–8.4**, kopiert **`deployment/cloud-host/provisioner.php`** und **`deployment/cloud-host/router.php`** nach `/opt/clh-provisioner/`, legt User **`clh-provisioner`**, **sudoers**, **systemd** und eine **Nginx-Beispieldatei** an. **Provisioner:** `127.0.0.1:9100` (von außen nicht freigegeben).

**Firewall prüfen:** `sudo ufw status numbered`.

> **Hinweis:** `router.php` liest den HTTP-Body einmal aus `php://input` und reicht ihn an `provisioner.php` weiter. Ohne diese Datei kann der PHP Built-in Server beim Signatur‑Check **`401 invalid signature`** liefern, obwohl Secret und Marketing-Code stimmen.

**Ende:** Abschnitt „Bootstrap abgeschlossen. Nächste Schritte:“ — die folgenden Schritte hier in der Doku sind die **ausführliche** Version davon.

---

## Schritt 3 — Release-ZIP ablegen

### Voraussetzungen auf dem VPS für den ZIP-Build

- **Node.js / npm** für `npm ci` und `npm run build` (Vite). Fehlt `npm`, bricht das Skript ab — es gibt **keine** `distribution/releases/current-cloud.zip`.
- **ZIP-Programm** (`zip` im PATH, Debian/Ubuntu-Paket **`zip`**). Das Cloud-Bootstrap installiert seit dem Repo-Update **`zip`** zusammen mit **`unzip`**; auf älteren Servern ggf. nachinstallieren:

  ```bash
  sudo apt-get install -y zip
  ```

`zip: command not found` nach erfolgreichem Vite-Build bedeutet: Paket **`zip`** installieren, Build-Skript **erneut** ausführen (damit werden Archiv und `unzip`-Test erzeugt).

### Node.js auf dem VPS

`scripts/build-cloud-release-zip.sh` ruft **`npm ci`** und **`npm run build`** auf.

**Prüfen:**

```bash
command -v node && node -v
command -v npm && npm -v
```

**Option A — Ubuntu/Debian: Distribution-Pakete** (oft ausreichend, z. B. Ubuntu 24.04 „Noble“ mit Node 18+):

```bash
sudo apt-get update
sudo apt-get install -y nodejs npm
```

**Option B — Node.js 20.x LTS (NodeSource, empfohlen wenn `apt`-Node zu alt fehlt):**

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

Danach erneut `node -v` / `npm -v` prüfen, dann im **Repository-Root**:

```bash
bash scripts/build-cloud-release-zip.sh
```

### ZIP erzeugen und nach `/opt/clh-releases/` legen

**ZIP bauen** (lokal, in CI oder **auf dem VPS** im gleichen Git-Klon — siehe oben: Node/npm):

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

### 6.3 Testweise einen Tenant ohne Marketing anlegen (Skript direkt auf dem VPS)

So bekommst du **eine echte App-URL** (Laravel unter deinem Tenant-Hostnamen), **ohne** Marketing und **ohne** den HMAC-Provisioner — nützlich für Smoke-Tests nach dem Bootstrap.

**Voraussetzungen**

- **DNS:** A-Record (oder passender Wildcard) für den gewählten **`--domain`** muss auf diese Maschine zeigen — siehe [Schritt 1](#schritt-1--dns-vorbereiten). Für **Let’s Encrypt** (Standard) muss **Port 80** von außen bis zur Challenge erreichbar sein.
- **Release-ZIP:** typisch `/opt/clh-releases/current.zip` (wie [Schritt 3](#schritt-3--release-zip-ablegen)); abweichend: `--release-zip /pfad/zur.zip`.
- **Slug:** erlaubtes Muster wie beim Provisioner (`a-z0-9`, Bindestriche, Länge — siehe Validierung in [`deployment/cloud-host/provisioner.php`](../../deployment/cloud-host/provisioner.php)); Verzeichnis **`/var/www/clh-tenants/<slug>/`** darf noch **nicht** existieren.

**Auf dem App-Host per SSH** (ein User mit `sudo`; Befehl als eine Zeile oder mit `\` wie unten):

```bash
sudo /usr/local/bin/clh-provision-tenant.sh \
  --slug demo \
  --domain demo.app.deinedomain.de \
  --admin-email du@deinedomain.de \
  --release-zip /opt/clh-releases/current.zip
```

Optional **Anzeigename** des ersten Admins:

`--admin-name "Test Admin"`

Wenn **`tenants_root`** in `/etc/clh-provisioner/config.json` von `/var/www/clh-tenants` abweicht, denselben Wert hier mitgeben:

`--tenant-root /dein/pfad`

**TLS überspringen** (reiner HTTP-Test, z. B. nur intern oder ohne DNS-Le):

```bash
sudo /usr/local/bin/clh-provision-tenant.sh \
  --slug demo \
  --domain demo.app.deinedomain.de \
  --admin-email du@deinedomain.de \
  --release-zip /opt/clh-releases/current.zip \
  --no-tls
```

**Nach erfolgreichem Lauf**

- **Stdout:** eine JSON-Zeile mit u. a. `instance_url` und `admin_url` (`…/admin`). **stderr** enthält Lognachrichten des Skripts.
- Im Browser die **Tenant-URL** öffnen (bei TLS: `https://<domain>/`, sonst `http://…`).
- **Admin:** unter `…/admin` mit der bei `--admin-email` gesetzten Adresse anmelden. Das **initiale Passwort** wird beim Seeding **nicht** auf der Konsole ausgegeben. Für einen bekannten Test kannst du es im Tenant-Verzeichnis neu setzen (≥ 8 Zeichen):

  ```bash
  cd /var/www/clh-tenants/demo   # slug anpassen
  sudo -u www-data env \
    CLH_ADMIN_EMAIL="du@deinedomain.de" \
    CLH_ADMIN_PASSWORD="dein-sicheres-passwort" \
    CLH_ADMIN_NAME="Admin" \
    php artisan db:seed --class=Database\\Seeders\\InstallAdminSeeder --force
  ```

**Hinweis:** Derselbe Ablauf läuft intern, wenn der **HTTP-Provisioner** mit `action: create` aufgerufen wird — du rufst hier nur direkt das Skript auf, das der Provisioner sonst per `sudo` startet.

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

1. Stelle sicher, dass ein **A-Record** für den **Tenant-Hostnamen** auf den VPS zeigt und **Port 80** von außen erreichbar ist — das Provision-Skript fordert **standardmäßig Let’s Encrypt** per **`certbot certonly --webroot`** unter **`…/public/.well-known`**, schreibt danach **stabile Nginx‑Blöcke** (HTTP: ACME + Redirect, HTTPS: App) und setzt **`APP_URL=https://…`**. Für reine Tests ohne TLS: **`--no-tls`**. DNS-**Wildcard** für viele Kunden-Unterdomains bleibt wie in den TLS-Abschnitten weiter oben beschrieben.
2. Tenant anlegen: vom **Marketing** aus, **ohne Marketing** wie in **[6.3 Testweise einen Tenant ohne Marketing](#63-testweise-einen-tenant-ohne-marketing-anlegen-skript-direkt-auf-dem-vps)** (Skript direkt auf dem VPS), oder testweise ein **signiertes POST** wie in [Schritt 7](#schritt-7--marketing-backend-anbinden).
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

## Admin-Dashboard: SMTP und Stripe je Tenant (Filament)

Nach dem Deployment der App (Migration **`settings`** ausgeführt: `php artisan migrate --force`) können Betreiber mit **`users.is_admin = true`** im Filament-Admin (**`/admin`**) unter der Navigationsgruppe **„System“** pflegen:

| Seite | Pfad | Inhalt |
|-------|------|--------|
| **E-Mail (SMTP)** | `/admin/mail-settings` | Mailer, SMTP-Host/Port/Scheme, Zugangsdaten, Absender; optional **Test-Mail** |
| **Stripe & Pläne** | `/admin/stripe-settings` | Stripe Publishable/Secret/Webhook-Secret sowie **Price-IDs** je Plan (`free` / `starter` / `pro`), passend zu den Produkten im Stripe-Dashboard |

**Persistenz:** Die Werte liegen in der Tabelle **`settings`** (sensible Felder verschlüsselt mit **`APP_KEY`**). **`RuntimeConfigServiceProvider`** überschreibt zur Laufzeit `config('mail.*')`, `config('cashier.*')` und `config('creator.stripe_prices.*')` — Laravel Mail, Cashier (Checkout/Webhook) und die bestehende Billing-UI nutzen dieselben Konfig-Pfade wie bei Installation über **`.env`**.

**Fallback:** Fehlt ein DB-Eintrag für einen Wert, gilt weiterhin die Tenant-**`.env`** (wie nach `clh-provision-tenant.sh`). Eintrag im UI **ersetzt** den `.env`-Wert nur für dieses Schlüsselfeld.

**Stripe-Webhook:** Endpoint bleibt **`POST /stripe/webhook`** (Cashier, Prefix aus `CASHIER_PATH`). Das Signing-Secret kann im Admin gesetzt oder weiterhin via **`STRIPE_WEBHOOK_SECRET`** in der `.env` gepflegt werden.

### Anwendungs-Update im Dashboard (`composer` / `update-application.sh`)

Das Dashboard startet [`scripts/update-application.sh`](../../scripts/update-application.sh) im Tenant-Verzeichnis. **PHP-FPM** läuft dabei als **`www-data`**. Schlägt **Composer** mit **`Permission denied`** bei **`vendor/composer/…`** fehl, gehören **`vendor/`** und andere Ordner oft noch **`root:root`** — beim Provisioning wurde **`composer install`** zuvor als **root** ausgeführt, ohne abschließende **`chown`** für die gesamte Installation.

**Einmalige Korrektur auf dem VPS** (Slug durch den echten Tenant ersetzen):

```bash
sudo chown -R www-data:www-data /var/www/clh-tenants/SLUG
sudo chmod -R ug+rwx /var/www/clh-tenants/SLUG/storage /var/www/clh-tenants/SLUG/bootstrap/cache
```

Anschließend das Update im Admin erneut ausführen. Mit aktuellem **`clh-provision-tenant.sh`** wird nach den Artisan-Caches **`chown -R www-data:www-data`** auf das Tenantroot gesetzt — neue Tenants sind davon nicht betroffen.

**npm (`EACCES`, Pfad `/var/www/.npm`):** Unter Debian/Ubuntu ist das Home von **`www-data`** oft **`/var/www`** — npm nutzt standardmäßig **`~/.npm`** → **`/var/www/.npm`**. Wenn dort einmal **`npm`** als **root** gelaufen ist, gehören Dateien **`root`** → beim Dashboard-Update als **`www-data`** schlägt **`npm ci`** mit **`mkdir`** / **`errno -13`** fehl — **nach** erfolgreichem Filament („Successfully published assets!“). Einmal beheben:

```bash
sudo chown -R www-data:www-data /var/www/.npm
```

Mit aktuellem Repo setzt [`scripts/update-application.sh`](../../scripts/update-application.sh) zusätzlich **`NPM_CONFIG_CACHE=$PWD/storage/npm-cache`**, damit der Cache **im Tenant** liegt und dieselben Rechte wie **`storage/`** hat (ersetzt das Problem langfristig).

---

## E-Mail aus Tenant-Apps — Standard **sendmail** (ohne SMTP in der Bestellung)

Kunden-Cloud-Instanzen erhalten bei der Erstellung **keine** SMTP-Zugangsdaten aus dem Marketing. Stattdessen setzt `clh-provision-tenant.sh` in der Tenant-`.env` u. a.:

| Variable | Bedeutung |
|----------|-----------|
| `MAIL_MAILER` | `sendmail` — Laravel übergibt an **`/usr/sbin/sendmail`** (Symfony/Laravel-Transport) |
| `MAIL_FROM_ADDRESS` | `noreply@<Tenant-Hostname>` (z. B. `noreply@test.app.creatorlinkhub.eu`) |
| `MAIL_FROM_NAME` | `Creator Link Hub` |

**Host / MTA:** Tenant-Mail braucht **`/usr/sbin/sendmail`**. Beim **ersten neuen Tenant** installiert **`clh-provision-tenant.sh`** fehlendes **Postfix** selbst (`apt-get install`, debconf non-interactive, „Internet Site“, `mailname` = FQDN oder `hostname`). Schlägt das fehl, bricht das Provisioning mit Fehler ab — kein nur noch halb nutzbarer Mail-Stack ohne MTA.

**Bootstrap:** **`scripts/bootstrap-cloud-host.sh`** installiert **Postfix** ebenfalls, damit frisch eingerichtete Hosts den MTA schon haben, bevor der erste Tenant angelegt wird (doppeltes `apt-get install postfix` ist harmlos).

**Zustellbarkeit:** Direktversand vom VPS funktioniert je nach Ruf des Servers, DNS (SPF/PTR) und Empfänger-Policy; manche Postfächer sortieren streng. Langfristig können Betreiber einen **Smarthost**/Relay konfigurieren oder **SMTP in der App** setzen: seit den Admin-Seiten **`/admin/mail-settings`** mit Persistenz in **`settings`** (siehe Abschnitt [Admin-Dashboard: SMTP und Stripe](#admin-dashboard-smtp-und-stripe-je-tenant-filament)); alternativ weiterhin nur Tenant-**`.env`** (`MAIL_MAILER=smtp`, …) oder Support-Anpassung.

**Bestehende Tenants** (vor diesem Stand): In `…/tenant/.env` dieselben `MAIL_*`-Schlüssel ergänzen/anpassen, dann im Tenant-Verzeichnis:

```bash
sudo -u www-data php artisan config:cache
```

---

## Fehlersuche: Kunden-URL zeigt Provisioner-JSON oder `nginx` meldet **404 Not Found**

### A) Unter `https://…` erscheint die Provisioner-Health-JSON oder `{"error":"not found"}`

**Ursache (ältere Installationen / kein TLS):** Es gab nur **HTTP** für den Tenant; unter **HTTPS** hat ein **anderer** vHost (z. B. **Provisioner** auf 443) zugeschlagen — JSON statt Laravel.

**Aktueller Stand im Repo:** `clh-provision-tenant.sh` nutzt **`certbot certonly --webroot`** (kein `certbot --nginx`, damit keine defekten Nginx‑Fragmente wie `return 404` entstehen), schreibt **HTTP/HTTPS‑Sites selbst**, setzt **`APP_URL=https://`** und **`config:cache`**. **Port 80** muss für die Challenge erreichbar sein; auf **:80** bleibt **`/.well-known/acme-challenge/`** auch nach Umstellung auf HTTPS erhalten (Renew).

**Manuell nachbessern** (wenn TLS beim Anlegen fehlgeschlagen ist oder alter Stand auf dem Server ohne Webroot‑Logik liegt):

```bash
sudo certbot certonly --webroot -w /var/www/clh-tenants/SLUG/public -d tenant.app.deinedomain.de
# Anschließend Nginx HTTPS für den Tenant ergänzen bzw. Skript aktualisieren und Tenant neu durchspielen (--no-tls löschen oder frischen Slug verwenden).
```

**Prüfen**, welcher vHost greift:

```bash
sudo nginx -T 2>/dev/null | grep -E 'server_name|listen|ssl_certificate' | head -80
curl -sS -o /dev/null -w '%{http_code}\n' -H 'Host: tenant.app.deinedomain.de' http://127.0.0.1/
```

### B) `404 Not Found` (Nginx-Fehlerseite, nicht Laravel)

Häufig: **Document-Root** zeigt nicht auf die Laravel-**`public/`** mit `index.php`, oder das App-Verzeichnis ist unvollständig.

**Sehr häufig:** Der Tenantordner **`/var/www/clh-tenants/SLUG`** hat Modus **`0700`** (z. B. weil beim Entpacken ein `mktemp`-Verzeichnis verschoben wurde) — dann kann **`www-data`** den Pfad zu **`…/public`** nicht durchlaufen, obwohl Nginx **`root`** richtig gesetzt hat. Abhilfe:

```bash
sudo chmod 0755 /var/www/clh-tenants/SLUG /var/www/clh-tenants/SLUG/public
sudo systemctl reload nginx
```

Neue Instanzen: im Repo setzt **`clh-provision-tenant.sh`** nach dem `mv` **`chmod 0755`** auf Installations- und `public/`-Ordner.

```bash
sudo grep -E 'root|server_name' /etc/nginx/sites-enabled/clh-SLUG.conf
ls -la /var/www/clh-tenants/SLUG/public/index.php
```

Ist **`artisan`** nicht unter `/var/www/clh-tenants/SLUG/`, liegt die App oft **in einem Unterordner** — dann ist der Tenantbaum fehl ausgerichtet; Nginx-`root` muss auf `…/APP/public` zeigen, wobei `…/APP` der Ordner mit `artisan` ist.

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
Erfolgs-JSON vom Provisioner enthält u. a. **`initial_admin_password`** (vom Skript erzeugt, gleicher Wert wie beim `InstallAdminSeeder` auf dem Tenant) — wird vom Marketing genutzt, um die „Cloud bereit“-E-Mail mit Erstpasswort zu versenden.
MariaDB auf dem VPS → eine DB pro Tenant (clh_<slug>)
```

Skripte (nur per `sudo` vom User `clh-provisioner`):

| Skript | Zweck |
|--------|--------|
| `clh-provision-tenant.sh` | Neuer Tenant: ZIP, `.env`, Composer/Artisan, Nginx + **Let’s Encrypt** (`certonly --webroot`), finale HTTP/HTTPS‑Sites im Repo‑Format, `chmod 755` Tenantroot, `APP_URL=https://…` (**`--no-tls`** möglich) |
| `clh-delete-tenant.sh` | Tenant entfernen (DB, Dateien, Nginx) |
| `clh-suspend-tenant.sh` | Tenant Site aus `sites-enabled` nehmen |
| `clh-resume-tenant.sh` | Tenant Site wieder aktivieren (`sites-available` → `sites-enabled`, Nginx reload) |

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
