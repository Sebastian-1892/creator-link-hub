# Server-Update nach GitHub-Release (Cloud App-Host)

How-to für **Betreiber** eines Multi-Tenant-**App-VPS**: Nach einem Push/Release im Repo **`creator-link-hub`** den Host aktualisieren, damit **Provisioner**, **Skripte** und optional das **Release-ZIP** für **neue** Kunden-Installationen zum aktuellen Stand passen — und damit ihr wisst, wie **bestehende** Tenant-Ordner anschließend auf dieselbe Version gehoben werden können.

> Es gibt **zwei getrennte Ebenen**: (1) **VPS-Host** = Git-Klon + Provisioner + `/opt/clh-releases/current.zip`. (2) **Je Kunde** = eigene Laravel-Installation unter `/var/www/clh-tenants/<slug>/`. Ein `git pull` auf dem Host **ändert bestehende Tenant-Verzeichnisse nicht**.

---

## Voraussetzungen

| Voraussetzung | Typisch |
|---------------|---------|
| Repo auf dem VPS | Git-Clone des Repos (z. B. unter `/opt/creator-link-hub-src/creator-link-hub`), Remote **`origin`** zeigt auf GitHub |
| Konfiguration | `/etc/clh-provisioner/install-paths.env` mit **`CLH_REPO_ROOT`** (Pfad zum Klon) und optional **`CLH_GIT_REF`** (Branch oder Tag, Standard **`main`**) |
| Rechte | Update-Skript **als root** ([`scripts/clh-cloud-host-update.sh`](../../scripts/clh-cloud-host-update.sh) liegt nach Bootstrap als `/usr/local/bin/clh-cloud-host-update.sh`) |
| Privates GitHub-Repo | Deploy-Key oder Credential für den User, mit dem auf dem Server **`git fetch`/`git pull`** läuft (**root** hat oft keine GitHub-Credentials — dann Deploy-Key für den Kloneigentümer oder `sudo -u git-user …`) |

---

## Schritt 1 — Host nach GitHub-Release aktualisieren

**Nach jedem Release**, das auf dem VPS laufen soll (Branch `main` oder euer konfigurierter Ref):

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh
```

Das Skript ([Quelle im Repo](../../scripts/clh-cloud-host-update.sh)):

1. **`git fetch`** / **`git checkout`** des konfigurierten Refs / **`git pull --ff-only`**
2. Kopiert **`deployment/cloud-host/provisioner.php`** und **`router.php`** nach **`/opt/clh-provisioner/`**
3. Installiert Tenant-Skripte nach **`/usr/local/bin/`** (`clh-provision-tenant.sh`, `clh-delete-tenant.sh`, …)
4. **`systemctl restart clh-provisioner`**, **`nginx -t`** und **`reload`**

**Optional — gleichzeitig neues Release-ZIP für *neue* Tenants:**

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip
```

Dafür müssen auf dem VPS **`npm`**, **`zip`** und die Build-Tools aus der Cloud-Doku verfügbar sein. Das Skript ruft intern **`scripts/build-cloud-release-zip.sh`** auf und legt die Datei nach **`/opt/clh-releases/current.zip`** (Quelle im Repo: Ausgabe unter `distribution/releases/current-cloud.zip`).

Ohne **`--with-zip`** bleibt **`current.zip`** unverändert — bestehende Neuanlagen über das Marketing nutzen dann weiter die alte ZIP-Basis, bis ihr das ZIP separat aktualisiert ([Schritt 9 in der Hauptanleitung](./README.md#schritt-9--nach-einem-release-zip-aktualisieren)).

---

## Schritt 2 — Was damit **nicht** automatisch passiert

| Bereich | Automatisch aktualisiert? |
|---------|---------------------------|
| Provisioner + Host-Skripte | Ja (Schritt 1) |
| **`/opt/clh-releases/current.zip`** | Nur mit **`--with-zip`** oder manuellem Kopieren einer neuen ZIP |
| **`/var/www/clh-tenants/<slug>/`** (bestehende Kunden) | **Nein** — explizite Rollout-Policy nötig |

Der Kommentar am Ende von `clh-cloud-host-update.sh` ist die verbindliche Kurzfassung: *„Bestehende Tenants: weiterhin einzeln aktualisieren.“*

---

## Schritt 3 — Bestehende Kunden-Installationen auf die neue Version bringen

Kunden arbeiten in **ihrer** Instanz unter **`/var/www/clh-tenants/<slug>/`**. Dort liegt **kein** automatisches `git pull` vom Host-Klon.

**Übliches Vorgehen (manuell oder mit eigenem Skript):**

1. **Neue App-Dateien** in den Tenant-Ordner bringen — gleicher Inhalt wie im Release (entpacktes Release-ZIP oder **`rsync`** vom Host-Klon unter Ausschluss von **`.env`**, **`storage/`** und **`bootstrap/cache`** nach Bedarf).  
2. **Besitzer:** nach Deploy **`chown -R www-data:www-data`** auf das Tenantroot (siehe [Cloud-Hosting README — Rechte & Dashboard-Update](./README.md#anwendungs-update-im-dashboard-composer--update-applicationsh)).  
3. **Abhängigkeiten & Migrationen** ausführen — im **Filament-Admin** („Abhängigkeiten & Migrationen“) oder per SSH:
   ```bash
   cd /var/www/clh-tenants/SLUG
   sudo -u www-data bash scripts/update-application.sh
   ```
   Das Skript [**ohne Git**](../../scripts/update-application.sh): `composer install`, `npm ci`/`npm run build`, `migrate`, Caches. Es lädt **nichts** von GitHub — es arbeitet nur mit den Dateien, die **bereits** im Ordner liegen.

**Hinweise:**

- **`NPM_CONFIG_CACHE`** kann unter **`storage/npm-cache`** liegen ([`update-application.sh`](../../scripts/update-application.sh)); globales **`/var/www/.npm`** mit falschem Besitzer verursacht **`EACCES`** — siehe Fehlerabschnitte in der [Cloud-Hosting README](./README.md).
- Viele Tenants → wiederholendes Deploy + Update pro Slug oder eigenes Orchestrierungs-Skript (außerhalb dieses Repos).

---

## Kurz-Checkliste für ein Release

1. **GitHub:** Tag/Release erstellen, **`main`** (oder Release-Branch) enthält den gewünschten Stand.
2. **App-VPS:** `sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip` (oder ohne ZIP, wenn ihr **`current.zip`** anders pflegt).
3. **Provisioner/Marketing:** bei Änderungen an **`provisioner.php`** ist Schritt 2 ausreichend; Marketing-URL/HMAC unverändert lassen, sofern ihr nichts am Vertrag ändert.
4. **Bestehende Tenants:** Rollout planen — Dateien + `update-application.sh` pro Instanz (oder zeitversetzt).
5. Optional **Manifest / „installierte Version“:** [`config/creator.php`](../../config/creator.php) **`CLH_INSTALLED_VERSION`** bzw. **`CLH_UPDATE_MANIFEST_URL`** für das Dashboard-Widget anpassen, damit Kunden im Admin eine konsistente Versionsanzeige haben.

---

## Verwandte Dokumentation

| Dokument | Inhalt |
|----------|--------|
| [README.md — Cloud-Multi-Tenant](../../README.md#cloud-multi-tenant-marketing-server--app-vps) | Schnelleinstieg Installer |
| [Cloud-Hosting README](README.md) | Gesamtinstallation, DNS, Provisioner, ZIP |
| [vps/README.md](../../vps/README.md) | Pfade und Komponenten auf dem VPS |
| [deployment.md](../deployment.md) | `update-application.sh` im Überblick |

---

Bei Änderungen an Skripten gelten die **Kommentarköpfe** in `scripts/*.sh` im Repository als technische Kurzreferenz.
