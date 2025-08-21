<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use App\Logging\AppLogger;
use App\Models\User;
use App\Utils\EmailToken;
use App\Utils\PasswordRestToken;
use App\Enums\AccountStatus;
use App\Jobs\MailUserAccountVerify;
use App\Jobs\MailUserAccountVerified;
use App\Jobs\MailUserReviewReminder;
use App\Jobs\MailUserPasswordReset;
use App\Jobs\MailUserPasswordResetSuccess;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserVerifyRegistrationRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RecorderController;


/**
 * AuthController handles user authentication, registration, and account management.
 * It provides methods for user registration, login, email verification, password reset,
 * and admin login functionalities.
 */
class AuthController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AuthController');
    }

    /**
     * Create User
     * @param Request $request
     * @return 200, 401, 500
     */
    public function register(UserRegisterRequest $request)
    {
        $data = $request->validated();
        $existing = User::where('email', $data['email'])->first();

        // Existing user: banned or deactivated
        $revoked = [2, 3];
        if ($existing && in_array($existing->account_status, $revoked, true)) {
            return response()->json([
                'message' => 'Your account cannot be registered again. Contact support.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Existing user: inactive, may need new token
        if ($existing && $existing->accountInactive()) {
            if (EmailToken::hasExpired($existing->email_verification_token)) {
                $existing->email_verification_token = EmailToken::generateToken();
                $existing->save();
                MailUserAccountVerify::dispatch($existing);
            }

            return response()->json([
                'message' => 'Check your email for the activation link.'
            ], Response::HTTP_OK);
        }

        // Generate a new user uid
        $data['uid']                       = Str::uuid()->toString();
        // Set the account email verification token
        $data['email_verification_token']  = EmailToken::generateToken();
        // Use a random password for initial registration
        $data['password']                  = Hash::make(Str::random(32));

        try {
            $user = User::create($data);
            MailUserAccountVerify::dispatch($user);

            return response()->json([
                'message' => 'Registration successful. Please check your inbox to activate your account.'
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            $this->logger->error("Registration failed", [
                'request' => $request->all(),
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => 'Registration failed, please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify Registration
     * @param Request $request
     * @return 200, 401, 404
     */
    public function verifyRegistration(UserVerifyRegistrationRequest $request)
    {
        $data = $request->validated();

        // Look up the user by token
        $user = User::where('email_verification_token', $data['token'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Verification token is invalid.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Handle expired tokens
        if (EmailToken::hasExpired($data['token'])) {
            $user->email_verification_token = EmailToken::generateToken();
            $user->save();

            MailUserAccountVerify::dispatch($user);

            return response()->json([
                'message' => 'Verification token has expired. We’ve sent a new email.',
            ], Response::HTTP_GONE);
        }

        // All good—activate account
        DB::transaction(function () use ($user, $data) {
            $user->password                  = bcrypt($data['password']);
            $user->account_status            = AccountStatus::VERIFIED;
            $user->email_verification_token  = null;
            $user->save();

            MailUserAccountVerified::dispatch($user);
            MailUserReviewReminder::dispatch($user)->delay(now()->addDays(3));
        });

        return response()->json([
            'message' => 'Your account has been verified. You may now log in.',
        ], Response::HTTP_OK);
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     * @return 401, 500, 200
     */
    public function login(
        UserLoginRequest $request,
        array $abilities = ['user'],
        bool $recordLogin = true
    ) {
        try {
            $loggingAttempt = Auth::once([
                'email'    => $request->email,
                'password' => $request->password,
            ]);

            if (! $loggingAttempt) {
                return $this->failedLoginResponse('Invalid credentials.', Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();

            // Delete all existing tokens for the user
            $user->tokens()->delete();

            // Check if the user is active
            if ($user->accountDeactivated() || $user->accountSuspended()) {
                return $this->failedLoginResponse('Account suspended. Please contact support.', Response::HTTP_FORBIDDEN);
            }

            // Create Session Token to authenticate the user on future requests
            $token = $user->createToken(
                'API Token',
                $abilities,
                Carbon::now()->addDays(40)
            );

            if ($recordLogin) {
                RecorderController::login($user['id']);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'token'   => $token->plainTextToken,
            ], Response::HTTP_OK, [
                'X-Token' => $token->plainTextToken,
            ]);
        } catch (\Throwable $th) {
            $this->logger->error("Login failed", [
                'request' => $request->all(),
                'error'   => $th->getMessage(),
            ]);

            return $this->failedLoginResponse(
                'Something went wrong, please try again.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle failed login response
     *
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function failedLoginResponse(string $message, int $status)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Admin Login
     */
    public function adminLogin(UserLoginRequest $request)
    {
        $adminEmailsStr = config('classer.admin_email');
        $unauthorized = response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], Response::HTTP_UNAUTHORIZED);

        if (!$adminEmailsStr) {
            $this->logger->error("Admin emails not found");
            return response()->json($unauthorized);
        }

        $adminEmails = explode(',', $adminEmailsStr);
        if (!in_array($request->email, $adminEmails)) {
            $this->logger->error("Invalid admin email", [
                'email' => $request->email,
                'headers' => $request->headers->all(),
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
        /* @var \App\Models\User $user */
        $user = auth()->user();
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

        // Create a new token with the specified abilities
        $token = $user->createToken("API TOKEN", $abilities, Carbon::now()->addDays(40));

        // Set the token in the response headers
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
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user || ! $user->currentAccessToken()) {
            return $this->logoutFailed(
                'Not authenticated or no token available.',
                Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $tokenId = $user->currentAccessToken()?->id;
            $user->tokens()->delete();
            $tokenExists = \Laravel\Sanctum\PersonalAccessToken::query()
                ->where('id', $tokenId)
                ->exists();

            if ($tokenExists) {
                $this->logger->error("Failed to delete token", [
                    'user_id' => $user->id,
                    'token_id' => $tokenId,
                ]);
                return $this->logoutFailed(
                    'Failed to delete token, please try again.',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return $this->logoutSuccess();
        } catch (\Throwable $th) {
            $this->logger->error("Logout failed", [
                'error'   => $th->getMessage(),
                'user_id' => $user?->id,
            ]);

            return $this->logoutFailed(
                'Something went wrong, please try again.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Logout Success Response
     *
     * @return JsonResponse
     */
    protected function logoutSuccess(): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Logged out',
        ], Response::HTTP_OK);
    }

    protected function logoutFailed(string $message, int $code): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
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
            'email' => 'required|email'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            if ($user->accountInactive()) {
                return response()->json([
                    // Don't actually send the email, just inform the user
                    'message' => 'Please check your email to continue the password reset process.'
                ], Response::HTTP_OK);
            }

            DB::transaction(function () use ($user) {
                $passwordResetToken = new PasswordRestToken();
                $user->password_reset_token = $passwordResetToken->generateToken();
                $user->save();

                MailUserPasswordReset::dispatch($user);
                RecorderController::passwordResetTriggered($user->id);
            });

            return response()->json([
                'message' => 'A password reset email has been sent to your email address, please check your inbox.'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            $this->logger->error("Forgot password failed", [
                'request' => $request->all(),
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reset Password
     * @param Request $request
     * @return User
     * @return 200, 401, 404
     */
    public function resetPassword(Request $request)
    {
        $validateRequest = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'min:6|required_with:passwordConfirmation|same:passwordConfirmation',
            'passwordConfirmation' => 'required'
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'message' => 'The form contains errors, please make sure passwords match and are at least 4 characters long.',
                'errors' => $validateRequest->errors()
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (PasswordRestToken::hasExpired($request->token)) {
            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $user = User::where('password_reset_token', $request->token)->firstOrFail();

            DB::transaction(function () use ($user, $request) {
                $user->password = bcrypt($request->password);
                $user->password_reset_token = null;
                $user->save();

                MailUserPasswordResetSuccess::dispatch($user);
                RecorderController::userUpdated($user->id);
            });

            return response()->json([
                'message' => 'Your password has been reset, you can now login.'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            $this->logger->error("Password reset failed", [
                'request' => $request->all(),
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}