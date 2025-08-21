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

        $cloudShareStats = $this->cloudShareStats();

        return response()->json([
            'status' => true,
            'message' => 'Stats',
            'data' => array_merge([
                'total_users' => $usersCount,
                'total_monthly_registers' => $monthlyRegistersCount,
                'total_weekly_registers' => $weeklyRegistersCount,
                'total_monthly_logins' => $monthlyLoginsCount,
                'total_weekly_logins' => $weeklyLoginsCount
            ], $cloudShareStats)
        ]);
    }

    /**
     * Get cloud share statistics 
     */
    public function cloudShareStats(): array
    {
        $startOfWeek = now()->startOfWeek();
        $stats = CloudShare::withTrashed()->selectRaw('
            COUNT(*) as total_count,
            SUM(size) as total_size,
            SUM(CASE WHEN deleted_at IS NULL AND created_at >= ? THEN 1 ELSE 0 END) as active_weekly_count,
            SUM(CASE WHEN deleted_at IS NULL AND created_at >= ? THEN size ELSE 0 END) as active_weekly_size,
            SUM(CASE WHEN deleted_at IS NOT NULL AND created_at >= ? THEN 1 ELSE 0 END) as deleted_weekly_count,
            SUM(CASE WHEN deleted_at IS NOT NULL AND created_at >= ? THEN size ELSE 0 END) as deleted_weekly_size
        ', [$startOfWeek, $startOfWeek, $startOfWeek, $startOfWeek])->first();

        return [
            'cs_total' => $stats->total_count ?? 0,
            'cs_size' => $stats->total_size ?? 0,
            'cs_active_weekly_total' => $stats->active_weekly_count ?? 0,
            'cs_active_weekly_size' => $stats->active_weekly_size ?? 0,
            'cs_deleted_weekly_total' => $stats->deleted_weekly_count ?? 0,
            'cs_deleted_weekly_size' => $stats->deleted_weekly_size ?? 0,
        ];
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
