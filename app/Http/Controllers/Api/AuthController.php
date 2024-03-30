<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SchedulerJobController;
use App\Utils\EmailToken;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @returns 200, 401, 500
     */
    public function register(Request $request)
    {
        $validateRequest = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email'
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }
        
        $schedulerJobController = new SchedulerJobController();
        $emailToken = new EmailToken();

        $emailAvalable = Validator::make($request->all(), ['email' => 'unique:users,email']);
        if ($emailAvalable->fails()) {
            $user = User::where('email', $request->email)->first();

            if (!$user->email_verified_at) { // check if the user is already verified
                if ($emailToken->hasExpired($user->email_verification_token)) { // if expired, generate a new token
                    $user->email_verification_token = EmailToken::generateToken();
                    $user->save();

                    $schedulerJobController->store(array(
                        'command' => 'app:send-verfication-email',
                        'metadata' => json_encode([
                            'user_id' => $user->id,
                            'token' => $user->email_verification_token
                        ]),
                    ));
                }

                return response()->json([
                    'message' => 'Please check your email to continue the registration process.',
                ], 200);
            }

            return response()->json([
                'message' => 'Whoops! Something went wrong, please try again later.'
            ], 500);
        }

        $request->merge([
            'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
            'password' => bcrypt($request->password),
            'email_verification_token' => EmailToken::generateToken(),
        ]);

        $user = User::create($request->all());
        $schedulerJobController->store(array(
            'command' => 'app:send-verfication-email',
            'metadata' => json_encode([
                'user_id' => $user->id,
                'token' => $user->email_verification_token
            ]),
        ));

        return response()->json([
            'message' => 'User created successfully, please check your email to continue the registration process.',
        ], 200);
    }

    /**
     * Verify Registration
     * @param Request $request
     * @returns 200, 401, 404
     */
    public function verifyRegistration(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'min:4|required_with:passwordConfirmation|same:passwordConfirmation',
            'passwordConfirmation' => 'required'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'The form contains errors, please make sure passwords match and are at least 4 characters long.',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if (EmailToken::hasExpired($request->token)) {
            return response()->json([
                'message' => 'Token not valid'
            ], 401);
        }

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return response()->json([
            'message' => 'Account verified successfully'
        ], 200);
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            // get all user Subscription
            $user = User::where('email', $request->email)
                ->with('subscriptions')
                ->first();

            $user->logged_in_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Logout The User
     * @param Request $request
     * @return User
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'User Logged Out Successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}



// $schedulerJobController = new SchedulerJobController();
// $schedulerJobController->store(
//     array(
//         'command' => 'app:send-verfication-email',
//         'metadata' => json_encode([
//             'user_id' => $user->id,
//             'token' => $emailVerificationToken
//         ]),
//     )
// );


// $schedulerJobController = new SchedulerJobController();
// $schedulerJobController->store(
//     array(
//         'command' => 'app:send-code',
//         'metadata' => '{"user_id":' . $user->id . '}',
//     )
// );

// $schedulerJobController->store(
//     array(
//         'command' => 'app:auto-login-reminder',
//         'metadata' => '{"user_id":' . $user->id . '}',
//         'scheduled_for' => now()->addDays(3)
//     )
// );

// $schedulerJobController->store(
//     array(
//         'command' => 'app:auto-login-reminder',
//         'metadata' => '{"user_id":' . $user->id . '}',
//         'scheduled_for' => now()->addDays(10)
//     )
// );