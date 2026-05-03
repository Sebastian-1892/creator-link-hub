# Step 06 – Public Profile Page

## Ziel
`/{slug}` Blade SSR, mobile-first, OpenGraph, `robots.txt`, `sitemap.xml`, Redis-Cache mit Invalidierung bei Profil-Update.

## Checkliste
- [ ] Route: ein Segment Slug (nicht mit App-Routen kollidieren)
- [ ] Cache-Key `profile:{slug}`, Tags bei Update löschen
- [ ] 404 wenn nicht veröffentlicht

## Abnahme
- Öffentliche URL lädt <200ms lokal, OG-Tags gesetzt
