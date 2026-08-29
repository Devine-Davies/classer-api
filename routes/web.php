<?php

use App\Http\Controllers\Web\ActionCameraMatcherController;
use App\Http\Controllers\Web\Admin\DiscountCodesController;
use App\Http\Controllers\Web\Admin\FaqsController;
use App\Http\Controllers\Web\Admin\CloudSharesController;
use App\Http\Controllers\Web\Admin\OrdersController;
use App\Http\Controllers\Web\Admin\PlansController;
use App\Http\Controllers\Web\Admin\PostsController;
use App\Http\Controllers\Web\Admin\ProductsController;
use App\Http\Controllers\Web\Admin\SchedulerController;
use App\Http\Controllers\Web\Admin\ShippingController;
use App\Http\Controllers\Web\Admin\StatsController;
use App\Http\Controllers\Web\Admin\TutorialsItemsController;
use App\Http\Controllers\Web\Admin\UsersController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\Admin\CurrenciesController;
use App\Http\Controllers\Web\Admin\EmailBroadcastController as AdminEmailBroadcastController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\SessionPreferencesController;
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
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/classer-share', 'classerShare')->name('classer-share');
    Route::get('/products/{catalogSlug}', 'product')->name('products.classer-home');
    Route::get('/how-to/deactivate', 'howToDeactivate')->name('how-to.deactivate');
    Route::get('/share/moment/{uid}', 'shareMoment')->name('share.moment');
});

Route::prefix('app')->controller(HomeController::class)->group(function () {
    Route::get('/', 'appShowcase')->name('app.index');
    Route::get('/showcase', 'appShowcase')->name('app');
    Route::get('/guides', 'guides')->name('guides');
    Route::get('/download', 'download')->name('download');
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

Route::post('/preferences/currency', [SessionPreferencesController::class, 'updateCurrency'])
    ->name('preferences.currency.update');

Route::prefix('checkout')->middleware('restrictCheckoutAccess')->controller(CheckoutController::class)->group(function () {
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
        ->middleware('guest')
        ->name('admin.login');

    Route::post('/login', [AdminController::class, 'login'])
        ->middleware(['guest', 'verifyRecaptcha'])
        ->name('admin.login.submit');

    Route::middleware(['auth', 'ensureAdminEmail'])->group(function () {
        Route::prefix('email-broadcasts')->controller(AdminEmailBroadcastController::class)->group(function () {
            Route::get('/', 'index')->name('admin.email-broadcasts');
            Route::post('/', 'queue')->name('admin.email-broadcasts.queue');
        });

        Route::controller(AdminController::class)->group(function () {
            Route::get('/logs', 'logs')->name('admin.logs');
            Route::post('/logs/clear', 'clearLog')->name('admin.logs.clear');
            Route::post('/logs/backup', 'backupLog')->name('admin.logs.backup');
            Route::get('/logout', 'logout')->name('admin.logout');
        });

        // Statistics
        Route::prefix('stats')->controller(StatsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.stats');
            Route::get('/{domain}/export', 'export')
                ->where('domain', 'users|plans|cloudshares|logins')
                ->name('admin.stats.export');
            Route::get('/{domain}', 'details')
                ->where('domain', 'users|plans|cloudshares|logins')
                ->name('admin.stats.details');
        });

        // Scheduler
        Route::prefix('scheduler')->controller(SchedulerController::class)->group(function () {
            Route::get('/', 'index')->name('admin.scheduler');
            Route::post('/run', 'run')->name('admin.scheduler.run');
            Route::post('/{job}/run', 'runJob')->name('admin.scheduler.jobs.run');
        });

        // Users
        Route::prefix('users')->controller(UsersController::class)->group(function () {
            Route::get('/', 'index')->name('admin.users');
            Route::get('/{userUid}', 'show')->name('admin.users.show');
            Route::post('/{userUid}/deactivate', 'deactivate')->name('admin.users.deactivate');
            Route::delete('/{userUid}', 'destroy')->name('admin.users.destroy');
        });

        // Cloud Shares
        Route::prefix('cloud-shares')->controller(CloudSharesController::class)->group(function () {
            Route::get('/', 'index')->name('admin.cloud-shares');
            Route::get('/{cloudShareUid}', 'show')->name('admin.cloud-shares.show');
            Route::post('/{cloudShareUid}/verify', 'runVerify')->name('admin.cloud-shares.verify');
            Route::post('/{cloudShareUid}/verify-now', 'runVerifyNow')->name('admin.cloud-shares.verify-now');
            Route::post('/{cloudShareUid}/expire', 'runExpire')->name('admin.cloud-shares.expire');
            Route::post('/{cloudShareUid}/cleanup', 'runCleanup')->name('admin.cloud-shares.cleanup');
            Route::delete('/{cloudShareUid}', 'destroy')->name('admin.cloud-shares.destroy');
        });

        // Orders
        Route::prefix('orders')->controller(OrdersController::class)->group(function () {
            Route::get('/', 'index')->name('admin.orders');
            Route::get('/{orderUid}', 'show')->name('admin.orders.show');
        });

        // Posts
        Route::prefix('posts')->controller(PostsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.posts');
            Route::post('/', 'store')->name('admin.posts.store');
            Route::get('/add', 'add')->name('admin.posts.add');
            Route::post('/refresh-cache', 'refreshCache')->name('admin.posts.refresh-cache');
            Route::get('/{postUid}', 'edit')->name('admin.posts.edit');
            Route::put('/{postUid}', 'update')->name('admin.posts.update');
            Route::delete('/{postUid}', 'destroy')->name('admin.posts.destroy');
        });

        // Products
        Route::prefix('products')->controller(ProductsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.products');
            Route::post('/', 'store')->name('admin.products.store');
            Route::get('/add', 'add')->name('admin.products.add');
            Route::post('/{productUid}/publish', 'togglePublished')->name('admin.products.publish');
            Route::get('/{productUid}', 'edit')->name('admin.products.edit');
            Route::put('/{productUid}', 'update')->name('admin.products.update');
        });

        // Plans
        Route::prefix('plans')->controller(PlansController::class)->group(function () {
            Route::get('/', 'index')->name('admin.plans');
            Route::post('/', 'store')->name('admin.plans.create');
            Route::get('/add', 'add')->name('admin.plans.add');
            Route::post('/{planUid}/publish', 'togglePublished')->name('admin.plans.publish');
            Route::get('/{planUid}', 'edit')->name('admin.plans.edit');
            Route::put('/{planUid}', 'update')->name('admin.plans.update');
        });

        // Discount Codes
        Route::prefix('discount-codes')->controller(DiscountCodesController::class)->group(function () {
            Route::get('/', 'index')->name('admin.discount-codes');
            Route::post('/', 'store')->name('admin.discount-codes.store');
            Route::get('/add', 'add')->name('admin.discount-codes.add');
            Route::get('/{discountCodeUid}', 'edit')->name('admin.discount-codes.edit');
            Route::put('/{discountCodeUid}', 'update')->name('admin.discount-codes.update');
        });

        // Shipping
        Route::prefix('shipping')->controller(ShippingController::class)->group(function () {
            Route::get('/', 'index')->name('admin.shipping');
            Route::get('/add', 'add')->name('admin.shipping.add');
            Route::post('/', 'create')->name('admin.shipping.create');
            Route::post('/{shippingRow}/publish', 'togglePublished')
                ->whereNumber('shippingRow')
                ->name('admin.shipping.publish');
            Route::get('/{shippingRow}', 'edit')
                ->whereNumber('shippingRow')
                ->name('admin.shipping.edit');
            Route::put('/{shippingRow}', 'update')
                ->whereNumber('shippingRow')
                ->name('admin.shipping.update');
            Route::delete('/{shippingRow}', 'destroy')
                ->whereNumber('shippingRow')
                ->name('admin.shipping.destroy');
        });

        // Currencies
        Route::prefix('currencies')->controller(CurrenciesController::class)->group(function () {
            Route::get('/', 'index')->name('admin.currencies');
            Route::get('/add', 'add')->name('admin.currencies.add');
            Route::post('/', 'create')->name('admin.currencies.create');
            Route::post('/{currencyRow}/publish', 'togglePublished')
                ->whereNumber('currencyRow')
                ->name('admin.currencies.publish');
            Route::get('/{currencyRow}', 'edit')
                ->whereNumber('currencyRow')
                ->name('admin.currencies.edit');
            Route::put('/{currencyRow}', 'update')
                ->whereNumber('currencyRow')
                ->name('admin.currencies.update');
            Route::delete('/{currencyRow}', 'destroy')
                ->whereNumber('currencyRow')
                ->name('admin.currencies.destroy');
        });

        // FAQs
        Route::prefix('faqs')->controller(FaqsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.faqs');
            Route::post('/', 'store')->name('admin.faqs.store');
            Route::get('/add', 'add')->name('admin.faqs.add');
            Route::post('/{faqUid}/publish', 'togglePublished')->name('admin.faqs.publish');
            Route::get('/{faqUid}', 'edit')->name('admin.faqs.edit');
            Route::put('/{faqUid}', 'update')->name('admin.faqs.update');
            Route::delete('/{faqUid}', 'destroy')->name('admin.faqs.destroy');
        });

        // Tutorials Items
        Route::prefix('tutorials-items')->controller(TutorialsItemsController::class)->group(function () {
            Route::get('/', 'index')->name('admin.tutorials-items');
            Route::post('/', 'create')->name('admin.tutorials-items.create');
            Route::get('/add', 'add')->name('admin.tutorials-items.add');
            Route::get('/{itemId}', 'edit')->name('admin.tutorials-items.edit');
            Route::put('/{itemId}', 'update')->name('admin.tutorials-items.update');
        });
    });
});
