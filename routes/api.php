<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\SectionsApiController;

// Authentication API Routes
Route::prefix('auth')->group(function () {
    Route::get('/login-fields', [\App\Http\Controllers\Api\AuthApiController::class, 'getLoginFields']); // Get system fields for login (email, password)
    Route::post('/register', [\App\Http\Controllers\Api\AuthApiController::class, 'register']); // Register new customer (full_name, phone, email, password, password_confirmation)
    Route::post('/login', [\App\Http\Controllers\Api\AuthApiController::class, 'login']); // Login customer
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\AuthApiController::class, 'me']); // Get authenticated customer
        Route::post('/logout', [\App\Http\Controllers\Api\AuthApiController::class, 'logout']); // Logout customer
    });
});

// Legacy user endpoint (for backward compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Catalog API Routes
Route::prefix('catalog')->group(function () {
    // Brands
    Route::get('/brands', [CatalogApiController::class, 'getBrands']);
    Route::get('/brands/{identifier}', [CatalogApiController::class, 'getBrand']);
    Route::get('/brands/{identifier}/categories', [CatalogApiController::class, 'getBrandCategories']);
    
    // Categories
    Route::get('/categories', [CatalogApiController::class, 'getCategories']);
    Route::get('/categories/{identifier}', [CatalogApiController::class, 'getCategory']);
    Route::get('/categories/{identifier}/children', [CatalogApiController::class, 'getCategoryChildren']);
    
    // Products
    Route::get('/products', [CatalogApiController::class, 'getProducts']);
    Route::get('/products/{identifier}', [CatalogApiController::class, 'getProduct']);
});

// Sections API Routes - Single endpoint for frontend
Route::prefix('sections')->group(function () {
    // Get sections for frontend (defaults to home page, can filter by page_url)
    Route::get('/', [SectionsApiController::class, 'getSections']);
});

// Cart API Routes
Route::prefix('cart')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\CartApiController::class, 'index']); // Get cart summary
    Route::post('/items', [\App\Http\Controllers\Api\CartApiController::class, 'addItem']); // Add item to cart
    Route::put('/items/{itemId}', [\App\Http\Controllers\Api\CartApiController::class, 'updateItem']); // Update cart item quantity
    Route::delete('/items/{itemId}', [\App\Http\Controllers\Api\CartApiController::class, 'removeItem']); // Remove cart item
    Route::post('/coupon', [\App\Http\Controllers\Api\CartApiController::class, 'applyCoupon']); // Apply coupon
    Route::delete('/coupon', [\App\Http\Controllers\Api\CartApiController::class, 'removeCoupon']); // Remove coupon
})->middleware('api'); // Ensure API middleware is applied

// Order API Routes
Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/validate-cart', [\App\Http\Controllers\Api\OrderApiController::class, 'validateCart']);  
    Route::post('/', [\App\Http\Controllers\Api\OrderApiController::class, 'store']); // Create order from cart
});
