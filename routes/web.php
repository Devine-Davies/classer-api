<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\HomeController;

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
Route::get('/', [HomeController::class, 'index']);
Route::get('/stories', [HomeController::class, 'stories']);
Route::get('/stories/{slug}', [HomeController::class, 'story']);
Route::get('/action-camera-matcher', [HomeController::class, 'actionCameraMatcher']);

/**
 * App routes
 */
Route::get('/download', [HomeController::class, 'download']);
Route::get('/privacy-policy/{isoLanCode}', [HomeController::class, 'privacyPolicy']);

/**
 * Auth routes
 */
Route::get('/auth/register', [AuthController::class, 'register']);
Route::get('/auth/register/verify/{token}', [AuthController::class, 'verifyAccount']);
Route::get('/auth/password/forgot', [AuthController::class, 'passwordForgot']);
Route::get('/auth/password/reset/{token}', [AuthController::class, 'passwordRest']);
