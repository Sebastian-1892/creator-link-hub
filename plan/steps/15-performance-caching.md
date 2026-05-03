# Step 15 – Performance & Caching

## Ziel
Indexe, Redis Cache Tags für Public Profile, Queue für Clicks, Lazy Images.

## Checkliste
- [ ] Migrationen: index auf `profiles.slug`, `links.profile_id`, `click_events.created_at`
- [ ] `CACHE_STORE=redis` optional fallback file
- [ ] Supervisor-Konfiguration dokumentiert (Step 16)

## Abnahme
- Explain-Analyse zeigt Index-Nutzung für Slug-Lookup
