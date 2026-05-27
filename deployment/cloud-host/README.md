# Cloud-Host (separater VPS)

Der **Marketing-Server** (dieses Repo auf KAS) startet keine Docker-Container. **Cloud-Kundeninstanzen** laufen auf einem eigenen Server (z. B. Hetzner, Debian/Ubuntu).

## DNS

- **Wildcard:** `*.app.creatorlinkhub.eu` → öffentliche IP des App-Hosts (Kunden-URLs: `<slug>.app.creatorlinkhub.eu`).
- **Provisioner-Host:** z. B. **`provision.app.creatorlinkhub.eu`** → dieselbe IP, TLS (Let’s Encrypt o. ä.).

## Ablauf (Marketing-Server)

1. Kunde wählt bei der Registrierung Plan **Cloud** und optional eine **Wunsch-Subdomain** (`<slug>.app.creatorlinkhub.eu`).
2. Nach **E-Mail-Bestätigung** (`/verify`): `cloud_status` wird `pending`, ein `provision_jobs`-Eintrag `action=create` wird eingereiht.
3. Cron `cron/run.php?token=…` oder `php bin/provision.php TOKEN` verarbeitet die Queue → `CloudProvisioner` sendet einen **signierten HTTPS-POST** an `provisioner.url` (Header `X-CLH-Signature` = HMAC-SHA256 über den **rohen** JSON-Body).
4. Der **Provisioner** auf dem VPS führt `sudo clh-provision-tenant.sh` aus und antwortet mit JSON u. a. **`instance_url`**, **`admin_url`**, **`initial_admin_password`** (einmaliges Admin-Passwort für Filament — identisch zum per Seed angelegten Nutzer).
5. Der Worker setzt `cloud_status=ready`, speichert `cloud_instance_url` und sendet die **HTML**-E-Mail „Cloud bereit“ (Layout wie die Bestätigungs-Mail) inkl. **Instanz-URL**, **Admin-Link** und **Passwort**.

**Stripe:** Nach erfolgreichem Checkout setzt der Webhook Stripe-IDs auf der Lizenz. Ein **zusätzliches** Provisioning wird nur noch eingereiht, wenn `cloud_status` noch `none` oder `failed` ist (nicht bei `pending` / `provisioning` / `ready`).

**Kündigung / Abo-Ende:** `customer.subscription.deleted` setzt die Lizenz auf gekündigt, `cloud_status=suspended` und legt optional einen Job `action=suspend` an (Nginx-Site aus, Daten bleiben).

**Account löschen (Cloud):** Bei gesetzter Custom Domain zuerst Job `remove_custom_domain`, danach `delete`. Stripe-Abos werden sofort gekündigt, der Nutzer wird **deaktiviert** (`disabled_at`). Nach erfolgreichem Tenant-Löschen auf dem VPS wird der Nutzer endgültig aus der DB entfernt (Slug wieder frei). Die Subdomain zeigt danach die „frei“-Landingpage; die Custom Domain wird vom VPS abgebaut (Zertifikat/Nginx), DNS beim Kunden sollte entfernt werden.

## Custom Domain (CNAME)

1. Kunde trägt unter **Mein Konto** eine Domain ein (`licenses.custom_domain`, `cname_status=pending`).
2. **DNS:** CNAME der Domain (z. B. `www`) auf den Cloud-Host **`{slug}.app.creatorlinkhub.eu`** (Spalte `licenses.domain`).
3. Cron prüft CNAME-Kette (`CustomDomainDnsCheck`) mit Backoff; bei Erfolg `cname_status=verified` und Job `provision_jobs.action=custom_domain`.
4. VPS: `clh-tenant-custom-domain.sh --mode add` — Nginx vHost + Let's Encrypt (HTTP-01) + `APP_EXTRA_HOSTS` in der Tenant-`.env`, `php artisan config:cache`.
5. Entfernen: Konto-UI → Job `remove_custom_domain`, Felder auf `none`.

**Migrationen:** `0013_custom_domain.sql`, `0014_provision_custom_domain.sql` (`php bin/migrate.php`).

### Staging E2E (Checkliste)

1. Migration auf Marketing-DB, Code deployen, VPS: `clh-tenant-custom-domain.sh` + Provisioner deployen (`clh-cloud-host-update.sh`).
2. Instanz `cloud_status=ready`; Domain eintragen → `pending`.
3. CNAME auf Staging-Subdomain setzen; Cron oder „DNS jetzt prüfen“ → `verified`, `provision_jobs` → `done`.
4. `curl -sI https://www.example.test` — Zertifikat und 200/302 von Laravel.
5. Negativ: falscher CNAME → `failed` nach Backoff; E-Mail bei finalem Fehlschlag.

## Konfiguration (Marketing-Server)

In `config/config.php`:

- **`provisioner.url`:** exakt die **öffentliche HTTPS-URL**, unter der ein **POST** (mit JSON-Body) beim Provisioner ankommt — **identisch** zu dem, was Nginx per `proxy_pass` auf **`127.0.0.1:9100`** weiterleitet. Beispiele:
  - `https://provision.app.creatorlinkhub.eu/`
  - oder mit Pfad, falls Nginx nur einen Unterpfad weiterleitet: `https://provision.app.creatorlinkhub.eu/v1/instances`  
  Der PHP-Provisioner wertet den **Pfad** nicht unterschiedlich aus; wichtig ist nur, dass **dieselbe** URL vom Marketing-Server erreichbar ist.
- **`provisioner.hmac_secret`:** **eine Zeile**, **identisch** mit dem Inhalt von **`/etc/clh-provisioner/secret`** auf dem VPS (kein Leerzeichen am Ende). **`hash_hmac`** nutzt diese Zeichenkette **direkt** (keine `hex2bin`-Dekodierung des Schlüssels). Ein Zeichen Abweichung → **401 invalid signature**.

```php
'provisioner' => [
    'url' => 'https://provision.app.creatorlinkhub.eu/',
    'hmac_secret' => '…hex aus /etc/clh-provisioner/secret…',
],
```

Leer lassen = Provisioning schlägt mit klarer Fehlermeldung fehl.

**Cron:** `cron.token` in `config/config.php` muss mit der URL im KAS-Cron übereinstimmen (`…/cron/run.php?token=…`).

## Einrichtung des VPS (creator-link-hub Repo)

1. **Release-ZIP** der Laravel-App bauen (`composer install --no-dev`, **`npm run build`**) und nach **`/opt/clh-releases/current.zip`** legen.
2. **`sudo bash scripts/bootstrap-cloud-host.sh`** (installiert u. a. Nginx, MariaDB, PHP-FPM, kopiert `clh-provision-tenant.sh`, **`clh-delete-tenant.sh`**, **`clh-suspend-tenant.sh`**, **`clh-resume-tenant.sh`** nach `/usr/local/bin`, User **`clh-provisioner`**, systemd **`clh-provisioner.service`**, setzt Verzeichnisrechte unter `/etc/clh-provisioner` so, dass der Dienst **`secret`** und **`config.json`** lesen kann).
3. **`provisioner.php`** und **`router.php`** liegen unter **`/opt/clh-provisioner/`** (beides deployen; der Router puffert `php://input` einmal, damit die HMAC beim PHP Built-in Server zuverlässig mit dem rohen Body übereinstimmt).
4. **Nginx:** Beispiel **`/etc/nginx/sites-available/clh-provisioner.conf`** (Bootstrap: `server_name provision.app.creatorlinkhub.eu`) aktivieren, **`nginx -t`**, reload, **TLS** (z. B. certbot) für diesen Hostnamen.
5. **Geheimnis:** Inhalt von **`/etc/clh-provisioner/secret`** 1:1 als **`provisioner.hmac_secret`** auf dem Marketing-Server eintragen (oder beide Seiten auf denselben neuen Wert setzen).

### Firewall (VPS)

- **Öffentlich erlauben:** **80/tcp** und **443/tcp** (HTTP/HTTPS für Provisioner und spätere Tenant-Hosts).
- **Nicht** öffentlich öffnen: **9100** — der PHP-Built-in-Server lauscht nur **`127.0.0.1:9100`**; Nginx terminiert TLS und proxy’t lokal.

### Secret / Rechte (häufige Fehlerquelle)

Der Dienst läuft als **`clh-provisioner`**. Es muss gelten:

```bash
sudo chown root:clh-provisioner /etc/clh-provisioner
sudo chmod 0750 /etc/clh-provisioner
sudo chown root:clh-provisioner /etc/clh-provisioner/secret /etc/clh-provisioner/config.json
sudo chmod 0640 /etc/clh-provisioner/secret /etc/clh-provisioner/config.json
sudo -u clh-provisioner test -r /etc/clh-provisioner/secret && echo OK
sudo systemctl restart clh-provisioner
```

*(Neuere Bootstrap-Skripte setzen `/etc/clh-provisioner` bereits auf `0750` + Gruppe `clh-provisioner`.)*

### Kurztest (von außen)

```bash
# GET (optionaler Health-Check, je nach Nginx — nicht zwingend aus provisioner.php)
curl -skS -w "\nHTTP:%{http_code}\n" "https://provision.app.creatorlinkhub.eu/"

# POST ohne Signatur: erwartet HTTP 401 + invalid signature (wenn Secret lesbar und Provisioner aktiv)
curl -skS -w "\nHTTP:%{http_code}\n" \
  -X POST "https://provision.app.creatorlinkhub.eu/" \
  -H "Content-Type: application/json" \
  -d '{}'
```

**500** mit Meldung zum Secret / nicht lesbar → Rechte wie oben. **401** `invalid signature` bei leerem `{}` → **in Ordnung** für den Signaturpfad.

## Weitere Dateien

- **GET** `/` oder `/health` (bzw. …`/health`) liefert **`{"ok":true,"service":"clh-provisioner"}`** (ohne Signatur) — z. B. für schnelle Checks hinter Nginx.
- [`clh-provisioner.service`](clh-provisioner.service) — Referenz-Unit (Bootstrap schreibt eine passende nach `/etc/systemd/system/`).
## Skript-Update nach Deploy

Nach Änderungen an den Tenant-Skripten im Repo (z. B. `clh-delete-tenant.sh`, `clh-suspend-tenant.sh`):

```bash
cd /pfad/zum/creator-link-hub   # Repo-Root
sudo bash vps/scripts/clh-cloud-host-update.sh
```

Das Skript kopiert die aktuellen Versionen nach `/usr/local/bin/` und lädt ggf. die Maintenance-Seite nach `/var/www/clh-suspended/`.

## Tenant manuell entfernen (Cleanup)

Wenn ein Tenant auf dem VPS hängen geblieben ist (z. B. nach fehlgeschlagenem Delete-Job), **SLUG** und Domain anpassen:

```bash
sudo /usr/local/bin/clh-delete-tenant.sh \
  --slug SLUG --domain SLUG.app.creatorlinkhub.eu \
  --tenant-root /var/www/clh-tenants --db-driver mysql
sudo nginx -t && sudo systemctl reload nginx
```

Das Delete-Skript entfernt u. a. `sites-available/enabled/clh-SLUG.conf`, **`sites-enabled/clh-SLUG-suspended.conf`**, MariaDB `clh_SLUG`, Dateien unter `/var/www/clh-tenants/SLUG/` und versucht optional `certbot delete` für die Domain.

## Security (Marketing-Server / KAS)

Production: `admin.allowed_ips`, `php bin/check-config.php`, Cron nur mit Header `X-CLH-Cron-Token`. Siehe [`marketing/README.md`](../../../marketing/README.md).

## Telegram-Benachrichtigungen (Marketing-Server)

Interne Hinweise bei **E-Mail-Verifizierung** (Registrierung) und **Stripe-Abo** (`customer.subscription.created`, `invoice.payment_succeeded`). Versand pseudonymisiert (Lizenz-ID, maskierte E-Mail `j***@domain.tld`, Plan, Slug) — **keine** vollständigen E-Mails, Namen, IPs oder Zahlungsdetails.

### Einrichtung

1. Bot bei [@BotFather](https://t.me/BotFather) anlegen, Token kopieren.
2. **Privaten** Channel/Gruppe anlegen, Bot als Mitglied + **Admin** (für spätere manuelle Löschung).
3. Chat-ID ermitteln (`getUpdates` nach Testnachricht an den Bot).
4. In `config/config.php` auf dem KAS-Server (nicht ins Repo):

```php
'telegram' => [
    'enabled' => true,
    'bot_token' => '…',
    'chat_id' => '-100…',
],
```

5. Migration: `php bin/migrate.php` (Tabelle `notification_jobs`).
6. Cron wie bisher — verarbeitet `notification_jobs` mit Zählern `telegram_sent=…`.

### DSGVO / Retention

- Pseudonymisierung reicht; kein separates EU-Opt-in im MVP.
- **Keine automatische Löschung** in Telegram — Channel manuell alle **90 Tage** prüfen und alte Meldungen entfernen (Policy).
- Ausgehende HTTPS zu `api.telegram.org` muss vom KAS erlaubt sein (wie Stripe-API).

## Sicherheit

- Geteiltes Geheimnis nur auf beiden Seiten; **TLS** für `provisioner.url` Pflicht.
- Request-Body enthält `ts` und `nonce` (Anti-Replay, ±5 Min, Nonce-Cache unter `/var/lib/clh-provisioner/nonces`).
- Keine Shell-Injektion: Slug strikt `[a-z0-9-]` (Marketing-Server + Skripte).
- `sudoers`: User `clh-provisioner` darf **nur** die Tenant-Skripte (`provision`, `delete`, `suspend`, `resume`, **`tenant-custom-domain`**) unter `/usr/local/bin/` ohne Passwort ausführen.
