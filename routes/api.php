<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\UserAccount;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
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
    Route::middleware(['verifyRecaptcha'])
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
 * /admin/send-invites
 * /admin/logs/{filename?}
 */
Route::middleware(['auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::post('/send-invites', [AdminController::class, 'sendInvites']);
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
