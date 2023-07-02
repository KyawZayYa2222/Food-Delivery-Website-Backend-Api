<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
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
Route::get('product/list', [ProductController::class, 'list']);
Route::post('/contact/create', [ContactController::class, 'store']);
// Routes to import authenticated route
// Routes to import admin route


// Authenticated User routes
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cart/create', [CartController::class, 'store']);
    Route::get('/cart/list', [CartController::class, 'list']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'destory']);
});

// Authenticated Admin routes
Route::middleware(['auth:sanctum', 'admin.auth'])->group(function() {
    Route::post('/category/create', [CategoryController::class, 'store']);
    Route::get('/category/list', [CategoryController::class, 'list']);
    Route::get('/category/list/{id}', [CategoryController::class, 'listById']);
    Route::post('/category/update/{id}', [CategoryController::class, 'update']);
    Route::post('/product/create', [ProductController::class, 'store']);
    Route::post('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'destory']);
    Route::get('/contact/list', [ContactController::class, 'list']);
});
