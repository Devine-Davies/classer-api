<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\EmailToken;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function register(Request $request)
    {
        return view('auth.register');
    }

    /**
     * Verify Account
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
}