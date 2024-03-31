<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SystemController;

use App\Http\Controllers\Web\AuthController;

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
Route::get('/releases/download', [SystemController::class, 'downloadLatestReleases']);

Route::get('/auth/register', [AuthController::class, 'register']);
Route::get('/auth/verify-account/{token}', [AuthController::class, 'verifyAccount']);
Route::get('/auth/forgotten-password', [AuthController::class, 'forgottenPassword']);
Route::get('/auth/reset-password/{token}', [AuthController::class, 'resetPassword']);
