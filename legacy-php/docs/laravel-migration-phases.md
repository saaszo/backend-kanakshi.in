# Laravel Migration Phases

## Goal

Replace the current custom PHP backend with a Laravel backend in controlled phases while keeping the existing storefront usable during migration.

## Working Strategy

- Keep the current HTML/CSS/JS storefront first.
- Move backend responsibilities into Laravel incrementally.
- Replace legacy `ajax/*.php` endpoints with Laravel JSON routes.
- Migrate high-risk areas first: auth, admin security, schema consistency, cart, checkout, payments.

## Phase 0: Foundation Audit and Freeze

### Objectives

- Stop schema drift between runtime code and database.
- Inventory every current module and route.
- Decide which legacy pages stay temporarily and which move first.

### Deliverables

- Laravel migration plan
- Current module inventory
- Database mismatch list
- Legacy endpoint replacement list

### Success Criteria

- We can state exactly what exists today.
- We have a safe migration order.

## Phase 1: Laravel Backend Workspace and Core Architecture

### Objectives

- Create a clean Laravel app beside the legacy project.
- Configure environment, database connection, and app structure.
- Define bounded contexts for the migration.

### Deliverables

- New Laravel project in `backend-laravel/`
- Base app config
- Initial route groups:
  - `web.php`
  - `api.php`
- Initial modules:
  - Auth
  - Catalog
  - Cart
  - Checkout
  - Orders
  - Admin
  - Settings

### Success Criteria

- Laravel boots locally.
- We have a place to move features without touching legacy files yet.

## Phase 2: Database Alignment and Laravel Migrations

### Objectives

- Convert the current database into reliable Laravel migrations.
- Resolve schema mismatches before business logic moves.

### Priority Tables

- `users`
- `products`
- `product_variants`
- `categories`
- `cart`
- `orders`
- `order_items`
- `coupons`
- `settings`
- `pages`
- `notifications`
- `subscribers`
- `abandoned_carts`

### Known Issues To Fix

- Auth code expects columns not guaranteed by `database.sql`
- Product admin writes columns missing from base schema
- Installer writes settings keys different from runtime settings keys
- Payment and stock flow depend on inconsistent order schema assumptions

### Success Criteria

- Fresh database can be built from Laravel migrations only.
- Legacy and Laravel code can read the same core tables during transition.

## Phase 3: Authentication and Admin Security

### Objectives

- Replace legacy login, session, and admin guard logic with Laravel auth and middleware.
- Remove duplicate auth logic in the legacy app over time.

### Scope

- Customer login/register/forgot password/reset password
- Admin login and role middleware
- Session hardening
- CSRF and validation normalization

### Success Criteria

- Auth flows are handled by Laravel.
- Admin access is middleware-based.
- No duplicate `requireAdmin()` logic remains in active use.

## Phase 4: Catalog and Content APIs

### Objectives

- Move product, category, search, page, and review logic to Laravel controllers/services.
- Keep existing storefront templates calling Laravel-backed APIs or routes.

### Scope

- Product listing
- Product detail
- Category navigation
- Search suggestions
- Dynamic pages
- Reviews

### Success Criteria

- Catalog reads are served from Laravel.
- Legacy helper-heavy SQL paths stop being the source of truth.

## Phase 5: AJAX Migration for Smooth Frontend Flow

### Objectives

- Replace all legacy AJAX PHP scripts with Laravel JSON endpoints.
- Standardize response shapes and frontend error handling.

### Endpoints To Replace First

- `ajax/cart.php`
- `ajax/update-cart.php`
- `ajax/remove-cart.php`
- `ajax/apply-coupon.php`
- `ajax/live-search.php`
- `ajax/get-variants.php`
- `ajax/add-to-wishlist.php`
- `ajax/submit-review.php`
- `ajax/subscribe.php`

### Success Criteria

- Existing frontend JS can call Laravel endpoints with the same or improved UX.
- JSON response contracts are consistent.

## Phase 6: Cart, Checkout, and Orders

### Objectives

- Rebuild cart and checkout logic in Laravel services.
- Fix the current stock-deduction-before-payment issue.

### Required Changes

- Centralized cart service
- Coupon service
- Order creation service
- Inventory reservation or post-payment stock deduction
- Order status transitions

### Success Criteria

- Cart totals come from one service.
- Online payments no longer permanently reduce stock before payment confirmation.

## Phase 7: Payment Gateway Hardening

### Objectives

- Move Razorpay, PhonePe, and future Paytm handling into Laravel.
- Add webhook-safe verification and idempotency.

### Scope

- Payment initiation
- Callback handlers
- Webhooks
- Transaction logging
- Failure recovery

### Success Criteria

- Payment flows are deterministic and auditable.
- Gateway status does not depend only on browser return flow.

## Phase 8: Admin Panel Migration

### Objectives

- Rebuild admin screens in Laravel gradually.
- Preserve current business workflows while improving code structure.

### Scope

- Dashboard
- Products
- Categories
- Orders
- Returns
- Coupons
- Banners
- Pages
- Subscribers
- Settings

### Success Criteria

- Admin no longer depends on scattered PHP pages with duplicated auth/includes.

## Phase 9: Background Jobs, Notifications, and Email

### Objectives

- Move cron-style tasks into Laravel jobs/commands.

### Scope

- Abandoned cart reminders
- Order emails
- Notifications
- Newsletter workflows

### Success Criteria

- Background behavior is queue/job ready and easier to monitor.

## Phase 10: Legacy Decommission

### Objectives

- Remove or archive old PHP entry points once their Laravel equivalents are live.

### Success Criteria

- Production backend runs on Laravel only.
- Legacy PHP files are no longer handling live business logic.

## Recommended Execution Order

1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 5
5. Phase 6
6. Phase 7
7. Phase 4
8. Phase 8
9. Phase 9
10. Phase 10

## Immediate Next Tasks

1. Scaffold Laravel backend workspace
2. Create module map from legacy files to Laravel domains
3. Create initial Laravel migrations for `users`, `settings`, `categories`, `products`
4. Implement Laravel auth and admin middleware
5. Replace the cart AJAX flow first
