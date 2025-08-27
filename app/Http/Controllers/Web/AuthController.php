<?php

namespace App\Http\Controllers\Web;

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
     * Create User
     * /auth/register
     * @param Request $request
     * @return User 
     */
    public function register(Request $request)
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
     * Create User
     * /auth/register
     * @param Request $request
     * @return User 
     */
    public function adminLogin()
    {
        return view('auth.admin.login.index');
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
            }

            // if ($user->account_status === AccountStatus::SUSPENDED) {
            //     // @TODO Log suspended account access attempt
            //     return redirect()->away('classer://auth/login?' . http_build_query([
            //         'status' => false,
            //     ]));
            // }

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

            $payload = [
                'status' => true,
                'message' => 'Success',
                'token' => $token->plainTextToken
            ];

            // RecorderController::login($user->id);
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
