<?php

use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\Admin\DiscountCodesController;
use App\Http\Controllers\Web\Admin\OrdersController;
use App\Http\Controllers\Web\Admin\PlansController;
use App\Http\Controllers\Web\Admin\PostsController;
use App\Http\Controllers\Web\Admin\ProductsController;
use App\Http\Controllers\Web\Admin\StatsController;
use App\Http\Controllers\Web\Admin\UsersController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

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

// when page not found, redirect to home page
Route::fallback(function () {
    return redirect('/');
});

Route::prefix('')->controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/guides', 'guides')->name('guides');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/classer-share', 'classerShare')->name('classer-share');
    Route::get('/app', 'appShowcase')->name('app');
    Route::get('/download', 'download')->name('download');
    Route::get('/products/{catalogSlug}', 'product')->name('products.classer-home');
    Route::get('/how-to/deactivate', 'howToDeactivate')->name('how-to.deactivate');
    Route::get('/share/moment/{uid}', 'shareMoment')->name('share.moment');
});

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::get('/register', 'register')->name('auth.register');
    Route::get('/register/verify/{token}', 'verifyAccount')->name('auth.register.verify');
    Route::get('/password/forgot', 'passwordForgot')->name('auth.password.forgot');
    Route::get('/password/reset/{token}', 'passwordReset')->name('auth.password.reset');
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

Route::prefix('checkout')->controller(CheckoutController::class)->group(function () {
    Route::get('/', 'index')->name('checkout.index');
    Route::post('/start', 'start')->name('checkout.start');

    /*
     * Checkout flow for collecting user details.
     * Example: /checkout/details
     */
    Route::prefix('details')->group(function () {
        Route::get('/', 'details')->name('checkout.details');
        Route::post('/', 'storeDetails')->name('checkout.details.store');
    });

    /*
     * Checkout flow for a specific order.
     * Example: /checkout/123e4567-e89b-12d3-a456-426614174000/payment
     */
    Route::prefix('{orderUid}')->group(function () {
        Route::get('/', 'checkout')->name('checkout.show');
        Route::get('/payment', 'payment')->name('checkout.payment');
        Route::get('/success', 'success')->name('checkout.success');
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])
        ->name('admin.login');

    Route::post('/login', [AdminController::class, 'login'])
        ->middleware('verifyRecaptcha')
        ->name('admin.login.submit');

    Route::middleware(['auth', 'ensureAdminEmail'])->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/trends', 'trends')->name('admin.trends');
            Route::get('/bulk-mails', 'bulkMails')->name('admin.bulk-mails');
            Route::get('/logs', 'logs')->name('admin.logs');
            Route::post('/logs/clear', 'clearLog')->name('admin.logs.clear');
            Route::get('/logout', 'logout')->name('admin.logout');
        });

        Route::prefix('stats')->controller(StatsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.stats');
        });

        Route::prefix('users')->controller(UsersController::class)->group(function () {
            Route::get('/', 'index')->name('admin.users');
            Route::get('/{userUid}', 'show')->name('admin.users.show');
        });

        Route::prefix('orders')->controller(OrdersController::class)->group(function () {
            Route::get('/', 'index')->name('admin.orders');
            Route::get('/{orderUid}', 'show')->name('admin.orders.show');
        });

        Route::prefix('posts')->controller(PostsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.posts');
            Route::post('/', 'store')->name('admin.posts.store');
            Route::get('/add', 'add')->name('admin.posts.add');
            Route::post('/refresh-cache', 'refreshCache')->name('admin.posts.refresh-cache');
            Route::get('/{postUid}', 'edit')->name('admin.posts.edit');
            Route::put('/{postUid}', 'update')->name('admin.posts.update');
            Route::delete('/{postUid}', 'destroy')->name('admin.posts.destroy');
        });

        Route::prefix('products')->controller(ProductsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.products');
            Route::post('/', 'store')->name('admin.products.store');
            Route::get('/add', 'add')->name('admin.products.add');
            Route::get('/{productUid}', 'edit')->name('admin.products.edit');
            Route::put('/{productUid}', 'update')->name('admin.products.update');
        });

        Route::prefix('plans')->controller(PlansController::class)->group(function () {
            Route::get('/', 'index')->name('admin.plans');
            Route::post('/', 'store')->name('admin.plans.create');
            Route::get('/add', 'add')->name('admin.plans.add');
            Route::get('/{planUid}', 'edit')->name('admin.plans.edit');
            Route::put('/{planUid}', 'update')->name('admin.plans.update');
        });

        Route::prefix('discount-codes')->controller(DiscountCodesController::class)->group(function () {
            Route::get('/', 'index')->name('admin.discount-codes');
            Route::post('/', 'store')->name('admin.discount-codes.store');
            Route::get('/add', 'add')->name('admin.discount-codes.add');
            Route::get('/{discountCodeUid}', 'edit')->name('admin.discount-codes.edit');
            Route::put('/{discountCodeUid}', 'update')->name('admin.discount-codes.update');
        });
    });
});
