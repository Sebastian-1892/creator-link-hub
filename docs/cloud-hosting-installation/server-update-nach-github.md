# Server-Update nach GitHub-Release (Cloud App-Host)

How-to für **Betreiber** eines Multi-Tenant-**App-VPS**: Nach einem Push/Release im Repo **`creator-link-hub`** den Host aktualisieren, damit **Provisioner**, **Skripte** und optional das **Release-ZIP** für **neue** Kunden-Installationen zum aktuellen Stand passen — und damit ihr wisst, wie **bestehende** Tenant-Ordner anschließend auf dieselbe Version gehoben werden können.

> Es gibt **zwei getrennte Ebenen**: (1) **VPS-Host** = Git-Klon + Provisioner + `/opt/clh-releases/current.zip`. (2) **Je Kunde** = eigene Laravel-Installation unter `/var/www/clh-tenants/<slug>/`. Ein `git pull` auf dem Host **ändert bestehende Tenant-Verzeichnisse nicht**.

---

## Konsole: VPS nach GitHub aktualisieren

Branch und Klon-Pfad für den Cloud-Host stehen in **`/etc/clh-provisioner/install-paths.env`** (`CLH_REPO_ROOT`, optional `CLH_GIT_REF`, Standard **`main`**). Bei **privatem GitHub-Repo** braucht der User, der `git pull` ausführt (oft **root** beim Update-Skript), einen **Deploy-Key** oder andere Credentials — sonst schlägt das Update fehl.

Per SSH auf dem VPS einloggen, dann:

**1. Nur Host** (Git-Klon, Provisioner, Nginx, Skripte unter `/usr/local/bin/` — **ohne** neues Release-ZIP):

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh
```

**2. Host inkl. neuem Release-ZIP** für **neue** Tenant-Neuanlagen (`npm`/`zip` auf dem Server erforderlich):

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip
```

**3. Host + alle bestehenden Tenants** auf den Repo-Stand bringen (rsync pro Slug, danach jeweils `update-application.sh`):

```bash
sudo /usr/local/bin/clh-rollout-all-tenants.sh --with-zip
```

Nur Tenants (Repo auf dem Host ist schon aktuell):

```bash
sudo /usr/local/bin/clh-rollout-all-tenants.sh --skip-host-update
```

**Hinweis:** Ohne Schritt 3 bleiben Ordner unter **`/var/www/clh-tenants/<slug>/`** auf der alten Version; nur der Host-Klon und der Provisioner sind dann neu.

---

## Voraussetzungen

| Voraussetzung | Typisch |
|---------------|---------|
| Repo auf dem VPS | Git-Clone des Repos (z. B. unter `/opt/creator-link-hub-src/creator-link-hub`), Remote **`origin`** zeigt auf GitHub |
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
3. **Abhängigkeiten & Migrationen** ausführen — im **Filament-Admin** („Anwendungs-Update“) oder per SSH:
   ```bash
   cd /var/www/clh-tenants/SLUG
   sudo -u www-data bash scripts/update-application.sh
   ```
   Das Skript [**ohne Git**](../../scripts/update-application.sh): `composer install`, `npm ci`/`npm run build`, `migrate`, Caches. Es lädt **nichts** von GitHub — es arbeitet nur mit den Dateien, die **bereits** im Ordner liegen.

**Hinweise:**

- **`NPM_CONFIG_CACHE`** kann unter **`storage/npm-cache`** liegen ([`update-application.sh`](../../scripts/update-application.sh)); globales **`/var/www/.npm`** mit falschem Besitzer verursacht **`EACCES`** — siehe Fehlerabschnitte in der [Cloud-Hosting README](./README.md).
- Viele Tenants → wiederholendes Deploy + Update pro Slug oder eigenes Orchestrierungs-Skript (außerhalb dieses Repos).

### Alle Slugs in einem Lauf (Host + Tenants)

Nach dem nächsten **`clh-cloud-host-update`** liegt auf dem VPS **[`scripts/clh-rollout-all-tenants.sh`](../../scripts/clh-rollout-all-tenants.sh)** als **`/usr/local/bin/clh-rollout-all-tenants.sh`**. Ein Aufruf **als root**:

1. Führt **`clh-cloud-host-update.sh`** aus (**`git pull`**, Provisioner, Tenant-Skripte, optional **`--with-zip`**).
2. Liest **`tenants_root`** aus **`/etc/clh-provisioner/config.json`** (Fallback **`/var/www/clh-tenants`**).
3. Für **jedes** Unterverzeichnis mit **`composer.json`** und **`artisan`**: **`rsync`** vom **`CLH_REPO_ROOT`** (ohne **`.env`**, **`storage/`**, **`bootstrap/cache/`**), **`chown www-data`**, dann die **Tenant-Kopie** **`bash /var/www/clh-tenants/<slug>/scripts/update-application.sh`** (nicht die unter **`CLH_REPO_ROOT`** — das Skript leitet **`ROOT`** aus **`BASH_SOURCE`** ab).

```bash
sudo /usr/local/bin/clh-rollout-all-tenants.sh
sudo /usr/local/bin/clh-rollout-all-tenants.sh --skip-host-update   # nur Tenants, Repo schon aktuell
sudo /usr/local/bin/clh-rollout-all-tenants.sh --with-zip            # inkl. Release-ZIP wie beim Host-Update
```

Verzeichnisse ohne Laravel (kein **`composer.json`/`artisan`**) werden übersprungen.

---

## Kurz-Checkliste für ein Release

1. **GitHub:** Tag/Release erstellen, **`main`** (oder Release-Branch) enthält den gewünschten Stand.
2. **App-VPS:** `sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip` (oder ohne ZIP, wenn ihr **`current.zip`** anders pflegt).
3. **Provisioner/Marketing:** bei Änderungen an **`provisioner.php`** ist Schritt 2 ausreichend; Marketing-URL/HMAC unverändert lassen, sofern ihr nichts am Vertrag ändert.
4. **Bestehende Tenants:** Rollout planen — Dateien + `update-application.sh` pro Instanz (oder zeitversetzt).

---

## Verwandte Dokumentation

| Dokument | Inhalt |
|----------|--------|
| [README.md — Cloud-Index](../../README.md) | Schnelleinstieg Installer |
| [Cloud-Hosting README](README.md) | Gesamtinstallation, DNS, Provisioner, ZIP |
| [VPS-Komponenten](../vps-components.md) | Pfade und Komponenten auf dem VPS |
| [deployment.md](../deployment.md) | `update-application.sh` im Überblick |

---

Bei Änderungen an Skripten gelten die **Kommentarköpfe** in `scripts/*.sh` im Repository als technische Kurzreferenz.
