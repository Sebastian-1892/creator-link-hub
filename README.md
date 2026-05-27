# Creator Link Hub

Link-in-Bio-SaaS (Laravel 11, Livewire/Volt, Filament, Stripe) — **Cloud-Multi-Tenant only**.

## Dokumentation

**Zentrale Einstiegsseite (für wiki.js & Leser:innen):** **[`docs/README.md`](docs/README.md)**

Dort: Inhaltsverzeichnis mit Verweisen auf Cloud-Hosting-Installation, VPS-Komponenten, Deployment, Updates und Produktvision.

- Kurzüberblick Entwickler:innen: [`docs/overview.md`](docs/overview.md)
- Cloud-Multi-Tenant einrichten: [`docs/cloud-hosting-installation/README.md`](docs/cloud-hosting-installation/README.md)
- Hub-Sprachen (DE/EN) Rollout: [`../claude_docs/vps/hub-i18n-rollout.md`](../claude_docs/vps/hub-i18n-rollout.md)
- Root dieses Repos: Anwendungscode, `scripts/`, `plan/`, `distribution/releases/`

## Übersetzungen (Hub)

- `lang/en.json` — englische UI (Keys = deutsche Default-Strings).
- `lang/de.json` — deutsche UI für ehemals englische Breeze-Strings.
- Prüfung: `php artisan translations:missing` (auch in CI).
