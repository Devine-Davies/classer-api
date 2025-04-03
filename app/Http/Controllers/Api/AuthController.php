<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
// use App\Models\PersonalAccessToken;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RecorderController;
use App\Http\Controllers\SchedulerController;
use App\Utils\EmailToken;
use App\Utils\PasswordRestToken;
use App\Enums\AccountStatus;


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
            ], Response::HTTP_BAD_REQUEST);
        }

        // if (!$this->validateCaptcha($request->grc)) {
        //     return response()->json([
        //         'message' => 'Something went wrong, please try again.'
        //     ], 401);
        // }

        $emailToken = new EmailToken();
        $emailAvailable = Validator::make($request->all(), ['email' => 'unique:users,email']);

        if (!$emailAvailable->fails()) {
            $request->merge([
                'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
                'email_verification_token' => EmailToken::generateToken(),
            ]);

            $user = User::create($request->all());
            $this->scheduleVerificationEmail($user);

            return response()->json([
                'message' => 'Registration successful, you should receive an email shortly to activate your account.'
            ], Response::HTTP_OK);
        }

        $user = User::where('email', $request->email)->first();

        $revokedStatus = [2, 3];
        if (in_array($user->account_status, $revokedStatus)) {
            // if the account is either deactivated or banned, we can allow the user to register again
            return response()->json([
                'message' => 'We have not been able to verify your account, please try again. If this issue persists, please contact support.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->accountInactive()) {
            if ($emailToken->hasExpired($user->email_verification_token)) { // if expired, generate a new token
                $user->email_verification_token = EmailToken::generateToken();
                $user->save();
                $this->scheduleVerificationEmail($user);
            }
        }

        return response()->json([
            'message' => 'Registration successful, you should receive an email shortly to activate your account.'
        ], Response::HTTP_OK);
    }

    /**
     * Verify Registration
     * @param Request $request
     * @return 200, 401, 404
     */
    public function verifyRegistration(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'grc' => 'required',
            'token' => 'required',
            'password' => 'min:6|required_with:passwordConfirmation|same:passwordConfirmation',
            'passwordConfirmation' => 'required'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'The form contains errors, please make sure passwords match and are at least 4 characters long.',
                'errors' => $validateUser->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again..'
            ], 401);
        }

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], Response::HTTP_NOT_FOUND);
        }

        if (EmailToken::hasExpired($request->token)) {
            $user->email_verification_token = EmailToken::generateToken();
            $user->save();
            $this->scheduleVerificationEmail($user);

            return response()->json([
                'message' => 'Verification token has expired, we have resent a verification email to your email address.'
            ], Response::HTTP_GONE);
        }

        $user->password = bcrypt($request->password);
        $user->account_status = AccountStatus::VERIFIED;
        $user->email_verification_token = null;
        $user->save();

        $this->scheduleAccountVerifiedEmail($user);
        $this->scheduleReviewReminder($user);

        return response()->json([
            'message' => 'Your account has been verified, you can now login.'
        ], Response::HTTP_OK);
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     * @return 401, 500, 200
     */
    public function login(Request $request, $abilities = ['user'], $recordLogin = true)
    {
        $requestValidator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($requestValidator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $requestValidator->errors()
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            if (Auth::attemptWhen($request->only('email', 'password'), function ($user) {
                return $user->account_status !== 0; // make sure the account is not inactive
            })) {
                $user = User::where('email', $request->email)->first();

                if ($user->accountDeactivated() || $user->accountSuspended()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unable to login, please contact support.'
                    ], Response::HTTP_FORBIDDEN);
                }

                // if this is the first login and review reminder emails

                $user->tokens()->delete();
                $token = $user->createToken("API TOKEN", $abilities, Carbon::now()->addDays(40));
                $headers = ['X-Token' => $token->plainTextToken];
                $payload = [
                    'status' => true,
                    'message' => 'Success',
                    'token' => $token->plainTextToken
                ];

                if ($recordLogin) {
                    RecorderController::login($user->id);
                }

                return response()->json($payload, Response::HTTP_OK, $headers);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Login failed, please check your credentials and that your account is verified.'
                ], Response::HTTP_FORBIDDEN);
            }
        } catch (\Throwable $th) {
            Log::error('INTERNAL ERROR: Login', [
                'request' => $request->all(),
                'errors' => $th->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Admin Login
     */
    public function adminLogin(Request $request)
    {
        $adminEmailsStr = env('APP_ADMIN_EMAILS'); // @TODO: Should move this into an Admin DB table
        $unauthorized = response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], Response::HTTP_UNAUTHORIZED);

        if (!$adminEmailsStr) {
            Log::error('INTERNAL ERROR: Admin Login', [
                'request' => $request->all(),
                'errors' => 'Admin emails not found'
            ]);

            return response()->json($unauthorized);
        }

        $adminEmails = explode(',', $adminEmailsStr);
        if (!in_array($request->email, $adminEmails)) {
            Log::error('INTERNAL ERROR: Admin Login', [
                'request' => $request->all(),
                'errors' => 'Email not found'
            ]);

            return response()->json($unauthorized);
        }

        return $this->login($request, ['admin', 'user'], false);
    }

    /**
     * Auto Login
     * @param Request $request
     * @return User
     */
    public function autoLogin(Request $request, $abilities = ['user'], $recordLogin = true)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $user->tokens()->delete();
        if ($user->account_status != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, please contact support'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($recordLogin) {
            RecorderController::autoLogin($user->id);
        }

        $token = $user->createToken("API TOKEN", $abilities, Carbon::now()->addDays(40));
        $headers = ['X-Token' => $token->plainTextToken];
        $payload = [
            'status' => true,
            'message' => 'Success',
            'token' => $token->plainTextToken
        ];

        return response()->json($payload, Response::HTTP_OK, $headers);
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
                'message' => 'Logged out',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('INTERNAL ERROR: Logout', [
                'request' => $request->all(),
                'errors' => $th->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                // Don't actually send the email, just inform the user
                'message' => 'Please check your email to continue the password reset process.'
            ], Response::HTTP_OK);
        }

        if ($user->accountInactive()) {
            return response()->json([
                // Don't actually send the email, just inform the user
                'message' => 'Please check your email to continue the password reset process.'
            ], Response::HTTP_OK);
        }

        $passwordResetToken = new PasswordRestToken();
        $user->password_reset_token = $passwordResetToken->generateToken();
        $user->save();

        RecorderController::passwordResetTriggered($user->id);
        $this->schedulePasswordResetEmail($user);
        return response()->json([
            'message' => 'A password reset email has been sent to your email address, please check your inbox.'
        ], Response::HTTP_OK);
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
            'password' => 'min:6|required_with:passwordConfirmation|same:passwordConfirmation',
            'passwordConfirmation' => 'required'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'The form contains errors, please make sure passwords match and are at least 4 characters long.',
                'errors' => $validateUser->errors()
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (PasswordRestToken::hasExpired($request->token)) {
            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('password_reset_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->save();

        RecorderController::userUpdated($user->id);
        $this->schedulePasswordResetSuccessEmail($user);
        return response()->json([
            'message' => 'Your password has been reset, you can now login.'
        ], Response::HTTP_OK);
    }

    /**
     * Send Verification Email
     */
    private function scheduleVerificationEmail($user)
    {
        $SchedulerController = new SchedulerController();
        $SchedulerController->store(
            array(
                'command' => 'immediate:email-account-verify',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );

        $SchedulerController->store(
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
        $SchedulerController = new SchedulerController();
        $SchedulerController->store(
            array(
                'command' => 'immediate:email-account-verify-success',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );

        $SchedulerController->store(
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
        $SchedulerController = new SchedulerController();
        $SchedulerController->store(
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
        $SchedulerController = new SchedulerController();
        $SchedulerController->store(
            array(
                'command' => 'immediate:email-password-reset-success',
                'metadata' => json_encode([
                    'user_id' => $user->id
                ]),
            )
        );
    }

    /**
     * Send Review Reminder
     */
    private function scheduleReviewReminder($user)
    {
        $SchedulerController = new SchedulerController();
        $SchedulerController->store(
            array(
                'command' => 'daily:email-review-reminder',
                'scheduled_for' => now()->addDays(3),
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

        // not successful.
        if (!$responseData->success) {
            return false;
        }

        // less than 0.5 score, maybe a bot
        if ($responseData->score < 0.50) {
            return false;
        }

        return true;
    }
}
