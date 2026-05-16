# Creator Link Hub — Dokumentation

Zentraler **Wiki.js-Einstieg**: Diese Seite verweist auf alle Unterdokumente. Pfade sind relativ zur Datei `docs/README.md` im Repository — beim Import in wiki.js Links ggf. auf eure URL-Struktur anpassen.

Creator Link Hub wird **ausschließlich als Cloud-Multi-Tenant-Lösung** betrieben (ein App-VPS, viele Tenants unter `/var/www/clh-tenants/<slug>/`).

---

## 1. Überblick & Produkt

| Dokument | Inhalt |
|----------|--------|
| [Projektüberblick (Repo, Stack, lokale Entwicklung)](overview.md) | Funktionen MVP, `composer`/`npm`, Tests, CI, Umgebungsvariablen |
| [Produktvision, Zielgruppe, Roadmap-Ideen](product-plan.md) | Ausführliche Produkt-/Geschäftsplanung (nicht identisch mit implementiertem MVP) |

---

## 2. Installation & Serverbetrieb

| Dokument | Inhalt |
|----------|--------|
| [Cloud-Hosting (Multi-Tenant App-VPS)](cloud-hosting-installation/README.md) | Bootstrap, DNS, Provisioner, Tenants, SMTP/Stripe im Admin, Troubleshooting |
| [Server-Update nach GitHub (Host + Tenants)](cloud-hosting-installation/server-update-nach-github.md) | `clh-cloud-host-update`, `clh-rollout-all-tenants`, Rollout-Policy |
| [VPS-Komponenten & Pfade (App-Server)](vps-components.md) | Provisioner, systemd, Nginx, Skripte unter `/usr/local/bin/` |
| [Deployment & Updates (allgemein)](deployment.md) | Cloud-Pfad, `update-application.sh`, Stripe, Backups |
| [Launch-Runbook](launch-runbook.md) | Go-Live-Checkliste |

---

## 3. Entwicklungsplan im Repo (nicht alles umgesetzt)

| Pfad | Hinweis |
|------|---------|
| [`plan/steps/`](../plan/steps/) | Iterative Feature-Schritte, teils Architektur |

Die ausführliche **Vision** steht unter [product-plan.md](product-plan.md); die **Umsetzung** folgt den Schritten unter `plan/steps/` und dem Code.

---

## 4. Schnellreferenz Repository

| Bereich | Ort im Repo |
|---------|-------------|
| Cloud-Bootstrap / Installer | `scripts/bootstrap-cloud-host.sh`, `scripts/install-cloud-host-interactive.sh` |
| Host-Update | `scripts/clh-cloud-host-update.sh` → `/usr/local/bin/` |
| Rollout alle Tenants | `scripts/clh-rollout-all-tenants.sh` |
| Tenant-Update (ohne Git) | `scripts/update-application.sh` |
| Provisioner (HTTP) | `deployment/cloud-host/router.php`, `provisioner.php` |
| Release-ZIP bauen | `scripts/build-cloud-release-zip.sh` → `distribution/releases/current-cloud.zip` |
| CI | `.github/workflows/ci.yml` |
| Konfiguration Beispiel | `.env.example` |
