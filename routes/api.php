<?php

use Illuminate\Support\Facades\Route;

// ADMIN USER CONTROLLER
use App\Http\Controllers\AdminUser\AdminAuthController;
use App\Http\Controllers\AdminUser\CategoryController;
use App\Http\Controllers\AdminUser\DashboardController;
use App\Http\Controllers\AdminUser\ProductController;
use App\Http\Controllers\AdminUser\SiteUserDetailController;

// SITE USER CONTROLLER
use App\Http\Controllers\SiteUser\AuthController;
use App\Http\Controllers\SiteUser\AddressController;
use App\Http\Controllers\SiteUser\OrderController;
use App\Http\Controllers\SiteUser\PaymentController;
use App\Http\Controllers\SiteUser\ShipmentController;
use App\Http\Controllers\SiteUser\ShoppingCartController;
use App\Http\Controllers\SiteUser\CollectionController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
// });

Route::middleware('guest:sanctum')->group(function () {
    Route::post('/admin/login',    [AdminAuthController::class, 'login']);

    Route::post('/user/register', [AuthController::class, 'register']);
    Route::post('/user/login',    [AuthController::class, 'login']);
});

// ADMIN USER
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/admin/admin', [AdminAuthController::class, 'index']);
    Route::get('/admin/get_admin', [AdminAuthController::class, 'show']);
    Route::post('/admin/admin', [AdminAuthController::class, 'store']);
    Route::put('/admin/admin', [AdminAuthController::class, 'update']);
    Route::delete('/admin/admin/{admin}', [AdminAuthController::class, 'destroy']);
    Route::get('/admin/show_selected_admin/{admin}', [AdminAuthController::class, 'showSelectedAdmin']);
    Route::put('/admin/update_selected_admin/{admin}', [AdminAuthController::class, 'updateSelectedAdmin']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

    // SiteUserDetailController
    Route::put('/admin/site_user', [SiteUserDetailController::class, 'index']);
    Route::get('/admin/site_user/{id}', [SiteUserDetailController::class, 'show']);
    Route::put('/admin/update_siteuser_status/{id}', [SiteUserDetailController::class, 'updateStatus']);

    // Dashboard
    Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/admin/dashboard/orders_data', [DashboardController::class, 'ordersData']);
    Route::get('/admin/dashboard/sales_data', [DashboardController::class, 'salesData']);
    Route::get('/admin/dashboard/recent_orders', [DashboardController::class, 'recentOrders']);

    // Category
    Route::get('/admin/category', [CategoryController::class, 'index']);
    Route::post('/admin/category', [CategoryController::class, 'store']);
    Route::get('/admin/category/{category}', [CategoryController::class, 'show']);
    Route::put('/admin/category/{category}', [CategoryController::class, 'update']);
    Route::delete('/admin/category/{category}', [CategoryController::class, 'destroy']);

    // Product
    Route::get('/admin/product', [ProductController::class, 'index']);
    Route::post('/admin/product', [ProductController::class, 'store']);
    Route::get('/admin/product/{product}', [ProductController::class, 'show']);
    Route::put('/admin/product/{product}', [ProductController::class, 'update']);
    Route::delete('/admin/product/{product}', [ProductController::class, 'destroy']);

    // Order
    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [OrderController::class, 'show']);
});

// SITE USER
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::get('/user/get_user', [AuthController::class, 'getUser']);
    Route::put('/user/update', [AuthController::class, 'updateUser']);

    // Shopping Cart
    Route::get('/user/shopping_cart', [ShoppingCartController::class, 'index']);
    Route::post('/user/shopping_cart', [ShoppingCartController::class, 'addToCart']);
    Route::put('/user/shopping_cart/{id}', [ShoppingCartController::class, 'updateCartItem']);
    Route::delete('/user/shopping_cart/{id}', [ShoppingCartController::class, 'removeCartItem']);

    // Address
    Route::get('/user/addresses', [AddressController::class, 'index']);
    Route::post('/user/addresses', [AddressController::class, 'store']);
    Route::get('/user/addresses/{address}', [AddressController::class, 'show']);
    Route::put('/user/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy']);

    // Order
    Route::get('/user/user_orders', [OrderController::class, 'getUserOrder']);
    Route::get('/user/user_orders/{id}', [OrderController::class, 'showUserOrder']);

    // Payment
    Route::post('/midtrans/snap-token', [PaymentController::class, 'initiatePayment']);

    // Shipping Cost
    Route::post('/calculate-shipping-cost', [ShipmentController::class, 'calculateShippingCost']);
});

Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']);

Route::get('/user/get_categories', [CollectionController::class, 'getAllCategories']);
Route::get('/user/get_products', [CollectionController::class, 'getAllProducts']);
Route::get('/user/product/{slug}/detail', [CollectionController::class, 'getProductDetail']);
