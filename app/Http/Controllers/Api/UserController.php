<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\UserUsageController;
use App\Http\Controllers\RecorderController;
use App\Models\User;
use App\Models\Subscription;
use App\Models\CloudEntity;
use App\Models\CloudEntityStatus;
use App\Enums\AccountStatus;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        return response()->json($request->user());
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
            $request->all(), [
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

    /**
     * Get Cloud Usage
     */
    public function cloudUsage(Request $request)
    {
        $uid = $request->user()->uid;
        $subscription = Subscription::where('uid', $uid)->where('status', 1)
            ->join('subscription_types', 'subscription_types.code', '=', 'subscriptions.sub_type')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $userUsage = UserUsageController::GetTotalUserUsage($uid);
        $totalFiles = $userUsage['totalFiles'];
        $totalSize = $userUsage['totalSize'];

        return response()->json([
            'status' => true,
            'message' => 'Cloud usage retrieved',
            'data' => [
                'totalFiles' => $totalFiles,
                'totalSize' => $totalSize,
                'hardLimitFiles' => $subscription->limit_short_count,
                'hardLimitSize' => $subscription->limit_short_size,
            ]
        ]);
    }

    /**
     * Delete S3 File Request
     */
    public function cloudDelete($id, Request $request)
    {
        $entity = CloudEntity::where('uid', $id)->first();

        if (!$entity) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found',
            ])->setStatusCode(404);
        }

        $schedulerController = new SchedulerController();
        $schedulerController->store([
            'command' => 'app:delete-s3-file',
            'metadata' => json_encode([
                'userId' => $entity->user_id,
                'eventId' => $entity->event_id,
                'location' => $entity->location
            ]),
        ]);

        $entity->status = CloudEntityStatus::SCHEDULED_FOR_DELETION;

        if (!$entity->save()) {
            return response()->json([
                'status' => false,
                'message' => 'Error scheduling deletion',
            ])->setStatusCode(500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Deletion scheduled',
        ]);
    }

    /**
     * Check if user can create short
     */
    public function cloudMomentRequest(String $id, Request $request)
    {
        $uid = $request->user()->uid;
        $subscription = Subscription::where('uid', $uid)->where('status', 1)
            ->join('subscription_types', 'subscription_types.code', '=', 'subscriptions.sub_type')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $userUsage = UserUsageController::GetTotalUserUsage($uid);
        $totalFiles = $userUsage['totalFiles'];
        $hardLimit = $subscription->limit_short_count;

        if ($totalFiles >= $hardLimit) {
            return response()->json([
                'message' => 'You have reached your limit. Please upgrade your subscription.'
            ], 418);
        }

        $cloudEntity = CloudEntity::create([
            'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
            'user_id' => $uid,
            'entity_id' => $id,
            'entity_type' => 'moment',
            'status' => CloudEntityStatus::PROCESSING,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cloud entity created',
            'data' => [
                'token' => $cloudEntity->uid,
            ]
        ], 200);
    }
}