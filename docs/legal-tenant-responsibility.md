# Rechtliche Verantwortung — Mandanten (Tenant-App)

## Rollen

| Partei | Rolle | Pflicht |
|--------|-------|---------|
| **Creator Link Hub (CLH)** | Plattformbetreiber | Marketing-Site, Konto, Lizenz, Cloud-Bereitstellung |
| **Creator (Kunde)** | In der Regel Verantwortlicher | Endnutzer der Bio-/Link-Seite |
| **CLH** | Oft Auftragsverarbeiter | Technische Verarbeitung auf VPS für den Creator |

## Pflichten des Creators

Im Admin unter **Branding → Rechtliches** ausfüllen:

- Impressum (`legal.impressum_html`)
- Datenschutz (`legal.datenschutz_html`)
- AGB (`legal.agb_html`)

Diese Texte gelten für **Besucher der Creator-Domain**, nicht für creatorlinkhub.eu.

## Cookie-Banner (Tenant)

Das Cookie-Banner in der Tenant-App (`resources/views/components/cookie-banner.blade.php`) informiert über technisch notwendige Cookies und verlinkt auf `/legal/datenschutz`. Texte in Branding konfigurierbar (`bio.cookie_text`).

- Kein Tracking-Cookie im Standard-MVP
- Bei künftigen Analytics: Einwilligung und Aktualisierung der Mandanten-Datenschutzerklärung erforderlich

## Datenfluss

```
Endnutzer → Creator-Domain (VPS) → ggf. Logs auf VPS
Marketing-Konto des Creators → creatorlinkhub.eu (separate Verantwortlichkeit CLH)
```

## Support

Fragen zu Mandanten-Datenschutz: Creator wendet sich an support@creatorlinkhub.eu; CLH stellt keine Rechtsberatung.
