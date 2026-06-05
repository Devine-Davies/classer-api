<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InsidersController;

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
    Route::get('/delivery', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
    Route::post('/delivery', [CheckoutController::class, 'storeDelivery'])->name('checkout.delivery.store');
    Route::get('/{orderUid}', [CheckoutController::class, 'checkout'])->name('checkout.show');
    Route::get('/{orderUid}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::get('/{orderUid}/success', [CheckoutController::class, 'success'])->name('checkout.success');
});

/**
 * Auth routes
 */
Route::group(['prefix' => 'auth'], function () {
    // User auth routes
    Route::get('/register', [AuthController::class, 'register']);
    Route::get('/register/verify/{token}', [AuthController::class, 'verifyAccount']);
    Route::get('/password/forgot', [AuthController::class, 'passwordForgot']);
    Route::get('/password/reset/{token}', [AuthController::class, 'passwordRest']);

    // Social auth routes
    Route::get('/{provider}/redirect', [AuthController::class, 'socialRedirect'])->where('provider', 'google|facebook');
    Route::get('/{provider}/callback', [AuthController::class, 'socialLogin'])->where('provider', 'google|facebook');

    // Admin route
    Route::get('/admin/login', [AuthController::class, 'adminLogin']);
    Route::get('/admin', [AuthController::class, 'admin'])->name('auth.admin');
    Route::get('/admin/stats', [AuthController::class, 'adminStats'])->name('auth.admin.stats');
    Route::get('/admin/trends', [AuthController::class, 'adminTrends'])->name('auth.admin.trends');
    Route::redirect('/admin/invites', '/auth/admin/bulk-mails');
    Route::get('/admin/bulk-mails', [AuthController::class, 'adminBulkMails'])->name('auth.admin.bulk-mails');
    Route::get('/admin/products', [AuthController::class, 'adminProducts'])->name('auth.admin.products');
    Route::get('/admin/products/add', [AuthController::class, 'adminProductsAdd'])->name('auth.admin.products.add');
    Route::get('/admin/products/{productUid}', [AuthController::class, 'adminProductsEdit'])->name('auth.admin.products.edit');
    Route::get('/admin/discount-codes', [AuthController::class, 'adminDiscountCodes'])->name('auth.admin.discount-codes');
    Route::get('/admin/discount-codes/add', [AuthController::class, 'adminDiscountCodesAdd'])->name('auth.admin.discount-codes.add');
    Route::get('/admin/discount-codes/{discountCodeUid}', [AuthController::class, 'adminDiscountCodesEdit'])->name('auth.admin.discount-codes.edit');
    Route::get('/admin/logs', [AuthController::class, 'adminLogs'])->name('auth.admin.logs');
    Route::get('/admin/orders', [AuthController::class, 'adminOrders'])->name('auth.admin.orders');
    Route::get('/admin/orders/{orderUid}', [AuthController::class, 'adminOrderShow'])->name('auth.admin.orders.show');
});
