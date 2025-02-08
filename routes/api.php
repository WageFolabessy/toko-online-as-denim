<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
// });

Route::post('/register', [AuthController::class, 'register'])->middleware('guest:sanctum');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');
Route::put('/user/update', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');

Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
Route::get('/category/{id}', [CategoryController::class, 'edit']);
Route::put('/category/{id}', [CategoryController::class, 'update']);
Route::delete('/category/{id}', action: [CategoryController::class, 'destroy']);
Route::get('/category/{slug}/products', [CategoryController::class, 'getByCategory']);

Route::get('/product', [ProductController::class, 'index']);
Route::post('/product', [ProductController::class, 'store']);
Route::get('/product/{id}', [ProductController::class, 'edit']);
Route::put('/product/{id}', [ProductController::class, 'update']);
Route::delete('/product/{id}', [ProductController::class, 'destroy']);
Route::get('/product/{slug}/detail', [ProductController::class, 'getProductDetail']);

Route::get('/product_image', [ProductImageController::class, 'index']);
Route::post('/product_image', [ProductImageController::class, 'store']);
Route::get('/product_image/{id}', [ProductImageController::class, 'showByProduct']);
Route::put('/product_image/{id}', [ProductImageController::class, 'update']);
Route::delete('/product_image/{id}', [ProductImageController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shopping_cart', [ShoppingCartController::class, 'index']);
    Route::post('/shopping_cart', [ShoppingCartController::class, 'addToCart']);
    Route::put('/shopping_cart/{id}', [ShoppingCartController::class, 'updateCartItem']);
    Route::delete('/shopping_cart/{id}', [ShoppingCartController::class, 'removeCartItem']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
});

