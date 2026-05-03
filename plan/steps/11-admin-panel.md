# Step 11 – Admin-Panel (Filament)

## Ziel
Filament 3 für User, Workspace, Profile, Subscription; Sperren; Activity/Audit.

## Checkliste
- [ ] `composer require filament/filament`
- [ ] `php artisan filament:install --panels=admin`
- [ ] Resources mit Policies (nur super_admin / admin role)
- [ ] Custom Actions: workspace sperren, user verifizieren erzwingen

## Abnahme
- Admin-URL nur für berechtigte User
