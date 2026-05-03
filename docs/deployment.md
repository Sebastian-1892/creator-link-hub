# Deployment (Kurz-Runbook)

## Server

- PHP 8.3 + Extensions: `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `redis`, `intl`, `bcmath`
- PostgreSQL 16, Redis 7
- Nginx → `public/index.php`
- Supervisor: `php artisan queue:work`
- Cron: `* * * * * php /path/artisan schedule:run`

## Nach dem Deploy

```bash
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

## Stripe

- Live-Keys in `.env`
- Webhook-Endpoint: `{APP_URL}/stripe/webhook` (Cashier-Standardpfad)
- `STRIPE_WEBHOOK_SECRET` setzen

## Backups

- Täglich PostgreSQL Dump auf Object Storage / Storage Box

## Monitoring

- Sentry DSN optional in `.env` (`SENTRY_LARAVEL_DSN`)
