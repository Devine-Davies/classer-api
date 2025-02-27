<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
// use App\Http\Controllers\Api\AwsEventController;
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
Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/verify', [AuthController::class, 'verifyRegistration']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);

    Route::get('/auto-login', [AuthController::class, 'autoLogin'])->middleware(['auth:sanctum', 'abilities:user', UserAccount::class]);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

/**
 * Admin routes
 */
Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:sanctum', 'abilities:admin']
], function () {
    Route::get('/stats', [AdminController::class, 'stats']);
})->middleware('auth:sanctum');

/**
 * User routes
 */
Route::group([
    'prefix' => 'user',
    'middleware' => ['auth:sanctum', 'abilities:user', UserAccount::class]
], function () {
    Route::get('/', [UserController::class, 'index']);
    Route::patch('/', [UserController::class, 'update']);
    Route::delete('/', [UserController::class, 'deactivate']);
    Route::patch('/update-password', [UserController::class, 'updatePassword']);
    Route::get('/enable-subscription', [UserController::class, 'enableSubscription']);
});



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