<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\AwsEventController;

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

// Login routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-registration', [AuthController::class, 'verifyRegistration']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);


// Route::get('reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');

// Route::post('/auth/validate-code', [AuthController::class, 'validateCode']);
// Route::get('/auth/resend-code', [AuthController::class, 'resendCode']);

// Aws routes
Route::middleware('auth:sanctum')->get('/aws/credentials', [AwsEventController::class, 'credentials']);
Route::middleware('auth:sanctum')->post('/aws/event', [AwsEventController::class, 'received']);

// User routes
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/user', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/user', [UserController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/user/enable-subscription', [UserController::class, 'enableSubscription']);

// User cloud routes
Route::middleware('auth:sanctum')->delete('/user/cloud/{id}', [UserController::class, 'cloudDelete']);
Route::middleware('auth:sanctum')->get('/user/cloud/usage', [UserController::class, 'cloudUsage']);
Route::middleware('auth:sanctum')->get('/user/cloud/moment/request/{id}', [UserController::class, 'cloudMomentRequest']);