# Copy-Audit — VPS (Tenant-App)

Stand: 2026-05-26

## Umfang

- `lang/` — App- und Branding-Strings
- `resources/views/` — Blade (öffentliche Bio-/Pricing-Views)
- **Nicht:** `vendor/`, `storage/framework/views`, Filament-Admin (intern)

## Stichprobe

- Leeres Branding: Defaults aus `BrandingService` + `lang/de/branding.php`
- Creator-Impressum/Datenschutz: mandantenseitig in Filament → [`legal-tenant-responsibility.md`](legal-tenant-responsibility.md)

## Scan

```bash
cd vps && php bin/audit-copy.php
```

Bei Bedarf `--fail-on-findings` in CI ergänzen (nach Marketing P1).

## Ergebnis (initial)

Keine `TODO`/`lorem`/Jane-Doe-Treffer in `lang/` und `resources/views` (Stand Erstscan).
