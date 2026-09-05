<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CloudBackup\CloudBackupVerifyUpload;
use App\Logging\AppLogger;
use App\Services\Admin\CloudBackupService;
use App\Services\CloudBackupManagementService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CloudBackupsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly CloudBackupService $cloudBackupService,
        private readonly CloudBackupManagementService $cloudBackupManagementService,
    ) {
        $this->logger->setContext('AdminCloudBackupsController Web');
    }

    /**
     * Display a listing of the cloud backups.
     * 
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->cloudBackupService->paginate($request);

        return view('admin.cloud-backups.index', [
            'data' => $paginate->getCollection(),
            'filters' => [
                'state' => strtolower(trim((string) $request->query('state', 'all'))),
                'q' => trim((string) $request->query('q', '')),
            ],
            'pagination' => [
                'total' => $paginate->total(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
            ],
        ]);
    }

    /**
     * Display the specified cloud backup.
     *
     * @param string $cloudBackupUid
     * @return Factory|View
     */
    public function show(string $cloudBackupUid): Factory|View
    {
        return view('admin.cloud-backups.show', [
            'cloudBackup' => $this->cloudBackupService->findByUidOrFail($cloudBackupUid),
        ]);
    }

    /**
     * Queue a verification job for the specified cloud backup.
     *
     * @param string $cloudBackupUid
     * @return RedirectResponse
     */
    public function runVerify(string $cloudBackupUid): RedirectResponse
    {
        try {
            $backup = $this->cloudBackupService->findByUidOrFail($cloudBackupUid);

            if ($backup->trashed()) {
                return redirect()->back()->with('error', 'Cannot verify a deleted cloud backup.');
            }

            if (! $this->reserveAction('verify', $cloudBackupUid)) {
                return redirect()->back()->with('error', 'Verify job was already queued recently. Please wait a moment.');
            }

            CloudBackupVerifyUpload::dispatch($backup)->onConnection('cloudbackup');

            return redirect()->back()->with('success', 'Verify job queued successfully.');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to queue cloud backup verify job', [
                'cloud_backup_uid' => $cloudBackupUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to queue verify job.');
        }
    }

    /**
     * Immediately verify the specified cloud backup.
     *
     * @param string $cloudBackupUid
     * @return RedirectResponse
     */
    public function runVerifyNow(string $cloudBackupUid): RedirectResponse
    {
        try {
            $backup = $this->cloudBackupService->findByUidOrFail($cloudBackupUid);

            if ($backup->trashed()) {
                return redirect()->back()->with('error', 'Cannot verify a deleted cloud backup.');
            }

            if (! $this->reserveAction('verify-now', $cloudBackupUid)) {
                return redirect()->back()->with('error', 'Verification was already started recently. Please wait a moment.');
            }

            $this->cloudBackupManagementService->verify($backup);

            return redirect()->back()->with('success', 'Cloud backup verified successfully.');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to verify cloud backup immediately', [
                'cloud_backup_uid' => $cloudBackupUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Cloud backup verification failed: '.$exception->getMessage());
        }
    }

    public function destroy(Request $request, string $cloudBackupUid): RedirectResponse
    {
        $request->validate(['confirmDelete' => ['required', 'in:DELETE']]);

        try {
            $backup = $this->cloudBackupService->findByUidOrFail($cloudBackupUid);
            $result = $this->cloudBackupService->deleteCloudBackup($backup);

            $this->logger->info('Admin deleted cloud backup', [
                'cloud_backup_uid' => $cloudBackupUid,
                ...$result,
            ]);

            return redirect()->route('admin.cloud-backups')->with('success', sprintf(
                'Cloud backup deleted. Removed %d entities and %d S3 objects.',
                $result['deleted_entities'],
                $result['deleted_objects']
            ));
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to delete cloud backup from admin', [
                'cloud_backup_uid' => $cloudBackupUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete cloud backup.');
        }
    }

    /**
     * Reserve an action for a specific cloud backup to prevent it from being performed too frequently.
     *
     * @param string $action
     * @param string $cloudBackupUid
     * @return bool
     */
    private function reserveAction(string $action, string $cloudBackupUid): bool
    {
        return Cache::add("admin:cloud-backup:{$action}:{$cloudBackupUid}", now()->timestamp, now()->addSeconds(45));
    }
}
