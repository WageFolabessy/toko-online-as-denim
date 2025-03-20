<?php

use App\Http\Controllers\SiteUser\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

// ADMIN USER CONTROLLER
use App\Http\Controllers\AdminUser\AdminAuthController;
use App\Http\Controllers\AdminUser\CategoryController;
use App\Http\Controllers\AdminUser\DashboardController;
use App\Http\Controllers\AdminUser\ProductController;
use App\Http\Controllers\AdminUser\SiteUserDetailController;
use App\Http\Controllers\AdminUser\OrderController as AdminUserOrderController;
use App\Http\Controllers\AdminUser\PaymentController as AdminUserPaymentController;
use App\Http\Controllers\AdminUser\ShipmentController as AdminUserShipmentController;
use App\Http\Controllers\AdminUser\ProductReviewController as AdminUserProductReviewController;
use App\Http\Controllers\AdminUser\ReportController;
// SITE USER CONTROLLER
use App\Http\Controllers\SiteUser\AuthController;
use App\Http\Controllers\SiteUser\AddressController;
use App\Http\Controllers\SiteUser\OrderController as SiteUserOrderController;
use App\Http\Controllers\SiteUser\PaymentController as SiteUserPaymentController;
use App\Http\Controllers\SiteUser\ShipmentController as SiteUserShipmentController;
use App\Http\Controllers\SiteUser\ShoppingCartController;
use App\Http\Controllers\SiteUser\CollectionController;
use App\Http\Controllers\SiteUser\ProductReviewController as SiteUserProductReviewController;

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
    Route::get('/admin/orders', [AdminUserOrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [AdminUserOrderController::class, 'show']);
    Route::put('/admin/orders/{id}', [AdminUserOrderController::class, 'updateStatus']);
    
    // Payment
    Route::get('/admin/payments', [AdminUserPaymentController::class, 'index']);
    Route::get('/admin/payments/{id}', [AdminUserPaymentController::class, 'show']);
    Route::put('/admin/payments/{id}', [AdminUserPaymentController::class, 'updateStatus']);
    
    // Shipment
    Route::get('/admin/shipments', [AdminUserShipmentController::class, 'index']);
    Route::put('/admin/shipments/{id}', [AdminUserShipmentController::class, 'update']);

    // Proudct Review
    Route::get('/admin/reviews', [AdminUserProductReviewController::class, 'index']);
    Route::get('/admin/reviews/{id}', [AdminUserProductReviewController::class, 'show']);

    // Report
    Route::get('/admin/reports', [ReportController::class, 'index']);
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
    Route::get('/user/user_orders', [SiteUserOrderController::class, 'getUserOrder']);
    Route::get('/user/user_orders/{id}', [SiteUserOrderController::class, 'showUserOrder']);

    // Proudct Review
    Route::post('/user/product/{productId}/reviews', [SiteUserProductReviewController::class, 'store']);
    Route::get('/user/product/{productId}/review-eligibility', [SiteUserProductReviewController::class, 'reviewEligibility']);
    Route::put('/user/reviews/{reviewId}', [SiteUserProductReviewController::class, 'updateReview']);
    Route::delete('/user/reviews/{reviewId}', [SiteUserProductReviewController::class, 'destroyReview']);
    
    // Payment
    Route::post('/midtrans/snap-token', [SiteUserPaymentController::class, 'initiatePayment']);

    // Shipping Cost
    Route::post('/calculate-shipping-cost', [SiteUserShipmentController::class, 'calculateShippingCost']);
});

Route::post('/midtrans/notification', [SiteUserPaymentController::class, 'handleNotification']);

Route::get('/user/get_categories', [CollectionController::class, 'getAllCategories']);
Route::get('/user/get_products', [CollectionController::class, 'getAllProducts']);
Route::get('/user/product/{slug}/detail', [CollectionController::class, 'getProductDetail']);
Route::get('/user/get_latest_products', [CollectionController::class, 'getLatesProducts']);

// Proudct Review
Route::get('/user/product/{productId}/reviews', [SiteUserProductReviewController::class, 'index']);

// Forgot Password
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ForgotPasswordController::class, 'reset']);
