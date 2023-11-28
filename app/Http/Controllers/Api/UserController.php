<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        return $request->user();
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

    /**
     * Validate Code Reset
     * @param Request $request
     * @return User
     */
    // public function validateCodeReset(Request $request)
    // {
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
    //                 'command' => 'app:send-trial-code',
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
