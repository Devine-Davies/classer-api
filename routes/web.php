<?php

use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InsidersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Main pages routes
 */
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'about']);
Route::get('/guides', [HomeController::class, 'guides']);
Route::get('/contact', [HomeController::class, 'contact']);
Route::get('/download', [HomeController::class, 'download']);
Route::get('/classer-home', [HomeController::class, 'classerHome']);
Route::get('/classer-home-2', [HomeController::class, 'classerHome2']);

/**
 * Stories routes
 */
Route::group(['prefix' => 'stories'], function () {
    Route::get('/', [HomeController::class, 'posts']);
    Route::get('/{slug}', [HomeController::class, 'post']);
});

/**
 * Blog routes
 */
Route::group(['prefix' => 'blog'], function () {
    Route::get('/', [HomeController::class, 'posts']);
    Route::get('/{slug}', [HomeController::class, 'post']);
});

/**
 * Action Camera Matcher routes
 */
Route::group(['prefix' => 'action-camera-matcher'], function () {
    Route::get('/', [ActionCameraMatcherController::class, 'index']);
    Route::get('/questions', [ActionCameraMatcherController::class, 'questions']);
    Route::get('/results/{answers}', [ActionCameraMatcherController::class, 'results']);
});

/**
 * Legal routes
 */
Route::get('/how-to/deactivate', [HomeController::class, 'howToDeactivate']);
Route::group(['prefix' => 'privacy-policy'], function () {
    Route::get('/', [HomeController::class, 'privacyPolicy']);
    Route::get('/{isoLanCode}', [HomeController::class, 'privacyPolicy'])->name('localized');
});

/**
 * Insiders routes & Sharing routes
 */
Route::get('/insiders/classer-share', [InsidersController::class, 'classerShare']);
Route::get('/share/moment/{uid}', [HomeController::class, 'shareMoment']);

/**
 * One-time checkout routes
 */
Route::group(['prefix' => 'checkout'], function () {
    Route::get('/', [CheckoutController::class, 'product'])->name('checkout.index');
    Route::post('/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('/details', [CheckoutController::class, 'details'])->name('checkout.details');
    Route::post('/details', [CheckoutController::class, 'storeDetails'])->name('checkout.details.store');
    Route::get('/{orderUid}', [CheckoutController::class, 'checkout'])->name('checkout.show');
    Route::get('/{orderUid}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::get('/{orderUid}/success', [CheckoutController::class, 'success'])->name('checkout.success');
});

/**
 * Auth routes
 */
Route::group(['prefix' => 'auth'], function () {
    // Social auth routes
    Route::get('/{provider}/redirect', [AuthController::class, 'socialRedirect'])->where('provider', 'google|facebook');
    Route::get('/{provider}/callback', [AuthController::class, 'socialLogin'])->where('provider', 'google|facebook');

    // Registration routes
    Route::group(['prefix' => 'register'], function () {
        Route::get('/', [AuthController::class, 'register']);
        Route::get('/verify/{token}', [AuthController::class, 'verifyAccount']);
    });

    // Password reset routes
    Route::group(['prefix' => 'password'], function () {
        Route::get('/forgot', [AuthController::class, 'passwordForgot']);
        Route::get('/reset/{token}', [AuthController::class, 'passwordReset']);
    });

    // Admin routes
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/login', [AuthController::class, 'adminLogin']);
        Route::get('/', [AuthController::class, 'admin'])->name('auth.admin');
        Route::get('/stats', [AuthController::class, 'adminStats'])->name('auth.admin.stats');
        Route::get('/trends', [AuthController::class, 'adminTrends'])->name('auth.admin.trends');
        Route::get('/bulk-mails', [AuthController::class, 'adminBulkMails'])->name('auth.admin.bulk-mails');
        Route::get('/logs', [AuthController::class, 'adminLogs'])->name('auth.admin.logs');

        // Products
        Route::group(['prefix' => 'products'], function () {
            Route::get('/', [AuthController::class, 'adminProducts'])->name('auth.admin.products');
            Route::get('/add', [AuthController::class, 'adminProductsAdd'])->name('auth.admin.products.add');
            Route::get('/{productUid}', [AuthController::class, 'adminProductsEdit'])->name('auth.admin.products.edit');
        });

        // Discount Codes
        Route::group(['prefix' => 'discount-codes'], function () {
            Route::get('/', [AuthController::class, 'adminDiscountCodes'])->name('auth.admin.discount-codes');
            Route::get('/add', [AuthController::class, 'adminDiscountCodesAdd'])->name('auth.admin.discount-codes.add');
            Route::get('/{discountCodeUid}', [AuthController::class, 'adminDiscountCodesEdit'])->name('auth.admin.discount-codes.edit');
        });

        // Orders
        Route::group(['prefix' => 'orders'], function () {
            Route::get('/', [AuthController::class, 'adminOrders'])->name('auth.admin.orders');
            Route::get('/{orderUid}', [AuthController::class, 'adminOrderShow'])->name('auth.admin.orders.show');
        });
    });
});
