# Legacy To Laravel Module Map

## Legacy Architecture Summary

The current app mixes controller logic, SQL, session handling, HTML rendering, and AJAX handling directly inside page files. Laravel should separate these into routes, controllers, services, requests, middleware, models, jobs, and policies.

## Module Map

### Auth

#### Legacy Files

- `login.php`
- `register.php`
- `forgot-password.php`
- `reset-password.php`
- `account.php`
- `includes/auth.php`
- `admin/login.php`
- `admin/includes/auth.php`

#### Laravel Target

- Models:
  - `User`
  - `PasswordResetToken` or Laravel-native reset storage
- Controllers:
  - `Auth/LoginController`
  - `Auth/RegisterController`
  - `Auth/ForgotPasswordController`
  - `Admin/AuthController`
- Middleware:
  - `auth`
  - `guest`
  - `admin`

### Catalog

#### Legacy Files

- `index.php`
- `products.php`
- `product.php`
- `search.php`
- `includes/home/home-marketplace.php`
- `config/functions.php`

#### Laravel Target

- Models:
  - `Product`
  - `Category`
  - `ProductVariant`
  - `ProductReview`
- Controllers:
  - `Store/HomeController`
  - `Store/ProductController`
  - `Store/SearchController`
- Services:
  - `Catalog/ProductQueryService`

### Cart

#### Legacy Files

- `cart.php`
- `ajax/cart.php`
- `ajax/update-cart.php`
- `ajax/remove-cart.php`
- `ajax/apply-coupon.php`

#### Laravel Target

- Models:
  - `CartItem`
  - `Coupon`
- Controllers:
  - `Api/CartController`
  - `Api/CouponController`
- Services:
  - `Cart/CartService`
  - `Cart/CartTotalsService`
  - `Cart/CouponService`

### Checkout and Orders

#### Legacy Files

- `checkout.php`
- `order-success.php`
- `my-orders.php`
- `order-details.php`
- `invoice.php`

#### Laravel Target

- Models:
  - `Order`
  - `OrderItem`
  - `OrderReturn`
  - `OrderTracking`
- Controllers:
  - `Checkout/CheckoutController`
  - `Account/OrderController`
- Services:
  - `Orders/OrderPlacementService`
  - `Orders/InventoryService`
  - `Orders/OrderStatusService`

### Payments

#### Legacy Files

- `payment/razorpay/initiate.php`
- `payment/razorpay/callback.php`
- `payment/phonepe/initiate.php`
- `payment/phonepe/callback.php`
- `payment/paytm/initiate.php`
- `payment/paytm/callback.php`

#### Laravel Target

- Controllers:
  - `Payments/RazorpayController`
  - `Payments/PhonePeController`
  - `Payments/PaytmController`
- Services:
  - `Payments/RazorpayService`
  - `Payments/PhonePeService`
  - `Payments/PaytmService`
  - `Payments/PaymentVerificationService`

### Admin

#### Legacy Files

- `admin/index.php`
- `admin/products/*`
- `admin/categories/*`
- `admin/orders/*`
- `admin/coupons/*`
- `admin/banners/*`
- `admin/pages/*`
- `admin/settings/index.php`
- `admin/subscribers/index.php`
- `admin/reviews/index.php`

#### Laravel Target

- Controllers:
  - `Admin/DashboardController`
  - `Admin/ProductController`
  - `Admin/CategoryController`
  - `Admin/OrderController`
  - `Admin/CouponController`
  - `Admin/BannerController`
  - `Admin/PageController`
  - `Admin/SettingsController`
  - `Admin/SubscriberController`
  - `Admin/ReviewController`

### Content and Static Pages

#### Legacy Files

- `page.php`
- `privacy-policy.php`
- `refund-policy.php`
- `terms-conditions.php`
- `about-us.php`
- `contact-us.php`
- `includes/templates/dynamic-page.php`

#### Laravel Target

- Controllers:
  - `Store/PageController`
  - `Store/ContactController`
- Models:
  - `Page`

### Wishlist and Reviews

#### Legacy Files

- `wishlist.php`
- `ajax/add-to-wishlist.php`
- `ajax/submit-review.php`

#### Laravel Target

- Models:
  - `WishlistItem`
  - `ProductReview`
- Controllers:
  - `Api/WishlistController`
  - `Api/ReviewController`

### Marketing and Background Work

#### Legacy Files

- `cron/abandoned-carts.php`
- `admin/marketing/*`
- `ajax/subscribe.php`

#### Laravel Target

- Models:
  - `Subscriber`
  - `AbandonedCart`
  - `Notification`
- Controllers:
  - `Api/SubscriberController`
  - `Admin/MarketingController`
- Console:
  - `artisan` commands
- Jobs:
  - Abandoned cart recovery
  - Marketing email dispatch

## Phase 1 Build Slice

The first Laravel implementation slice should cover:

1. Base app bootstrap
2. Database connection
3. Core models:
   - `User`
   - `Setting`
   - `Category`
   - `Product`
4. Auth scaffolding
5. Cart JSON endpoints

## Notes

- The existing frontend can stay temporarily and call Laravel APIs.
- We do not need to migrate every Blade view immediately.
- The fastest route to visible improvement is auth plus cart plus checkout backend logic.
