<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Public\ProductController as PublicProductController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\StoreController as SellerStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/products', [PublicProductController::class, 'index']);
    Route::get('/products/{product}', [PublicProductController::class, 'show']);
});

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);

#Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy']);
    Route::get('/me', [RoleController::class, 'show']);
    Route::post('/select-role', [RoleController::class, 'update']);

    // ---- Seller (Level 2) ----
    Route::middleware('role:seller')->prefix('seller')->group(function () {
        Route::get('/store', [SellerStoreController::class, 'show']);
        Route::post('/store', [SellerStoreController::class, 'store']);

        Route::get('/products', [SellerProductController::class, 'index']);
        Route::post('/products', [SellerProductController::class, 'store']);
        Route::put('/products/{product}', [SellerProductController::class, 'update']);
        Route::delete('/products/{product}', [SellerProductController::class, 'destroy']);
    });
});
