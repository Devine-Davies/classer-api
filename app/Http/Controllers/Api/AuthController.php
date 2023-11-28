<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SchedulerJobController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Str;


// generate random 6 digit and letter code
function generateRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $request->merge([
                'password' => bcrypt($request->password),
                'code' => Str::upper(Str::random(6)),
            ]);

            $user = UserController::store($request);

            $schedulerJobController = new SchedulerJobController();
            $schedulerJobController->store(
                array(
                    'command' => 'app:send-trial-code',
                    'metadata' => '{"user_id":' . $user->id . '}',
                )
            );

            $schedulerJobController->store(
                array(
                    'command' => 'app:auto-login-reminder',
                    'metadata' => '{"user_id":' . $user->id . '}',
                    'scheduled_for' => now()->addSeconds(10)
                )
            );

            $schedulerJobController->store(
                array(
                    'command' => 'app:auto-login-reminder',
                    'metadata' => '{"user_id":' . $user->id . '}',
                    'scheduled_for' => now()->addSeconds(40)
                )
            );

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
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

            $user = User::where('email', $request->email)->first();
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

    /**
     * Validate Code
     * @param Request $request
     * @return User
     */
    public function validateCode(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'code' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('code', $request->code)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Code does not match with our record.',
                ], 401);
            }

            $user->code = null;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Code Validated Successfully',
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
     * Resend Code
     * @param Request $request
     * @return User
     */
    public function resendCode(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email does not match with our record.',
                ], 401);
            }

            $user->code = Str::upper(Str::random(6));
            $user->save();

            $schedulerJobController = new SchedulerJobController();
            $schedulerJobController->store(
                array(
                    'command' => 'app:send-trial-code',
                    'metadata' => '{"user_id":' . $user->id . '}',
                )
            );

            return response()->json([
                'status' => true,
                'message' => 'Code Resent Successfully',
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
     * @TODO: find out if we need this function. 
     * Validate Code Reset
     * @param Request $request
     * @return User
     */
    public function validateCodeReset(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email does not match with our record.',
                ], 401);
            }

            $user->code = Str::upper(Str::random(6));
            $user->save();

            $schedulerJobController = new SchedulerJobController();
            $schedulerJobController->store(
                array(
                    'command' => 'app:send-trial-code',
                    'metadata' => '{"user_id":' . $user->id . '}',
                )
            );

            return response()->json([
                'status' => true,
                'message' => 'Code Validated Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
