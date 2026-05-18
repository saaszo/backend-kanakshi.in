# Laravel Schema Decisions

## Why This Exists

The legacy `database.sql` is not fully aligned with the actual runtime code. Laravel migrations are being written to match the running application behavior first, while staying as close as possible to the legacy schema.

## Decisions Already Applied

### Users

Added runtime-required fields that the current PHP app expects but `database.sql` does not reliably provide:

- `email_verify_token`
- `login_attempts`
- `locked_until`
- `last_login`

### Products

Added fields that current admin product forms already write:

- `cost_price`
- `weight`
- `video_url`
- `custom_schema`
- `aplus_content`

### Shipping Type

The legacy schema uses values closer to `free` / `flat`, but the current admin code uses:

- `default`
- `free`
- `custom`

Laravel migrations follow the current application code so admin product forms and shipping logic can be normalized in later phases.

### Password Reset Storage

Laravel uses `password_reset_tokens`, while the legacy app uses `password_resets`.

For transition safety, both can exist during migration.

## Next Schema Targets

- `coupons`
- `orders`
- `order_items`
- `pages`
- `notifications`
- `subscribers`
- `abandoned_carts`
- `wishlists`
