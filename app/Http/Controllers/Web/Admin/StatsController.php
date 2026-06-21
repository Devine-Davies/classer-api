<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StatsController extends Controller
{
    /**
     * StatsController constructor.
     */
    public function __construct(
        protected StatsService $statsService
    ) {}

    /**
     * Get statistics based on provided filters and preset.
     *
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $stats = collect($this->statsService->getStats())
            ->map(function (mixed $value, string $key) {
                $numericValue = is_array($value)
                    ? ($value['value'] ?? $value['total'] ?? 0)
                    : $value;

                return [
                    'label' => match ($key) {
                        'totalUsers' => 'Total Users',
                        'registers' => 'Registers',
                        'logins' => 'Logins',
                        'cloudShares' => 'Cloud Shares',
                        'activeCloudShares' => 'Active Cloud Shares',
                        'deletedCloudShares' => 'Deleted Cloud Shares',
                        default => ucfirst($key),
                    },
                    'value' => $numericValue,
                    'formatted' => number_format((float) $numericValue, 0, '.', ','),
                    'raw' => $value,
                ];
            })
            ->values()
            ->all();

        return view('admin.sections.stats.index', [
            'stats' => $stats,
        ]);
    }
}
