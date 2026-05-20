<?php

use App\Http\Controllers\Api\Catalog\CategoryIndexController;
use App\Http\Controllers\Api\Catalog\ProductIndexController;
use App\Http\Controllers\Api\Catalog\ProductShowController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Settings\PublicHomepageSectionsController;
use App\Http\Controllers\Api\Settings\PublicSettingsController;
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
});
