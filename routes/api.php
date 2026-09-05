<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CloudBackupController;
use App\Http\Controllers\Api\CloudShareController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\SystemController;
use App\Http\Middleware\UserAccount;
use Illuminate\Support\Facades\Route;

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
})->middleware(['throttle:10,1', 'verifyRecaptcha']);

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
 */
Route::prefix('auth')->group(function () {
    Route::middleware([])
        ->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/register/verify', [AuthController::class, 'verifyRegistration']);
            Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
            Route::post('/password/reset', [AuthController::class, 'resetPassword']);
        });

    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])->get('/auto-login', [AuthController::class, 'autoLogin']);
});

/**
 * Admin routes
 *
 * /admin/stats
 * /admin/logs/{filename?}
 */
Route::middleware(['auth:sanctum', 'ensureAdminEmail'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/logs/{filename?}', [AdminController::class, 'logs'])->name('api.admin.logs.show')->where(
            'filename', '[A-Za-z0-9_\-\.]+'
        );
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

        Route::middleware(['has:subscription,cloudShare'])
            ->prefix('cloud')
            ->group(function () {
                Route::get('/share', [CloudShareController::class, 'index']);
            });

        Route::middleware(['has:subscription,cloudBackup'])
            ->prefix('cloud')
            ->group(function () {
                Route::get('/backup', [CloudBackupController::class, 'index']);
            });
    });

/**
 * Cloud Backup routes
 *
 * /cloud-backups
 * /cloud-backups/{cloudBackupUID}
 * /cloud-backups/{cloudBackupUID}/complete
 * /cloud-backups/{cloudBackupUID}/restore
 */
Route::middleware([
    'auth:sanctum',
    'abilities:user',
    UserAccount::class,
    'has:subscription,cloudBackup',
])->prefix('cloud-backups')->group(function () {
    Route::get('', [CloudBackupController::class, 'index']);
    Route::post('', [CloudBackupController::class, 'create']);
    Route::get('/{cloudBackupUID}', [CloudBackupController::class, 'show']);
    Route::post('/{cloudBackupUID}/complete', [CloudBackupController::class, 'complete']);
    Route::post('/{cloudBackupUID}/restore', [CloudBackupController::class, 'restore']);
    Route::delete('/{cloudBackupUID}', [CloudBackupController::class, 'destroy']);
});

/**
 * Cloud Share routes
 *
 * /cloud/share
 * /cloud/share/{cloudShareUID}/complete
 */
Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])
    ->prefix('cloud')
    ->group(function () {
        Route::middleware(['has:subscription,cloudShare'])
            ->prefix('share')
            ->group(function () {
                Route::post('', [CloudShareController::class, 'create']);
                Route::post('/{cloudShareUID}/complete', [CloudShareController::class, 'complete']);
            });
    });
