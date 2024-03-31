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
        return view('auth.register');
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

        return view('auth.verify-account', [
            'token' => $token,
            'userEmail' => $user->email,
        ]);
    }

    /**
     * Forgotten Password
     * /auth/forgotten-password
     */
    public function forgottenPassword()
    {
        return view('auth.forgotten-password');
    }

    /**
     * Reset Password
     * /auth/reset-password/{token
     */
    public function resetPassword($token)
    {
        if (PasswordRestToken::hasExpired($token)) {
            return redirect('/');
        }

        $user = User::where('password_reset_token', $token)->first();

        if (!$user) {
            return redirect('/');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'userEmail' => $user->email,
        ]);
    }
}