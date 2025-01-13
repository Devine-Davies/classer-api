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
        $usersCount = User::count();

        $monthlyRegisters = User::where('created_at', '>=', now()->startOfMonth())->get();
        $monthlyRegistersCount = count($monthlyRegisters);
        $weeklyRegistersCount = 0;
        foreach ($monthlyRegisters as $register) {            
            if ($register->created_at >= now()->startOfWeek()) {
                $weeklyRegistersCount++;
            }
        }

        $monthlyLogins = RecorderModel::where('created_at', '>=', now()->startOfMonth())->get();
        $monthlyLoginsCount = count($monthlyLogins);
        $weeklyLoginsCount = 0;
        foreach ($monthlyLogins as $login) {
            if ($login->created_at >= now()->startOfWeek()) {
                $weeklyLoginsCount++;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Stats',
            'data' => [
                'totalUsers' => $usersCount,
                'totalMonthlyRegisters' => $monthlyRegistersCount,
                'totalWeeklyRegisters' => $weeklyRegistersCount,
                'monthlyLoginsCount' => $monthlyLoginsCount,
                'totalWeeklyLogins' => $weeklyLoginsCount
            ]
        ]);
    }
}
