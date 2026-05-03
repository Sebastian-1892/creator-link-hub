# Step 09 – Plan-Limits & RBAC

## Ziel
Free: max. 10 Links, Branding; Paid: Branding aus, unlimited links. spatie/permission: admin, user.

## Checkliste
- [ ] `composer require spatie/laravel-permission`
- [ ] Policy `LinkPolicy`, `ProfilePolicy`
- [ ] `PlanService` oder Gates: `canAddLink`, `hasPlatformBranding`
- [ ] Filament: Rolle `super_admin` für Admin-User

## Abnahme
- Free-User kann 11. Link nicht speichern; Paid schon
