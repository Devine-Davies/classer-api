<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InsidersController;
use App\Http\Controllers\Web\SubscriptionController;

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
 * Main routes
 */
Route::group([], function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/action-camera-matcher', [HomeController::class, 'actionCameraMatcher']);
    Route::get('/download', [HomeController::class, 'download']);
    Route::get('/privacy-policy/{isoLanCode}', [HomeController::class, 'privacyPolicy']);
    Route::get('/how-to/deactivate', [HomeController::class, 'howToDeactivate']);
    Route::get('/share/moment/{uid}', [HomeController::class, 'shareMoment']);

    Route::get('insiders/classer-share', [InsidersController::class, 'classerShare']);
});

/**
 * Stories routes
 */
Route::group(['prefix' => 'stories'], function () {
    Route::get('/', [HomeController::class, 'stories']);
    Route::get('/{slug}', [HomeController::class, 'story']);
});

// /**
//  * Subscription routes
//  */
// Route::group(['prefix' => 'subscriptions'], function () {
//     Route::get('/', [SubscriptionController::class, 'subscriptions']);
//     Route::get('/{token}', [SubscriptionController::class, 'subscriptionsUser']);
//     Route::post('/subscriptions/redirect', [SubscriptionController::class, 'handleRedirect'])
//         ->name('subscriptions.redirect');
// });

/**
 * Auth routes
 *
 * /register
 * /register/verify/{token}
 * /password/forgot
 * /password/reset/{token}
 * /admin/login
 * /{provider}/redirect
 * /{provider}/callback
 */
Route::group(['prefix' => 'auth'], function () {
    Route::get('/register', [AuthController::class, 'register']);
    Route::get('/register/verify/{token}', [AuthController::class, 'verifyAccount']);
    Route::get('/password/forgot', [AuthController::class, 'passwordForgot']);
    Route::get('/password/reset/{token}', [AuthController::class, 'passwordRest']);
    Route::get('/admin/login', [AuthController::class, 'adminLogin']);
    Route::get('/{provider}/redirect', [AuthController::class, 'socialRedirect'])->where('provider', 'google|facebook');
    Route::get('/{provider}/callback', [AuthController::class, 'socialLogin'])->where('provider', 'google|facebook');
});
