<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use App\Logging\AppLogger;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RecorderController;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserUpdatePasswordRequest;
use App\Http\Requests\UserDeactivateRequest;
use App\Http\Requests\SubscriptionEnableRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\SubscriptionResource;

/**
 * UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext(context: 'UserController');
    }

    /**
     * Display a listing of the resource.
     * This method retrieves the authenticated user's data,
     * including their subscription and payment method details.
     * It returns a JSON response with the user data.
     * If an error occurs, it logs the error and returns a 500 response.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        try {
            $user->load('subscription', 'subscription.paymentMethod');
            return response()->json(
                (new UserResource($user))->toArray($request)
            );
        } catch (\Throwable $th) {
            $this->logger->error("Error fetching user data", [
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, please try again',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * This method handles user registration.
     * It validates the request data, generates a UUID for the user,
     * hashes the password, and creates a new user record in the database.
     * If successful, it returns a JSON response with the user data.
     * If an error occurs, it logs the error and returns a 500 response.
     */
    public function store(UserStoreRequest $request)
    {
        // Grab only the validated fields
        $data = $request->validated();

        // Add UUID and hash the password
        $data['uid']      = Str::uuid()->toString();
        $data['password'] = bcrypt($data['password']);

        try {
            $user = User::create($data);
            return response()->json(
                (new UserResource($user))->toArray($request),
                201
            );
        } catch (\Throwable $th) {
            $this->logger?->error('Error creating user', [
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error, Please try again later',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * This method updates the user’s profile information.
     * It validates the request data and updates only the fields that are explicitly allowed.
     */
    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->fill($data); // Fill only the fields we’ve explicitly allowed

        try {
            $user->save();

            // Trigger any post-update logic
            RecorderController::userUpdated($user->id);

            // Return a clean, top-level JSON response
            return response()->json(
                (new UserResource($user))->toArray($request),
                200
            );
        } catch (\Throwable $th) {
            $this->logger->error("Error updating user data", [
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error, Please try again later',
            ], 500);
        }
    }

    /**
     * Update Password
     * This method updates the user's password after verifying the current password.
     * It returns a JSON response indicating success or failure.
     */
    public function updatePassword(UserUpdatePasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $data = $request->validated();

        // Check current password
        if (!Hash::check($data['password'], (string) $user->getAuthPassword())) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect',
            ], 401);
        }

        try {
            $user->password = bcrypt($data['newPassword']);
            $user->save();
            
            RecorderController::userUpdated($user->id);
            return response()->json([
                'status'  => true,
                'message' => 'Password updated',
            ]);
        } catch (\Throwable $th) {
            $this->logger->error("Error updating user password", [
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update password.',
            ], 500);
        }
    }

    /**
     * Deactivate Account
     * This method deactivates the user account by setting the account status to DEACTIVATED.
     * It returns a JSON response with the updated user data or an error message.
     */
    public function deactivate(UserDeactivateRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        try {
            $user->update([
                'account_status' => AccountStatus::DEACTIVATED,
            ]);

            RecorderController::userUpdated($user->id);

            return response()->json(
                (new UserResource($user))->toArray($request),
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error("Error deactivating user account", [
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to deactivate account.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Enable Subscription
     * This method creates a new subscription for the user.
     */
    public function enableSubscription(SubscriptionEnableRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $data = $request->validated();

        try {
            $subscription = $user->subscriptions()->create([
                'uid'                     => Str::uuid()->toString(),
                'subscription_type_id'    => $data['subType'],
                'issue_date'              => now(),
                'expiration_date'         => now()->addDays(30),
            ]);

            // record the update, fire events, etc.
            RecorderController::userUpdated($user->id);

            return (new SubscriptionResource($subscription))
                ->additional([
                    'status'  => true,
                    'message' => 'Subscription created',
                ])
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            $this->logger->error("Error creating subscription", [
                'error'   => $th->getMessage(),
                'user_id' => $user->id,
                'payload' => $data,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create subscription.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
