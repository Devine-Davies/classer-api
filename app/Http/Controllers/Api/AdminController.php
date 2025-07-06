<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RecorderModel;
use App\Models\CloudShare;

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
        $startOfWeek = now()->startOfWeek();

        $monthlyRegisters = User::where('created_at', '>=', now()->startOfMonth())->get();
        $monthlyRegistersCount = count($monthlyRegisters);
        $weeklyRegistersCount = 0;
        foreach ($monthlyRegisters as $register) {
            if ($register->created_at >= $startOfWeek) {
                $weeklyRegistersCount++;
            }
        }

        $monthlyLogins = RecorderModel::where('created_at', '>=', now()->startOfMonth())->get();
        $monthlyLoginsCount = count($monthlyLogins);
        $weeklyLoginsCount = 0;
        foreach ($monthlyLogins as $login) {
            if ($login->created_at >= $startOfWeek) {
                $weeklyLoginsCount++;
            }
        }

        $cloudStats = CloudShare::selectRaw('
            COUNT(*) as total_count,
            SUM(size) as total_size,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as weekly_count,
            SUM(CASE WHEN created_at >= ? THEN size ELSE 0 END) as weekly_size
        ', [$startOfWeek, $startOfWeek])->first();

        $cloudSharesCount = $cloudStats->total_count ?? 0;
        $weeklyCloudSharesCount = $cloudStats->weekly_count ?? 0;
        $totalCloudUsage = $cloudStats->total_size ?? 0;
        $totalCloudUsageWeekly = $cloudStats->weekly_size ?? 0;

        return response()->json([
            'status' => true,
            'message' => 'Stats',
            'data' => [
                'totalUsers' => $usersCount,
                'totalMonthlyRegisters' => $monthlyRegistersCount,
                'totalWeeklyRegisters' => $weeklyRegistersCount,
                'monthlyLoginsCount' => $monthlyLoginsCount,
                'totalWeeklyLogins' => $weeklyLoginsCount,
                'cloudSharesCount' => $cloudSharesCount,
                'weeklyCloudSharesCount' => $weeklyCloudSharesCount,
                'totalCloudUsage' => $totalCloudUsage, // Example value, replace with actual
                'totalCloudUsageWeekly' => $totalCloudUsageWeekly, // Example value, replace with actual
            ]
        ]);
    }

    /**
     * Get logs
     * @param \Illuminate\Http\Request $request
     * @param mixed $filename
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logs(Request $request, $filename = 'laravel.log')
    {
        $path = storage_path("logs/{$filename}");

        if (!File::exists($path)) {
            return response()->json(['message' => "Log file '{$filename}' not found."], 404);
        }

        $lines = explode("\n", File::get($path));
        $tail = array_slice($lines, -200);

        return response()->json($tail);
    }
}
