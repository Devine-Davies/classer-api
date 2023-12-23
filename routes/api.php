<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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

// Login route
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/validate-code', [AuthController::class, 'validateCode']);
Route::get('/auth/resend-code', [AuthController::class, 'resendCode']);

// https://classermedia.com/api/aws/create
Route::post('/aws/create', [UserController::class, function (Request $request) {
    Log::useFiles(storage_path().'/logs/s3Create.log');
    Log::info($request->all());
}]);

// User routes
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/user', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/user', [UserController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/user/enable-subscription', [UserController::class, 'enableSubscription']);
Route::middleware('auth:sanctum')->get('/user/can', [UserController::class, 'can']);
Route::middleware('auth:sanctum')->delete('/user/cloud', [UserController::class, 'cloudDelete']);



// Subscription routes
// Route::middleware('auth:sanctum')->get('/subscription', [SubscriptionController::class, 'index']);
// Route::middleware('auth:sanctum')->post('/subscription', [SubscriptionController::class, 'store']);
// Route::middleware('auth:sanctum')->patch('/subscription', [SubscriptionController::class, 'update']);
// Route::middleware('auth:sanctum')->delete('/subscription', [SubscriptionController::class, 'destroy']);