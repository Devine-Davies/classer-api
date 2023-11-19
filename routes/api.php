<?php

use Illuminate\Support\Facades\Route;
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

// User routes
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/user', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/user', [UserController::class, 'destroy']);

// Login route
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
