<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatsDetailsService;
use App\Services\Admin\StatsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatsController extends Controller
{
    /**
     * StatsController constructor.
     */
    public function __construct(
        protected StatsService $statsService,
        protected StatsDetailsService $statsDetailsService,
    ) {}

    /**
     * Get statistics based on provided filters and preset.
     *
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $now = now();

        $stats = [
            [
                'key' => 'overall',
                'label' => 'Overall',
                'description' => 'All-time snapshot across the platform.',
                'items' => $this->mapStatsCards(
                    stats: $this->statsService->getStats(),
                    startDate: '2000-01-01',
                    endDate: $now->toDateString(),
                    interval: 'yearly',
                ),
            ],
            [
                'key' => 'weekly',
                'label' => 'This Week',
                'description' => 'Current week performance and activity.',
                'items' => $this->mapStatsCards(
                    stats: $this->statsService->getStats(preset: 'week'),
                    startDate: $now->copy()->startOfWeek()->toDateString(),
                    endDate: $now->toDateString(),
                    interval: 'daily',
                ),
            ],
        ];

        return view('admin.stats.index', [
            'stats' => $stats,
        ]);
    }

    private function mapStatsCards(array $stats, string $startDate, string $endDate, string $interval): array
    {
        return collect($stats)
            ->map(function (mixed $value, string $key) use ($startDate, $endDate, $interval) {
                $numericValue = is_array($value)
                    ? ($value['value'] ?? $value['total'] ?? 0)
                    : $value;

                $domain = match ($key) {
                    'totalUsers', 'registers' => 'users',
                    'logins' => 'logins',
                    'cloudShares', 'activeCloudShares', 'deletedCloudShares' => 'cloudshares',
                    default => null,
                };

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
                    'details_url' => $domain
                        ? route('admin.stats.details', [
                            'domain' => $domain,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'interval' => $interval,
                        ])
                        : null,
                ];
            })
            ->values()
            ->all();
    }

    public function details(Request $request, string $domain)
    {
        $payload = $this->statsDetailsService->build($this->requestWithDomain($request, $domain));
        $payload['activeSection'] = 'stats';
        $payload['detailsRoute'] = route('admin.stats.details', [
            'domain' => $payload['activeDomain'] ?? $domain,
        ]);

        return view('admin.stats.details', $payload);
    }

    public function export(Request $request, string $domain): StreamedResponse
    {
        $payload = $this->statsDetailsService->build($this->requestWithDomain($request, $domain));
        $series = collect($payload['series']);
        $rows = collect($payload['exportRows']);
        $filters = $payload['filters'];

        $filename = sprintf(
            '%s-stats-%s-to-%s-%s.csv',
            $payload['activeDomain'],
            $filters['start_date'],
            $filters['end_date'],
            $filters['interval'],
        );

        return response()->streamDownload(function () use ($series, $rows): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Period', ...$series->pluck('label')->all()], ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->label,
                    ...$series->map(fn (array $entry): int => (int) ($row->values[$entry['key']] ?? 0))->all(),
                ], ',', '"', '');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function requestWithDomain(Request $request, string $domain): Request
    {
        return $request->duplicate(array_merge($request->query(), [
            'domain' => $domain,
        ]));
    }
}
