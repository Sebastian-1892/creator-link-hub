# Creator Link Hub — öffentlicher Update-Kanal (pCloud)

Öffentliche Basis-URL: **https://filedn.eu/lFa08iL0cJzHeyFFtNiVfqY/creator-link-hub/update/**

Lokaler Spiegel (pCloud Drive): **`Public Folder/creator-link-hub/update`** (Inhalt dieses Ordners dorthin kopieren bzw. synchron halten).

Nur statische Dateien (gleiches Muster wie beim Projekt **software.rechnungsschmiede.eu** unter `update/README.md`): **`versions.json`**, Changelogs (`.md`), ZIPs unter **`downloads/`**. Kein PHP auf dem Host nötig.

## Struktur

| Pfad | Zweck |
|------|--------|
| `versions.json` | `latest_version`, `min_php_version`, Liste `versions[]` mit `download_url`, `changelog_url`, `signature` (SHA-256, optional leer) |
| `downloads/` | `creator-link-hub-<version>.zip` |
| `changelogs/` | `0.1.0.md` usw. |
| `roadmap/README.md` | Sammelliste bis zum nächsten Release |
| `update_versions_json.sh` | Helfer zum Eintragen einer neuen Version |

## Release-Ablauf (Kurz)

1. Roadmap / Änderungen in `changelogs/<version>.md` festhalten  
2. ZIP bauen (ohne `.git`, ohne `node_modules`, ohne `vendor` — je nach Release-Policy) nach `downloads/creator-link-hub-<version>.zip`  
3. `sha256sum downloads/creator-link-hub-<version>.zip`  
4. `./update_versions_json.sh <version> <sha256>`  
5. **Reihenfolge Upload (pCloud / filedn):** zuerst ZIP + Changelog, **zuletzt** `versions.json`  
6. In der **betriebenen** App `CLH_INSTALLED_VERSION` in `.env` bzw. `config/creator.php` nach Deploy anheben

Die Laravel-App lädt **`CLH_UPDATE_MANIFEST_URL`** (Standard: …/update/versions.json) und vergleicht mit **`CLH_INSTALLED_VERSION`**.
