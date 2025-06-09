<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RecorderController;
use App\Models\User;
use App\Models\Subscription;
use App\Enums\AccountStatus;

/**
 * UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        try {
            $user = $request->user();
            $user->load('subscription', 'subscription.paymentMethod');
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    static public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
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

        $request->merge([
            'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-'))
        ]);

        $user = User::create($request->all());
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $user->name = $request->name ?? $user->name;
        $user->dob = $request->dob ?? $user->dob;

        try {
            $user->save();
            RecorderController::userUpdated($user->id);
            return response()->json($user);
        } catch (\Throwable $th) {
            // $th->getMessage(); // This should be logged & monitored
            return response()->json([
                'message' => 'Internal Server Error, Please try again later'
            ], 500);
        }
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        // // Validate request
        $validateUser = Validator::make(
            $request->all(),
            [
                'password' => 'required',
                'newPassword' => 'required|min:6|different:password',
                'passwordConfirmation' => 'required|same:newPassword'
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $user = auth()->user();
        $currentPasswordStatus = Hash::check(
            $request->password,
            auth()->user()->password
        );

        if (!$currentPasswordStatus) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ], 401);
        }

        User::findOrFail(Auth::user()->id)->update([
            'password' => Hash::make($request->newPassword),
        ]);

        RecorderController::userUpdated($user->id);

        return response()->json([
            'status' => true,
            'message' => 'Password updated',
        ]);
    }

    /**
     * Deactivate Account
     */
    public function deactivate(Request $request)
    {
        $user = $request->user();
        $user->account_status = AccountStatus::DEACTIVATED;
        $user->save();

        RecorderController::userUpdated($user->id);
        return response()->json([
            'status' => true,
            'message' => 'Account has been deactivated',
        ]);
    }

    /**
     * Enable Subscription
     */
    public function enableSubscription(Request $request)
    {
        $uid = $request->user()->uid;
        $subscriptionType = $request->input('subType');
        $subscription = Subscription::create([
            'uid' => $uid,
            'sub_type' => $subscriptionType,
            'issue_date' => now(),
            'expiration_date' => now()->addDays(30)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Subscription created',
            'data' => $subscription
        ]);
    }
}
