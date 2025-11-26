<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InsidersController;

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

/**
 * Main pages routes
 */
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'about']);
Route::get('/guides', [HomeController::class, 'guides']);
Route::get('/contact', [HomeController::class, 'contact']);
Route::get('/download', [HomeController::class, 'download']);

/**
 * Stories routes
 */
Route::group(['prefix' => 'stories'], function () {
    Route::get('/', [HomeController::class, 'posts']);
    Route::get('/{slug}', [HomeController::class, 'post']);
});

/**
 * Blog routes
 */
Route::group(['prefix' => 'blog'], function () {
    Route::get('/', [HomeController::class, 'posts']);
    Route::get('/{slug}', [HomeController::class, 'post']);
});

/**
 * Action Camera Matcher routes
 */
Route::group(['prefix' => 'action-camera-matcher'], function () {
    Route::get('/', [ActionCameraMatcherController::class, 'index']);
    Route::get('/questions', [ActionCameraMatcherController::class, 'questions']);
    Route::get('/results/{answers}', [ActionCameraMatcherController::class, 'results']);
});

/**
 * Legal routes
 */
Route::get('/how-to/deactivate', [HomeController::class, 'howToDeactivate']);
Route::group(['prefix' => 'privacy-policy'], function () {
    Route::get('/', [HomeController::class, 'privacyPolicy']);
    Route::get('/{isoLanCode}', [HomeController::class, 'privacyPolicy'])->name('localized');
});

/**
 * Insiders routes & Sharing routes
 */
Route::get('/insiders/classer-share', [InsidersController::class, 'classerShare']);
Route::get('/share/moment/{uid}', [HomeController::class, 'shareMoment']);

/**
 * Auth routes
 */
Route::group(['prefix' => 'auth'], function () {
    // User auth routes
    Route::get('/register', [AuthController::class, 'register']);
    Route::get('/register/verify/{token}', [AuthController::class, 'verifyAccount']);
    Route::get('/password/forgot', [AuthController::class, 'passwordForgot']);
    Route::get('/password/reset/{token}', [AuthController::class, 'passwordRest']);

    // Social auth routes
    Route::get('/{provider}/redirect', [AuthController::class, 'socialRedirect'])->where('provider', 'google|facebook');
    Route::get('/{provider}/callback', [AuthController::class, 'socialLogin'])->where('provider', 'google|facebook');

    // Admin route
    Route::get('/admin/login', [AuthController::class, 'adminLogin']);
});
