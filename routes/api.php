<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CloudShareController;
use App\Http\Middleware\UserAccount;

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
 */
Route::get('/versions', [SystemController::class, 'versions']);

/**
 * Site routes
 */
Route::group([], function () {
    Route::post('/site/actions-camera-matcher', [SiteController::class, 'acmStore']);
});

/**
 * Authenticate routes
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
    Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])->get(
        '/auto-login',
        [AuthController::class, 'autoLogin']
    );
});

/**
 * Admin routes
 */
Route::middleware(['auth:sanctum', 'abilities:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/logs/{filename?}', [AdminController::class, 'logs']);
    });

/**
 * User routes
 */
Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])
    ->prefix('user')
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('/', [UserController::class, 'update']);
        Route::delete('/', [UserController::class, 'deactivate']);
        Route::patch('/update-password', [UserController::class, 'updatePassword']);
        Route::get('/enable-subscription', [UserController::class, 'enableSubscription']);
        Route::middleware(['has:subscription']) // has.subscription assuming custom middleware for subscription check
            ->prefix('cloud')
            ->group(function () {
                Route::get('/share', [CloudShareController::class, 'index']);
            });
    });

/**
 * User routes
 */
Route::middleware(['auth:sanctum', 'abilities:user', UserAccount::class])
    ->prefix('cloud')
    ->group(function () {
        Route::middleware(['has:subscription,cloudStorage']) // has.subscription assuming custom middleware for subscription check
            ->prefix('share')
            ->group(function () {
                Route::post('/presign', [CloudShareController::class, 'presign']);
                Route::get('/confirm/{uploadId}', [CloudShareController::class, 'confirm']);
            });
    });





// Route::middleware([]) // has.subscription assuming custom middleware for subscription check
// ->prefix('usage')
// ->group(function () {
//     // Route::get('/', [UserUsage::class, 'hasStorage']);
// });

// Route::middleware([]) // has.subscription assuming custom middleware for subscription check
// ->prefix('moment')
// ->group(function () {
//     Route::post('/', [UserMomentsController::class, 'index']);
//     Route::get('/{uid}', [UserMomentsController::class, 'show']);
//     Route::delete('/{uid}', [UserMomentsController::class, 'delete']);
//     Route::get('/can-create/{sizeMB}', [UserMomentsController::class, 'canCreate']);
// });


// /**
//  * Aws routes
//  */
// Route::group(['middleware' => 'auth:sanctum'], function () {
//     Route::get('/aws/credentials', [AwsEventController::class, 'credentials']);
//     Route::post('/aws/event', [AwsEventController::class, 'received']);
// });

// User cloud routes
// Route::middleware('auth:sanctum')->delete('/user/cloud/{id}', [UserController::class, 'cloudDelete']);
// Route::middleware('auth:sanctum')->get('/user/cloud/usage', [UserController::class, 'cloudUsage']);
// Route::middleware('auth:sanctum')->get('/user/cloud/moment/request/{id}', [UserController::class, 'cloudMomentRequest']);