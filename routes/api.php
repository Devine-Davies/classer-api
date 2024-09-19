<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AwsEventController;
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

// System routes
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
Route::group([], function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/register/verify', [AuthController::class, 'verifyRegistration']);
    Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

/**
 * Aws routes
 */
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/aws/credentials', [AwsEventController::class, 'credentials']);
    Route::post('/aws/event', [AwsEventController::class, 'received']);
});

/**
 * User routes
 */
Route::group(['middleware' => ['auth:sanctum', UserAccount::class]], function () {
    Route::get('/user', [UserController::class, 'index']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'deactivate']);
    Route::get('/user/enable-subscription', [UserController::class, 'enableSubscription']);
});

// User cloud routes
Route::middleware('auth:sanctum')->delete('/user/cloud/{id}', [UserController::class, 'cloudDelete']);
Route::middleware('auth:sanctum')->get('/user/cloud/usage', [UserController::class, 'cloudUsage']);
Route::middleware('auth:sanctum')->get('/user/cloud/moment/request/{id}', [UserController::class, 'cloudMomentRequest']);
