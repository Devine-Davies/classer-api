<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\EmailToken;
use App\Utils\PasswordRestToken;

class AuthController extends Controller
{
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
    public function adminLogin(Request $request)
    {
        return view('auth.admin.login', [
            'token' => '',
            'userEmail' => '',
        ]);
    }
}