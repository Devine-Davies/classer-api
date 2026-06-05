<?php

namespace App\Http\Controllers\Web;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Logging\AppLogger;
use App\Http\Controllers\Controller;
use App\Utils\EmailToken;
use App\Utils\PasswordRestToken;
use App\Enums\AccountStatus;
use App\Enums\RegistrationType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Jobs\MailUserAccountVerified;
use App\Jobs\MailUserReviewReminder;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\RecorderController;

class AuthController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AuthController Web');
    }

    /**
        * Show registration page.
     * /auth/register
     */
    public function register(Request $_): Factory|View
    {
        return view('auth.register.index');
    }

    /**
     * Verify Account
     * /auth/verify-account/{token}
     */
    public function verifyAccount($token)
    {
        if (EmailToken::hasExpired($token)) {
            return redirect('/');
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect('/');
        }

        return view('auth.register.verify', [
            'token' => $token,
            'userEmail' => $user->email,
        ]);
    }

    /**
     * Forgotten Password
     * /auth/password/forgot
     */
    public function passwordForgot()
    {
        return view('auth.password.forgot');
    }

    /**
     * Password Rest
     * /auth/password/rest/{token}
     */
    public function passwordRest($token)
    {
        if (PasswordRestToken::hasExpired($token)) {
            return redirect('/');
        }

        $user = User::where('password_reset_token', $token)->first();

        if (!$user) {
            return redirect('/');
        }

        return view('auth.password.reset', [
            'token' => $token,
            'userEmail' => $user->email,
        ]);
    }

    /**
        * Show admin login page.
        * /auth/admin/login
     */
    public function adminLogin(): Factory|View
    {
        return view('auth.admin.login.index');
    }

    /**
     * Admin section root.
     */
    public function admin(): RedirectResponse
    {
        return redirect('/auth/admin/stats');
    }

    /**
     * Admin stats page.
     */
    public function adminStats(): Factory|View
    {
        return view('auth.admin.sections.stats.index');
    }

    /**
     * Admin trends page.
     */
    public function adminTrends(): Factory|View
    {
        return view('auth.admin.sections.trends.index');
    }

    /**
     * Admin bulk mails page.
     */
    public function adminBulkMails(): Factory|View
    {
        return view('auth.admin.sections.bulk-mails.index', [
            'mailTemplates' => config('classer.admin_bulk_mail_templates', []),
        ]);
    }

    /**
     * Admin logs page.
     */
    public function adminLogs(): Factory|View
    {
        return view('auth.admin.sections.logs.index');
    }

    /**
     * Admin orders page.
     */
    public function adminOrders(): Factory|View
    {
        return view('auth.admin.sections.orders.index');
    }

    /**
     * Admin order details page.
     */
    public function adminOrderShow(string $orderUid): Factory|View
    {
        return view('auth.admin.sections.orders.show', [
            'orderUid' => $orderUid,
        ]);
    }

    /**
     * Admin products page.
     */
    public function adminProducts(): Factory|View
    {
        return view('auth.admin.sections.products.index');
    }

    /**
     * Admin add product page.
     */
    public function adminProductsAdd(): Factory|View
    {
        return view('auth.admin.sections.products.add');
    }

    /**
     * Admin edit product page.
     */
    public function adminProductsEdit(string $productUid): Factory|View
    {
        return view('auth.admin.sections.products.edit', [
            'productUid' => $productUid,
        ]);
    }

    /**
     * Admin discount codes page.
     */
    public function adminDiscountCodes(): Factory|View
    {
        return view('auth.admin.sections.discount-codes.index');
    }

    /**
     * Admin add discount code page.
     */
    public function adminDiscountCodesAdd(): Factory|View
    {
        return view('auth.admin.sections.discount-codes.add');
    }

    /**
     * Admin edit discount code page.
     */
    public function adminDiscountCodesEdit(string $discountCodeUid): Factory|View
    {
        return view('auth.admin.sections.discount-codes.edit', [
            'discountCodeUid' => $discountCodeUid,
        ]);
    }

    /**
     * Social Redirect
     * @param string $provider : google|facebook
     */
    public function socialRedirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Social Login
     */
    public function socialLogin($provider)
    {
        try {
            // Get the user from the Google/Microsoft callback
            $socialiteUser = Socialite::driver($provider)->user();

            // check if the user already exists
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (!$user) {
                // Create a new user
                $user = User::create([
                    'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
                    'name' => $socialiteUser->getName(),
                    'email' => $socialiteUser->getEmail(),
                    'password' => bcrypt(Str::random(16)),
                    'account_status' => 1, //AccountStatus::VERIFIED,
                    // 'registration_type' => RegistrationType::SOCIAL, //RegistrationType::SOCIAL
                ]);

                MailUserAccountVerified::dispatch($user);
                MailUserReviewReminder::dispatch($user)->delay(now()->addDays(3));
            }

            if ($user->account_status === AccountStatus::SUSPENDED) {
                return redirect()->away('classer://auth/login?' . http_build_query([
                    'status' => false,
                ]));
            }

            if (in_array($user->account_status, [AccountStatus::INACTIVE, AccountStatus::DEACTIVATED])) {
                $user->account_status = AccountStatus::VERIFIED;
                $user->save();
            }

            $user->tokens()->delete();
            $token = $user->createToken(
                "API TOKEN",
                ['user'],
                Carbon::now()->addDays(40)
            );

            // Handle the exception
            $this->logger->info('Social login', [
                'provider' => $provider,
                'email' => $user->email,
            ]);

            RecorderController::login($user->id);
            return redirect()->away('classer://auth/login?' . http_build_query([
                'status' => true,
                'message' => 'Success',
                'token' => $token->plainTextToken
            ]));
        } catch (\Exception $e) {
            // Handle the exception
            $this->logger->error('Social login failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
        }
    }
}
