<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SchedulerJobController;
use App\Http\Controllers\UserUsageController;
use App\Models\User;
use App\Models\Subscription;
use App\Models\CloudEntity;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        // return $request->user();
        return $request->user()->load(['subscriptions' => function ($query) {
            $query->where('status', 1);
        }]);
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
        $id = $request->user()->id;
        $user = User::find($id);
        $user->name = $request->name;
        // $user->email = $request->email;
        // $user->password = $request->password;
        // $user->trial_code = $request->trial_code;
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->user()->id;
        $user = User::find($id);
        $user->delete();
        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
            'data' => $user
        ]);
    }

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
            'message' => 'Subscription created successfully',
            'data' => $subscription
        ]);
    }

    /**
     * Delete S3 File Request
     */
    public function cloudDelete($id, Request $request)
    {
        $media = CloudEntity::where('uid', $id)->first();

        if (!$media) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found',
            ])->setStatusCode(404);
        }

        $schedulerJobController = new SchedulerJobController();
        $schedulerJobController->store([
            'command' => 'app:delete-s3-file',
            'metadata' => json_encode([
                'userId' => $media->user_id,
                'eventId' => $media->event_id,
                'location' => $media->location
            ]),
        ]);

        $media->status = 0;
        $saved = $media->save();

        if (!$saved) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting file'
            ])->setStatusCode(500);
        }

        return response()->json([
            'status' => true,
            'message' => 'File scheduled for deletion'
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
            ])->setStatusCode(404);
        }

        $userUsage = UserUsageController::GetTotalUserUsage($uid);
        $totalFiles = $userUsage['totalFiles'];
        $hardLimit = $subscription->limit_short_count;

        if ($totalFiles >= $hardLimit) {
            return response()->json([
                'message' => 'You have reached your limit. Please upgrade your subscription.'
            ])->setStatusCode(418);
        }

        $cloudEntity = CloudEntity::create([
            'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
            'user_id' => $uid,
            'entity_id' => $id,
            'entity_type' => 'moment',
        ]);

        return response()->json([
            'status' => true,
            'ticket' => $cloudEntity->uid,
            'message' => 'Cloud entity created successfully',
        ]);
    }


    //     try {
    //         $validateUser = Validator::make(
    //             $request->all(),
    //             [
    //                 'email' => 'required|email'
    //             ]
    //         );

    //         if ($validateUser->fails()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'validation error',
    //                 'errors' => $validateUser->errors()
    //             ], 401);
    //         }

    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Email does not match with our record.',
    //             ], 401);
    //         }

    //         $user->code = Str::upper(Str::random(6));
    //         $user->save();

    //         $schedulerJobController = new SchedulerJobController();
    //         $schedulerJobController->store(
    //             array(
    //                 'command' => 'app:send-code',
    //                 'metadata' => '{"user_id":' . $user->id . '}',
    //             )
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Code Validated Successfully',
    //             'token' => $user->createToken("API TOKEN")->plainTextToken
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
}
