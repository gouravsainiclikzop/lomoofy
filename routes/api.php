<?php

use App\Http\Controllers\Api\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Panel API - Themes (token protected)
|--------------------------------------------------------------------------
*/
Route::middleware('api.token')->prefix('themes')->group(function () {
    Route::post('/', [ThemeController::class, 'store']);
    Route::get('/', [ThemeController::class, 'index']);
    Route::get('/{id}', [ThemeController::class, 'show']);
    Route::put('/{id}', [ThemeController::class, 'update']);
    Route::delete('/{id}', [ThemeController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Public Theme Listing (no auth)
|--------------------------------------------------------------------------
*/
Route::get('/public/themes', [ThemeController::class, 'publicIndex']);
