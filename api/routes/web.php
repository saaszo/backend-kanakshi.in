<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\StoreSettingsController;
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
        Route::get('/settings', [StoreSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/store', [StoreSettingsController::class, 'updateStore'])->name('settings.store.update');
        Route::put('/settings/email', [StoreSettingsController::class, 'updateEmail'])->name('settings.email.update');
        Route::put('/settings/gateways/{gateway}', [StoreSettingsController::class, 'updateGateway'])->name('settings.gateway.update');
        Route::put('/settings/delivery-partners/{partner}', [StoreSettingsController::class, 'updateDeliveryPartner'])->name('settings.delivery.update');

        Route::get('/homepage-sections', [HomepageSectionController::class, 'index'])->name('homepage-sections.index');
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
