<?php

use App\Http\Controllers\Api\Catalog\CategoryIndexController;
use App\Http\Controllers\Api\Catalog\ProductIndexController;
use App\Http\Controllers\Api\Catalog\ProductShowController;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Marketing\PublicCouponsController;
use App\Http\Controllers\Api\Settings\PublicHomepageSectionsController;
use App\Http\Controllers\Api\Settings\PublicSettingsController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::prefix('catalog')->group(function (): void {
        Route::get('/categories', CategoryIndexController::class);
        Route::get('/products', ProductIndexController::class);
        Route::get('/products/{slug}', ProductShowController::class);
    });

    Route::prefix('settings')->group(function (): void {
        Route::get('/public', PublicSettingsController::class);
        Route::get('/homepage-sections', PublicHomepageSectionsController::class);
    });

    Route::prefix('marketing')->group(function (): void {
        Route::get('/coupons', PublicCouponsController::class);
    });

    Route::prefix('customer/auth')->group(function (): void {
        Route::get('/config', [CustomerAuthController::class, 'config']);
        Route::post('/register', [CustomerAuthController::class, 'register']);
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::get('/me', [CustomerAuthController::class, 'me']);
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::post('/verify-email-otp', [CustomerAuthController::class, 'verifyEmailOtp']);
        Route::post('/resend-verification-otp', [CustomerAuthController::class, 'resendVerificationOtp']);
        Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword']);
    });

    // Orders, Tracking and Checkout Routes
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::post('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment']);
    Route::post('/checkout/cancel-order', [CheckoutController::class, 'cancelOrder']);
    Route::get('/orders/track', [OrderTrackingController::class, 'track']);
    Route::get('/customer/orders', [CustomerOrderController::class, 'index']);
    Route::get('/customer/orders/{order_number}', [CustomerOrderController::class, 'show']);
});

