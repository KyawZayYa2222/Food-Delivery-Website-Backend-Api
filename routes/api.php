<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GiveawayController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SlideshowController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\admin\PaymentController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\DashboardController;


// Public routes
Route::controller(AuthController::class)->group(function() {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/google-login', 'loginWithGoogle');
});
Route::prefix('category')->controller(CategoryController::class)->group(function() {
    Route::get('/list', 'list');
    Route::get('/paginatedlist', 'paginatedList');
});
Route::prefix('product')->controller(ProductController::class)->group(function() {
    Route::get('/list', 'list');
    Route::get('/search', 'find');
    Route::get('/orderbydesc/list', 'orderByDesc');
    Route::get('/bycategory/{categoryId}/list', 'listByCategory');
    Route::get('/{id}/details', 'show');
});

Route::post('/contact/create', [ContactController::class, 'store']);
Route::post('/subscriber/create', [SubscriberController::class, 'store']);
Route::get('/giveaway/{id}/show', [GiveawayController::class, 'show']);
Route::get('/feedback/public-list', [FeedbackController::class, 'publicList']);
Route::get('/slideshow/active-list', [SlideshowController::class, 'activeList']);


// Authenticated User routes
Route::middleware('auth:sanctum')->prefix('user')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('order')->controller(OrderController::class)->group(function() {
        Route::get('/list', 'orderListOfAUser');
        Route::delete('/{id}/cancel', 'orderCancel');
    });

    Route::controller(UserController::class)->group(function() {
        Route::get('/details', 'show');
        Route::put('/profile-info/update', 'infoUpdate');
        Route::post('/profile-picture/update', 'profileImageUpdate');
        Route::post('/password/update', 'passwordUpdate');
    });

    Route::prefix('cart')->controller(CartController::class)->group(function() {
        Route::post('/create', 'store');
        Route::get('/list', 'list');
        Route::get('/total-cost', 'totalCost')->name('cart-totalcost');
        Route::post('/{id}/increasecount', 'increaseCount');
        Route::post('/{id}/decreasecount', 'decreaseCount');
        Route::delete('/all/remove', 'destory');
        Route::delete('/{id}/remove', 'delete');
    });

    Route::post('/feedback/create', [FeedbackController::class, 'store']);
    Route::post('/order/create', [OrderController::class, 'store']);
});


// Authenticated Admin routes
Route::middleware(['auth:sanctum', 'admin.auth'])->prefix('admin')->group(function() {
    Route::prefix('dashboard')->controller(DashboardController::class)->group(function() {
        Route::get('/total-income', 'totalIncome');
        Route::get('/total-order', 'totalOrder');
        Route::get('/total-product', 'totalProduct');
        Route::get('/total-register', 'totalRegister');
        Route::get('/recent-sales', 'recentSales');
    });

    Route::prefix('user')->controller(UserController::class)->group(function() {
        Route::get('/list', 'list');
        Route::delete('/{id}/delete', 'destroy');
    });

    Route::prefix('category')->controller(CategoryController::class)->group(function() {
        Route::post('/create', 'store');
        Route::get('/{id}', 'show');
        Route::post('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destory');
    });

    Route::prefix('giveaway')->controller(GiveawayController::class)->group(function() {
        Route::get('/list', 'list');
        Route::get('/paginatedlist', 'paginatedList');
        Route::post('/create', 'store');
        Route::post('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destory');
    });

    Route::prefix('promotion')->controller(PromotionController::class)->group(function() {
        Route::get('/list/all', 'allList');
        Route::get('/list/active', 'activeList');
        Route::post('/create', 'store');
        Route::put('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destory');
    });

    Route::prefix('product')->controller(ProductController::class)->group(function() {
        Route::post('/create', 'store');
        Route::post('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destory');
    });

    Route::prefix('payment')->controller(PaymentController::class)->group(function() {
        Route::get('/list', 'list');
        Route::get('/{id}/verify', 'verify');
        Route::get('/{id}/reject', 'reject');
    });

    Route::prefix('order')->controller(OrderController::class)->group(function() {
        Route::get('/list', 'list');
        Route::put('/{id}/accept', 'accept');
        Route::put('/{id}/reject', 'reject');
        Route::delete('/{id}/delete', 'destory');
    });

    Route::prefix('contact')->controller(ContactController::class)->group(function() {
        Route::get('/list', 'list');
        Route::delete('/{contactId}/delete', 'destory');
    });

    Route::prefix('feedback')->controller(FeedbackController::class)->group(function() {
        Route::get('/list', 'list');
        Route::get('/{id}/control-public', 'controlPublic');
        Route::delete('/{id}/delete', 'destroy');
    });

    Route::prefix('slideshow')->controller(SlideshowController::class)->group(function() {
        Route::get('/list', 'list');
        Route::post('/create', 'store');
        Route::post('/{id}/update', 'update');
        Route::delete('/{id}/delete', 'destroy');
    });

    Route::prefix('subscriber')->controller(SubscriberController::class)->group(function() {
        Route::get('/list', 'list');
        Route::delete('/{id}/delete', 'destory');
    });
});
