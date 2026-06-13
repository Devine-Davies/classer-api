<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\CatalogItemsController;
use App\Http\Controllers\Web\Admin\DiscountCodesController;
use App\Http\Controllers\Web\Admin\OrdersController;
use App\Http\Controllers\Web\Admin\PlansController;
use App\Http\Controllers\Web\Admin\ProductsController;
use App\Http\Controllers\Web\Admin\UsersController;
use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\PromotionRedeemController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CheckoutController;
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

Route::prefix('')->controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/guides', 'guides')->name('guides');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/classer-share', 'classerShare')->name('classer-share');
    Route::get('/download', 'download')->name('download');
    Route::get('/classer-home', 'classerHome')->name('classer-home');
    Route::get('/classer-home-2', 'classerHome2')->name('classer-home-2');
    Route::get('/how-to/deactivate', [HomeController::class, 'howToDeactivate']);
    Route::get('/share/moment/{uid}', [HomeController::class, 'shareMoment']);
});

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::get('/register', 'register')->name('auth.register');
    Route::get('/register/verify/{token}', 'verifyAccount')->name('auth.register.verify');
    Route::get('/password/forgot', 'passwordForgot')->name('auth.password.forgot');
    Route::get('/password/reset/{token}', 'passwordReset')->name('auth.password.reset');
    Route::get('/admin/login', 'adminLogin')->name('auth.admin.login');
    Route::get('/{provider}/redirect', 'socialRedirect')->where('provider', 'google|facebook')->name('auth.social.redirect');
    Route::get('/{provider}/callback', 'socialLogin')->where('provider', 'google|facebook')->name('auth.social.callback');
});

Route::prefix('stories')->controller(HomeController::class)->group(function () {
    Route::get('/', 'posts')->name('stories');
    Route::get('/{slug}', 'post')->name('stories.post');
});

Route::prefix('blog')->controller(HomeController::class)->group(function () {
    Route::get('/', 'posts')->name('blog');
    Route::get('/{slug}', 'post')->name('blog.post');
});

Route::prefix('action-camera-matcher')->controller(ActionCameraMatcherController::class)->group(function () {
    Route::get('/', 'index')->name('acm.index');
    Route::get('/questions', 'questions')->name('acm.questions');
    Route::get('/results/{answers}', 'results')->name('acm.results');
});

Route::prefix('privacy-policy')->controller(HomeController::class)->group(function () {
    Route::get('/', 'privacyPolicy')->name('privacy-policy');
    Route::get('/{isoLanCode}', 'privacyPolicy')->name('privacy-policy.localized');
});

Route::prefix('promotions')->controller(PromotionRedeemController::class)->group(function () {
    Route::get('/redeem', 'form')->name('promotions.redeem.form');
    Route::post('/redeem', 'redeem')->name('promotions.redeem.submit');
    Route::get('/redeem/{redeemCode}', 'prefill')
        ->where('redeemCode', '[A-Za-z0-9]{64}')
        ->name('promotions.redeem.prefill');
});

Route::prefix('checkout')->controller(CheckoutController::class)->group(function () {
    Route::get('/', 'product')->name('checkout.index');
    Route::post('/start', 'start')->name('checkout.start');
    Route::get('/details', 'details')->name('checkout.details');
    Route::post('/details', 'storeDetails')->name('checkout.details.store');
    Route::get('/{orderUid}', 'checkout')->name('checkout.show');
    Route::get('/{orderUid}/payment', 'payment')->name('checkout.payment');
    Route::get('/{orderUid}/success', 'success')->name('checkout.success');
});


Route::group(['prefix' => 'auth/admin'], function () {
    Route::prefix('users')->controller(UsersController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.users');
        Route::get('/{userUid}', 'show')->name('auth.admin.users.show');
    });

    Route::prefix('orders')->controller(OrdersController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.orders');
        Route::get('/{orderUid}', 'show')->name('auth.admin.orders.show');
    });

    Route::prefix('catalog-items')->controller(CatalogItemsController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.catalog-items');
        Route::post('/', 'store')->name('auth.admin.catalog-items.store');
        Route::get('/add', 'add')->name('auth.admin.catalog-items.add');
        Route::get('/{catUid}', 'edit')->name('auth.admin.catalog-items.edit');
        Route::put('/{catUid}', 'update')->name('auth.admin.catalog-items.update');
    });

    Route::prefix('discount-codes')->controller(DiscountCodesController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.discount-codes');
        Route::post('/', 'store')->name('auth.admin.discount-codes.store');
        Route::get('/add', 'add')->name('auth.admin.discount-codes.add');
        Route::get('/{discoCodeUid}', 'edit')->name('auth.admin.discount-codes.edit');
        Route::put('/{discoCodeUid}', 'update')->name('auth.admin.discount-codes.update');
    });

    Route::prefix('products')->controller(ProductsController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.products');
        Route::post('/', 'store')->name('auth.admin.products.store');
        Route::get('/add', 'add')->name('auth.admin.products.add');
        Route::get('/{productUid}', 'edit')->name('auth.admin.products.edit');
        Route::put('/{productUid}', 'update')->name('auth.admin.products.update');
    });

    Route::prefix('plans')->controller(PlansController::class)->group(function () {
        Route::get('/', 'index')->name('auth.admin.plans');
        Route::post('/', 'create')->name('auth.admin.plans.create');
        Route::get('/add', 'add')->name('auth.admin.plans.add');
        Route::get('/{planUid}', 'edit')->name('auth.admin.plans.edit');
        Route::put('/{planUid}', 'update')->name('auth.admin.plans.update');
    });

    Route::prefix('')->controller(AdminController::class)->group(function () {
        Route::get('/stats', 'stats')->name('auth.admin.stats');
        Route::get('/trends', 'trends')->name('auth.admin.trends');
        Route::get('/bulk-mails', 'bulkMails')->name('auth.admin.bulk-mails');
        Route::get('/logs/{filename?}', 'logs')->name('auth.admin.logs');
    });
});
