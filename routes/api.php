<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\CategoryController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Public routes
Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/category/list', [CategoryController::class, 'list']);
Route::get('/product/list', [ProductController::class, 'list']);
Route::get('/product/search', [ProductController::class, 'find']);
Route::get('/product/orderbydesc/list', [ProductController::class, 'orderByDesc']);
Route::get('/product/bycategory/{categoryId}/list', [ProductController::class, 'listByCategory']);
Route::get('/product/{id}/details', [ProductController::class, 'show']);
Route::post('/contact/create', [ContactController::class, 'store']);


// Authenticated User routes
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/order/list', [OrderController::class, 'orderListOfAUser']);

    Route::prefix('user')->group(function() {
        Route::get('/details', [UserController::class, 'show']);
        Route::put('/profile-info/update', [UserController::class, 'infoUpdate']);
        Route::post('/profile-picture/update', [UserController::class, 'profileImageUpdate']);
        Route::post('/password/update', [UserController::class, 'passwordUpdate']);
    });

    Route::prefix('cart')->group(function() {
        Route::post('/create', [CartController::class, 'store']);
        Route::get('/list', [CartController::class, 'list']);
        Route::delete('/remove/{id}', [CartController::class, 'destory']);
        Route::post('/order/create', [OrderController::class, 'store']);
    });
});


// Authenticated Admin routes
Route::middleware(['auth:sanctum', 'admin.auth'])->prefix('admin')->group(function() {
    Route::get('/user/list', [UserController::class, 'list']);

    Route::prefix('category')->group(function() {
        Route::post('/create', [CategoryController::class, 'store']);
        Route::get('{id}/', [CategoryController::class, 'show']);
        Route::post('/{id}/update', [CategoryController::class, 'update']);
        Route::delete('/{id}/delete', [CategoryController::class, 'destory']);
    });

    Route::prefix('product')->group(function() {
        Route::post('/create', [ProductController::class, 'store']);
        Route::post('/update/{id}', [ProductController::class, 'update']);
        Route::delete('/delete/{id}', [ProductController::class, 'destory']);
    });

    Route::prefix('order')->group(function() {
        Route::get('/list', [OrderController::class, 'list']);
        Route::put('/{orderId}/accept', [OrderController::class, 'accept']);
        Route::put('/{orderId}/reject', [OrderController::class, 'reject']);
        Route::delete('/{orderId}/delete', [OrderController::class, 'destory']);
    });

    Route::prefix('contact')->group(function() {
        Route::get('/list', [ContactController::class, 'list']);
        Route::delete('/{contactId}/delete', [ContactController::class, 'destory']);
    });
});
