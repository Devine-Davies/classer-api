<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\UserAccount;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\Admin\BulkMailController as AdminBulkMailController;
use App\Http\Controllers\Api\Admin\DiscountCodesController as AdminDiscountCodesController;
use App\Http\Controllers\Api\Admin\OrdersController as AdminOrdersController;
use App\Http\Controllers\Api\Admin\ProductsController as AdminProductsController;
use App\Http\Controllers\Api\Admin\StatsController as AdminStatsController;
use App\Http\Controllers\Api\Admin\TrendsController as AdminTrendsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CloudShareController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
| http://localhost/api/user
|
*/

/**
 * System routes
 * 
 * /versions
 */
Route::get('/versions', [SystemController::class, 'versions']);

/**
 * Site routes
 * 
 * /site/actions-camera-matcher
 * /insiders/invite/accept
 */
Route::group([], function () {
    Route::post('/site/actions-camera-matcher', [SiteController::class, 'acmStore']);
    Route::post('/insiders/invite/accept', [SiteController::class, 'acceptInvite']);
})->middleware('verifyRecaptcha');

/**
 * Public checkout API routes
 */
Route::prefix('checkout')->group(function () {
    Route::post('/orders', [CheckoutController::class, 'createOrder']);
    Route::post('/orders/{orderUid}/discount', [CheckoutController::class, 'applyDiscount']);
    Route::post('/orders/{orderUid}/intent', [CheckoutController::class, 'createPaymentIntent']);
});

/**
 * Stripe webhook endpoint
 */
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

/**
 * Authenticate routes
 * 
 * /auth/login
 * /auth/logout
 * /auth/auto-login
 * /auth/register
 * /auth/register/verify
 * /auth/password/forgot
 * /auth/password/reset
 * /auth/admin/login
 */
Route::prefix('auth')->group(function () {
    Route::middleware([])
        ->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/register/verify', [AuthController::class, 'verifyRegistration']);
            Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
            Route::post('/password/reset', [AuthController::class, 'resetPassword']);
            Route::post('/admin/login', [AuthController::class, 'adminLogin']);
        });

    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])->get('/auto-login', [AuthController::class, 'autoLogin']);
});

/**
 * Admin routes
 * 
 * /admin/stats
 * /admin/bulk-mails/queue
 * /admin/logs/{filename?}
 */
Route::middleware(['auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::prefix('stats')->controller(AdminStatsController::class)->group(function () {
            Route::get('/totalUsers', 'totalUsers');
            Route::get('/registers', 'registers');
            Route::get('/logins', 'logins');
            Route::get('/cloudShares', 'cloudShares');
            Route::get('/cloudShares/active', 'cloudShareActive');
            Route::get('/cloudShares/deleted', 'cloudShareDeleted');
        });

        Route::prefix('trends')->controller(AdminTrendsController::class)->group(function () {
            Route::get('/users', 'users');
            Route::get('/subscriptions', 'subscriptions');
            Route::get('/cloudShares', 'cloudShares');
            Route::get('/logins', 'logins');
        });

        Route::prefix('products')->controller(AdminProductsController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{productUid}', 'show');
            Route::post('/', 'store');
            Route::patch('/{productUid}', 'update');
            Route::delete('/{productUid}', 'destroy');
        });

        Route::prefix('discount-codes')->controller(AdminDiscountCodesController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{discountCodeUid}', 'show');
            Route::post('/', 'store');
            Route::patch('/{discountCodeUid}', 'update');
            Route::patch('/{discountCodeUid}/disable', 'disable');
        });

        Route::prefix('orders')->controller(AdminOrdersController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{orderUid}', 'show');
        });

        Route::prefix('bulk-mails')->controller(AdminBulkMailController::class)->group(function () {
            Route::post('/queue', 'queue');
        });

        Route::get('/logs/{filename?}', [AdminController::class, 'logs']);
    });

/**
 * User routes
 * 
 * /user
 * /user/update-password
 * /user/deactivate
 * /user/enable-subscription
 * /user/cloud/share
 */
Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])
    ->prefix('user')
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('/', [UserController::class, 'update']);
        Route::delete('/', [UserController::class, 'deactivate']);
        Route::patch('/update-password', [UserController::class, 'updatePassword']);
        Route::get('/enable-subscription', [UserController::class, 'enableSubscription']);

        // has.subscription assuming custom middleware for subscription check
        Route::middleware(['has:subscription'])
            ->prefix('cloud')
            ->group(function () {
                Route::get('/share', [CloudShareController::class, 'index']);
            });
    });

/**
 * Cloud Share routes
 * 
 * /cloud/share/presign
 * @deprecated /cloud/share/confirm/{cloudShareUID}
 */
Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])
    ->prefix('cloud')
    ->group(function () {
        // has.subscription assuming custom middleware for subscription check
        Route::middleware(['has:subscription,cloudStorage'])
            ->prefix('share')
            ->group(function () {
                Route::post('', [CloudShareController::class, 'create']);
            });
    });
