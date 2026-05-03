# Step 01 – Foundation & Setup

## Ziel
Laravel-11-Projekt mit PostgreSQL, Redis, Pest, Pint, Larastan, GitHub Actions und dokumentierter `.env.example`.

## Checkliste (Implementierung)
- [ ] `composer create-project laravel/laravel .` oder gleichwertig im Repo-Root
- [ ] `composer require` Dev-Tools: pestphp/pest, pestphp/pest-plugin-laravel, laravel/pint, larastan/larastan
- [ ] PostgreSQL in `.env`, `config/database.php` prüfen
- [ ] Redis für `cache`, `queue`, `session` konfigurieren
- [ ] `.env.example` mit Platzhaltern für DB, Redis, Stripe, Mail, S3
- [ ] `README.md` mit lokalen Setup-Schritten
- [ ] GitHub Actions Workflow: `composer install`, `npm ci`, `npm run build`, `php artisan test`

## Abnahme
- `php artisan about` läuft
- `php artisan test` (grün, auch wenn nur Beispieltests)
- CI-Workflow grün
