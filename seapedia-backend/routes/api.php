<?php

use App\Http\Controllers\Admin\MonitoringController as AdminMonitoringController;
use App\Http\Controllers\Admin\OverdueController as AdminOverdueController;
use App\Http\Controllers\Driver\JobController as DriverJobController;
use App\Http\Controllers\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Buyer\ReportController as BuyerReportController;
use App\Http\Controllers\Seller\ReportController as SellerReportController;
use App\Http\Controllers\Buyer\AddressController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\CheckoutController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\WalletController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
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

        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/orders/{order}', [SellerOrderController::class, 'show']);
        Route::post('/orders/{order}/process', [\App\Http\Controllers\Seller\OrderController::class, 'process']);

        Route::get('/products', [SellerProductController::class, 'index']);
        Route::post('/products', [SellerProductController::class, 'store']);
        Route::put('/products/{product}', [SellerProductController::class, 'update']);
        Route::delete('/products/{product}', [SellerProductController::class, 'destroy']);

        Route::get('/reports/income', [SellerReportController::class, 'income']);
    });

    Route::middleware('role:buyer')->prefix('buyer')->group(function () {
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/topup', [WalletController::class, 'topup']);

    Route::apiResource('addresses', AddressController::class)->except(['show']);

    Route::get('/cart-items', [CartController::class, 'index']);
    Route::post('/cart-items', [CartController::class, 'store']);
    Route::put('/cart-items/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart-items/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('/cart-items', [CartController::class, 'clear']);

    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/orders', [BuyerOrderController::class, 'index']);
    Route::get('/orders/{order}', [BuyerOrderController::class, 'show']);

    Route::get('/reports/spending', [BuyerReportController::class, 'spending']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
    Route::get('/vouchers', [AdminVoucherController::class, 'index']);
    Route::get('/vouchers/{voucher}', [AdminVoucherController::class, 'show']);
    Route::post('/vouchers', [AdminVoucherController::class, 'store']);

    Route::get('/promos', [AdminPromoController::class, 'index']);
    Route::get('/promos/{promo}', [AdminPromoController::class, 'show']);
    Route::post('/promos', [AdminPromoController::class, 'store']);

    Route::get('/monitoring',            [AdminMonitoringController::class, 'summary']);
    Route::get('/monitoring/users',      [AdminMonitoringController::class, 'users']);
    Route::get('/monitoring/stores',     [AdminMonitoringController::class, 'stores']);
    Route::get('/monitoring/products',   [AdminMonitoringController::class, 'products']);
    Route::get('/monitoring/orders',     [AdminMonitoringController::class, 'orders']);
    Route::get('/monitoring/deliveries', [AdminMonitoringController::class, 'deliveries']);
    Route::get('/monitoring/overdue',    [AdminMonitoringController::class, 'overdueOrders']);

    Route::post('/overdue/handle',       [AdminOverdueController::class, 'handle']);
    Route::post('/simulate-next-day',    [AdminOverdueController::class, 'simulateNextDay']);
    });

    Route::middleware('role:driver')->prefix('driver')->group(function () {
    Route::get('/jobs',                      [DriverJobController::class, 'index']);
    Route::get('/jobs/{delivery}',           [DriverJobController::class, 'show']);
    Route::post('/jobs/{delivery}/take',     [DriverJobController::class, 'take']);
    Route::post('/jobs/{delivery}/complete', [DriverJobController::class, 'complete']);
    Route::get('/history',                   [DriverJobController::class, 'history']);
    });
});
