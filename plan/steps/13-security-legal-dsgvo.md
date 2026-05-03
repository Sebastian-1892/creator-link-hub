# Step 13 – Sicherheit, Legal & DSGVO

## Ziel
Rate-Limits, keine offenen Redirects, IP-Hashing, Cookie-Banner-Stub, statische Seiten Impressum/Datenschutz/AGB, AVV-PDFs verlinken.

## Checkliste
- [ ] `RateLimiter` für `login`, `register`, `go`
- [ ] Legal Blade views mit Platzhaltern für Firmendaten
- [ ] AVV: Links zu Stripe/Postmark Subprocessors

## Abnahme
- Brute-Force gedrosselt; Legal-Seiten 200
