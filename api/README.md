# Backend Laravel Workspace

This directory is the new Laravel backend workspace for the migration away from the legacy custom PHP backend.

## Current Status

- Laravel 12 scaffold created
- API route file enabled
- Phase 1 foundation started
- Initial API endpoints added:
  - `/api/v1/health`
  - `/api/v1/catalog/products`
  - `/api/v1/settings/public`

## Migration Rules

- Do not rewrite the whole storefront at once.
- Keep the current frontend alive while moving backend logic gradually.
- Migrate auth, cart, checkout, and payments before decommissioning legacy PHP endpoints.

## Recommended Local Setup

The legacy app currently uses:

- Host: `localhost`
- Database: `luxury_store`
- Username: `root`
- Password: empty

Do not point Laravel at the live legacy database for destructive migrations until the migration set is finalized.

## Production Scheduler

Configure the hosting cron service to run Laravel's scheduler every minute:

`cd /path/to/laravel_app && php artisan schedule:run`

The scheduler publishes due blog posts and releases stock/coupon reservations
from online-payment orders that remain pending past their expiry window.

The archived legacy PHP mailer no longer contains fallback credentials. If it
must remain operational, configure `LEGACY_MASTER_SMTP_HOST`,
`LEGACY_MASTER_SMTP_USER`, `LEGACY_MASTER_SMTP_PASSWORD`,
`LEGACY_MASTER_SMTP_PORT`, and `LEGACY_MASTER_SMTP_ENCRYPTION` in its runtime
environment.

## Immediate Next Steps

1. Add Laravel migrations for legacy core tables
2. Implement Laravel auth and admin middleware
3. Replace cart AJAX endpoints with Laravel JSON controllers
4. Move checkout and order placement into service classes

## Phase 2 Progress

- Core legacy-aligned migrations added for:
  - `users`
  - `settings`
  - `categories`
  - `products`
  - `product_variants`
  - `cart`
  - `password_resets`
- Starter settings seeder added
- `.env.example` now includes the legacy MySQL connection values as comments
