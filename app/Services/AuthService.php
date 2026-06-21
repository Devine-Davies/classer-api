<?php

namespace App\Services;

use App\Http\Controllers\RecorderController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * Authenticate a user with the provided credentials.
     */
    public function authenticate(
        string $email,
        string $password,
        array $abilities = ['user'],
        bool $createToken = true,
        bool $recordLogin = true
    ): array {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'status' => false,
                'message' => 'Invalid credentials.',
                'code' => Response::HTTP_UNAUTHORIZED,
            ];
        }

        if ($user->accountDeactivated() || $user->accountSuspended()) {
            return [
                'status' => false,
                'message' => 'Account suspended. Please contact support.',
                'code' => Response::HTTP_FORBIDDEN,
            ];
        }

        if ($recordLogin) {
            RecorderController::login($user->id);
        }

        $token = null;

        if ($createToken) {
            $user->tokens()->delete();

            $token = $user
                ->createToken('API Token', $abilities, Carbon::now()->addDays(40))
                ->plainTextToken;
        }

        return [
            'status' => true,
            'message' => 'Login successful.',
            'code' => Response::HTTP_OK,
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(User $user): bool
    {
        $adminEmails = collect(
            explode(',', config('classer.admin_email', ''))
        )
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->values()
            ->all();

        return in_array(strtolower(trim($user->email ?? '')), $adminEmails, true);
    }

    /**
     * Log in a web user.
     */
    public function loginWebUser(User $user): void
    {
        Auth::login($user);
    }

    /**
     * Log out the currently authenticated web user.
     */
    public function logoutWebUser(): void
    {
        Auth::logout();
    }
}
