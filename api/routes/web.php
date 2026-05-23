<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\HomepageProductController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\StoreSettingsController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\EmailOtpVerificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderReturnController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
        Route::get('/verify-otp', [AdminAuthController::class, 'showVerifyOtp'])->name('verify-otp.form');
        Route::post('/verify-otp', [AdminAuthController::class, 'verifyOtp'])->name('verify-otp.attempt');
        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('forgot-password.form');
        Route::post('/forgot-password', [AdminAuthController::class, 'sendForgotPasswordOtp'])->name('forgot-password.send');
        Route::get('/reset-password', [AdminAuthController::class, 'showResetPassword'])->name('reset-password.form');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('reset-password.attempt');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::put('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.update-tracking');
        Route::post('/orders/{order}/tracking-logs', [OrderController::class, 'addTrackingLog'])->name('orders.add-tracking-log');
        Route::get('/returns', [OrderReturnController::class, 'index'])->name('returns.index');
        Route::get('/returns/{return}', [OrderReturnController::class, 'show'])->name('returns.show');
        Route::put('/returns/{return}', [OrderReturnController::class, 'update'])->name('returns.update');

        Route::get('/settings', [StoreSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/store', [StoreSettingsController::class, 'updateStore'])->name('settings.store.update');
        Route::put('/settings/gateways/{gateway}', [StoreSettingsController::class, 'updateGateway'])->name('settings.gateway.update');
        Route::put('/settings/delivery-partners/{partner}', [StoreSettingsController::class, 'updateDeliveryPartner'])->name('settings.delivery.update');
        Route::get('/email-otp-verification', [EmailOtpVerificationController::class, 'edit'])->name('email-otp.edit');
        Route::put('/email-otp-verification/email', [EmailOtpVerificationController::class, 'updateEmail'])->name('email-otp.email.update');
        Route::put('/email-otp-verification/verification', [EmailOtpVerificationController::class, 'updateVerification'])->name('email-otp.verification.update');
        Route::put('/email-otp-verification/providers/{provider}', [EmailOtpVerificationController::class, 'updateProvider'])->name('email-otp.provider.update');

        Route::get('/homepage-sections', [HomepageSectionController::class, 'index'])->name('homepage-sections.index');
        Route::get('/homepage-sections/hero/editor', [HomepageSectionController::class, 'editHero'])->name('homepage-sections.hero.edit');
        Route::put('/homepage-sections/hero/editor', [HomepageSectionController::class, 'updateHero'])->name('homepage-sections.hero.update');
        Route::get('/homepage-sections/{homepageSection}/edit', [HomepageSectionController::class, 'edit'])->name('homepage-sections.edit');
        Route::put('/homepage-sections/{homepageSection}', [HomepageSectionController::class, 'update'])->name('homepage-sections.update');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('/homepage-products', [HomepageProductController::class, 'index'])->name('homepage-products.index');
        Route::put('/homepage-products/{sectionKey}', [HomepageProductController::class, 'update'])->name('homepage-products.update');

        Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
        Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
        Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');

        Route::get('/social-links', [SocialLinkController::class, 'index'])->name('social-links.index');
        Route::post('/social-links', [SocialLinkController::class, 'store'])->name('social-links.store');
        Route::put('/social-links/{socialLink}', [SocialLinkController::class, 'update'])->name('social-links.update');
        Route::delete('/social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('social-links.destroy');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
