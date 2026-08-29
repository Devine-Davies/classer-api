<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SchedulerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory;
use Illuminate\View\View;

class SchedulerController extends Controller
{
    public function __construct(
        private readonly SchedulerService $schedulerService,
    ) {}

    /**
     * Show scheduler configuration entries.
     */
    public function index(): Factory|View
    {
        return view('admin.scheduler.index', [
            'jobs' => $this->schedulerService->listJobs(),
        ]);
    }

    /**
     * Trigger the scheduler manually.
     */
    public function run(): RedirectResponse
    {
        $result = $this->schedulerService->triggerAllJobs();
        $output = (string) ($result['output'] ?? '');

        if ($this->hasFailureOutput($output) || ($result['exit_code'] ?? 1) !== 0) {
            return redirect()
                ->route('admin.scheduler')
                ->with('error', 'Scheduler run completed with errors.');
        }

        $message = 'Scheduler triggered successfully.';

        if ($output !== '') {
            $message .= ' '.str($output)->limit(180)->toString();
        }

        return redirect()
            ->route('admin.scheduler')
            ->with('success', $message);
    }

    /**
     * Trigger a single scheduler job manually.
     */
    public function runJob(string $job): RedirectResponse
    {
        $result = $this->schedulerService->triggerJob($job);
        $output = (string) ($result['output'] ?? '');

        if ($this->hasFailureOutput($output) || ($result['exit_code'] ?? 1) !== 0) {
            return redirect()
                ->route('admin.scheduler')
                ->with('error', 'That scheduler job could not be run successfully.');
        }

        $message = 'Scheduler job triggered successfully.';

        if ($output !== '') {
            $message .= ' '.str($output)->limit(180)->toString();
        }

        return redirect()
            ->route('admin.scheduler')
            ->with('success', $message);
    }

    private function hasFailureOutput(string $output): bool
    {
        return str_contains($output, 'FAIL') || str_contains($output, 'FAILED') || str_contains($output, 'ERROR');
    }
}