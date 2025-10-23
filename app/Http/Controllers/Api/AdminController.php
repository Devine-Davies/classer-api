<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RecorderModel;
use App\Models\CloudShare;
use App\Jobs\MailEarlyAccessInvite;

/**
 * Admin Controller
 */
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
            'data' => array_merge(
                [
                    'total_users' => $usersCount,
                    'total_monthly_registers' => $monthlyRegistersCount,
                    'total_weekly_registers' => $weeklyRegistersCount,
                    'total_monthly_logins' => $monthlyLoginsCount,
                    'total_weekly_logins' => $weeklyLoginsCount,
                ],
                $cloudShareStats,
            ),
        ]);
    }

    /**
     * Send bulk invites
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvites(Request $request)
    {
        // 1) collect & normalize input
        $raw = (string) $request->input('emails', '');
        $emails = collect(preg_split('/[\s,]+/', $raw))
            ->map(fn($e) => strtolower(trim($e)))
            ->filter() // remove empties
            ->unique()
            ->values();

        // // 2) validate formats
        $validator = Validator::make(['emails' => $emails->toArray()], ['emails' => ['required', 'array', 'min:1'], 'emails.*' => ['email']]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->errors()->all(),
                ],
                422,
            );
        }

        // // 3) fetch active users that match the list
        // // If your DB collation is case-insensitive (default MySQL), this is fine.
        // // If not, consider LOWER(email) matching (shown below in a comment).
        $users = User::query()
            ->whereIn('email', $emails)
            ->where('account_status', 1) // TODO: replace with enum/constant when ready
            ->get( );

        // // optional: compute not_found for UX
        // // map found emails to lowercase to compare apples-to-apples
        $foundEmails = $users->pluck('email')->map(fn($e) => strtolower($e))->values();
        $notFound = $emails->diff($foundEmails)->values();

        // // 4) dispatch invites (chunk to be safe for large sets)
        $users->chunk(200)->each(function ($chunk) {
            foreach ($chunk as $user) {
                MailEarlyAccessInvite::dispatch($user, null);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Invites are being sent',
            'data' => [
                'total_sent' => $users->count(),
                'sent' => $foundEmails, // optional detail
                'not_found' => $notFound, // optional detail
            ],
        ]);
    }

    /**
     * Get cloud share statistics
     */
    public function cloudShareStats(): array
    {
        $startOfWeek = now()->startOfWeek();
        $stats = CloudShare::withTrashed()
            ->selectRaw(
                '
            COUNT(*) as total_count,
            SUM(size) as total_size,
            SUM(CASE WHEN deleted_at IS NULL AND created_at >= ? THEN 1 ELSE 0 END) as active_weekly_count,
            SUM(CASE WHEN deleted_at IS NULL AND created_at >= ? THEN size ELSE 0 END) as active_weekly_size,
            SUM(CASE WHEN deleted_at IS NOT NULL AND created_at >= ? THEN 1 ELSE 0 END) as deleted_weekly_count,
            SUM(CASE WHEN deleted_at IS NOT NULL AND created_at >= ? THEN size ELSE 0 END) as deleted_weekly_size
        ',
                [$startOfWeek, $startOfWeek, $startOfWeek, $startOfWeek],
            )
            ->first();

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
