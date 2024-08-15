<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SchedulerJobController;
use App\Utils\EmailToken;
use App\Utils\PasswordRestToken;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return 200, 401, 500
     */
    public function register(Request $request)
    {
        $validateRequest = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            // 'grc' => 'required',
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }

        // if (!$this->validateCaptcha($request->grc)) {
        //     return response()->json([
        //         'message' => 'Something went wrong, please try again..'
        //     ], 401);
        // }

        $emailToken = new EmailToken();
        $emailAvailable = Validator::make($request->all(), ['email' => 'unique:users,email']);

        if ($emailAvailable->fails()) {
            $user = User::where('email', $request->email)->first();

            if ($user->account_status == 0) { // check if the user is already verified
                if ($emailToken->hasExpired($user->email_verification_token)) { // if expired, generate a new token
                    $user->email_verification_token = EmailToken::generateToken();
                    $user->save();

                    $this->scheduleVerificationEmail($user);
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
            // 'password' => bcrypt($request->password), need this when using token
            'email_verification_token' => EmailToken::generateToken(),
        ]);

        $user = User::create($request->all());
        $this->scheduleVerificationEmail($user);

        return response()->json([
            'message' => 'User created successfully, please check your email to continue the registration process.',
        ], 200);
    }

    /**
     * Verify Registration
     * @param Request $request
     * @return 200, 401, 404
     */
    public function verifyRegistration(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            // 'grc' => 'required',
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

        // if (!$this->validateCaptcha($request->grc)) {
        //     return response()->json([
        //         'message' => 'Something went wrong, please try again..'
        //     ], 401);
        // }

        if (EmailToken::hasExpired($request->token)) {
            return response()->json([
                'message' => 'Something went wrong, please try again later.'
            ], 401);
        }

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->account_status = 1;
        $user->email_verification_token = null;
        $user->save();

        $this->scheduleAccountVerifiedEmail($user);

        return response()->json([
            'message' => 'Account verified successfully'
        ], 200);
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     * @return 401, 500, 200
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
     * @return 200, 500
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
     * Forgot Password
     * @param Request $request
     * @return User
     * @return 200, 401
     */
    public function forgotPassword(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'grc' => 'required',
            'email' => 'required|email'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        
        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again..'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                // Don't actually send the email, just return this message
                'message' => 'Please check your email to continue the password reset process.'
            ], 200);
        }

        $userNotVerified = $user->account_status == 0;

        if ($userNotVerified) {
            return response()->json([
                // Don't actually send the email, just return this message
                'message' => 'Please check your email to continue the password reset process.'
            ], 200);
        }

        $passwordResetToken = new PasswordRestToken();
        $user->password_reset_token = $passwordResetToken->generateToken();
        $user->save();

        $this->schedulePasswordResetEmail($user);
        return response()->json([
            'message' => 'Please check your email to continue the password reset process.'
        ], 200);
    }

    /**
     * Reset Password
     * @param Request $request
     * @return User
     * @return 200, 401, 404
     */
    public function resetPassword(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'grc' => 'required',
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

        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again..'
            ], 401);
        }

        if (PasswordRestToken::hasExpired($request->token)) {
            return response()->json([
                'message' => 'Something went wrong, please try again later.'
            ], 401);
        }

        $user = User::where('password_reset_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->save();

        $this->schedulePasswordResetSuccessEmail($user);

        return response()->json([
            'message' => 'Password reset successfully'
        ], 200);
    }

    /**
     * Send Verification Email
     */
    private function scheduleVerificationEmail($user)
    {
        $schedulerJobController = new SchedulerJobController();
        $schedulerJobController->store(
            array(
                'command' => 'immediate:email-account-verify',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );

        $schedulerJobController->store(
            array(
                'command' => 'daily:email-account-verify-reminder',
                'scheduled_for' => now()->addDays(1),
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );
    }

    /**
     * Send Verification Email
     */
    private function scheduleAccountVerifiedEmail($user)
    {
        $schedulerJobController = new SchedulerJobController();
        $schedulerJobController->store(
            array(
                'command' => 'immediate:email-account-verify-success',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );

        $schedulerJobController->store(
            array(
                'command' => 'daily:email-account-login-reminder',
                'scheduled_for' => now()->addDays(3),
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );
    }

    /**
     * Send Password Reset Email
     */
    private function schedulePasswordResetEmail($user)
    {
        $schedulerJobController = new SchedulerJobController();
        $schedulerJobController->store(
            array(
                'command' => 'immediate:email-password-reset',
                'metadata' => json_encode([
                    'user_id' => $user->id,
                    'token' => $user->password_reset_token
                ]),
            )
        );
    }

    /**
     * Send Password Reset Success Email
     */
    private function schedulePasswordResetSuccessEmail($user)
    {
        $schedulerJobController = new SchedulerJobController();
        $schedulerJobController->store(
            array(
                'command' => 'immediate:email-password-reset-success',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );
    }

    /**
     * Validate Captcha
     * @param string $code
     */
    private function validateCaptcha($code)
    {
        $secretKey = '6LdNKLMpAAAAAAROGY9QuLqt4e-wbxgCmSZzIXEU';
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$code");
        $responseData = json_decode($response);

        if(!$responseData->success) {
            return false;
        }

        if($responseData->score < 0.5) {
            return false;
        }

        return true;
    }
}