# Step 03 – Auth, Workspaces & Onboarding

## Ziel
Breeze (Livewire), E-Mail-Verifizierung, automatische Workspace-Erstellung, 3-Schritt-Onboarding nach erstem Login.

## Checkliste
- [ ] `composer require laravel/breeze --dev` + `php artisan breeze:install livewire`
- [ ] Event/Observer: bei `Registered` → Workspace + Default-Profile anlegen
- [ ] Onboarding Livewire-Komponente: Name/Kategorie/Ziel → Profil-Slug-Vorschlag
- [ ] Middleware: Onboarding abschließen bevor Dashboard

## Abnahme
- Registrierung → Workspace existiert → Onboarding → Dashboard
