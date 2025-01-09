<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RecorderModel;

class AdminController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return 200, 401, 500
     */
    public function stats(Request $request)
    {
        // $user = $request->user();
        $usersCount = User::count();
        $weeklyRegistersCount = User::where('created_at', '>=', now()->startOfWeek())->count();
        $weeklyLoginsCount = RecorderModel::where('created_at', '>=', now()->startOfWeek())->count();
        $totalMonthlyLogins = RecorderModel::where('created_at', '>=', now()->startOfMonth())->count();
        return response()->json([
            'status' => true,
            'message' => 'Stats',
            'data' => [
                'totalUsers' => $usersCount,
                'totalWeeklyRegisters' => $weeklyRegistersCount,
                'totalWeeklyLogins' => $weeklyLoginsCount,
                'totalMonthlyLogins' => $totalMonthlyLogins
            ]
        ]);
    }
}
