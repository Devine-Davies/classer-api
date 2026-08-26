<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CloudShare\CloudShareExpireUpload;
use App\Jobs\CloudShare\CloudShareVerifyUpload;
use App\Logging\AppLogger;
use App\Services\Admin\CloudShareService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CloudSharesController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly CloudShareService $cloudShareService,
    ) {
        $this->logger->setContext('AdminCloudSharesController Web');
    }

    /**
     * Admin Cloud Share listing page.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->cloudShareService->paginate($request);

        return view('admin.cloud-shares.index', [
            'data' => $paginate->getCollection(),
            'filters' => [
                'state' => strtolower(trim((string) $request->query('state', 'all'))),
                'q' => trim((string) $request->query('q', '')),
            ],
            'pagination' => [
                'total' => $paginate->total(),
                'per_page' => $paginate->perPage(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
            ],
        ]);
    }

    /**
     * Admin Cloud Share detail page.
     */
    public function show(string $cloudShareUid): Factory|View
    {
        $cloudShare = $this->cloudShareService->findByUidOrFail($cloudShareUid);

        return view('admin.cloud-shares.show', [
            'cloudShare' => $cloudShare,
        ]);
    }

    /**
     * Queue CloudShareVerifyUpload manually.
     */
    public function runVerify(string $cloudShareUid): RedirectResponse
    {
        try {
            $cloudShare = $this->cloudShareService->findByUidOrFail($cloudShareUid);

            if ($cloudShare->trashed()) {
                return redirect()->back()->with('error', 'Cannot verify a deleted cloud share.');
            }

            if (! $this->reserveAction('verify', $cloudShareUid, 45)) {
                return redirect()->back()->with('error', 'Verify job was already queued recently. Please wait a moment.');
            }

            CloudShareVerifyUpload::dispatch($cloudShare)->onConnection('cloudshare');

            return redirect()->back()->with('success', 'Verify job queued successfully.');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to queue cloud share verify job', [
                'cloud_share_uid' => $cloudShareUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to queue verify job.');
        }
    }

    /**
     * Queue CloudShareExpireUpload manually.
     */
    public function runExpire(string $cloudShareUid): RedirectResponse
    {
        try {
            $cloudShare = $this->cloudShareService->findByUidOrFail($cloudShareUid);

            if ($cloudShare->trashed()) {
                return redirect()->back()->with('error', 'Cannot expire a deleted cloud share.');
            }

            if (! $this->reserveAction('expire', $cloudShareUid, 45)) {
                return redirect()->back()->with('error', 'Expire job was already queued recently. Please wait a moment.');
            }

            CloudShareExpireUpload::dispatch($cloudShare)->onConnection('cloudshare');

            return redirect()->back()->with('success', 'Expire job queued successfully.');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to queue cloud share expire job', [
                'cloud_share_uid' => $cloudShareUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to queue expire job.');
        }
    }

    /**
     * Execute manual cleanup command for a cloud share.
     */
    public function runCleanup(string $cloudShareUid): RedirectResponse
    {
        try {
            if (! $this->reserveAction('cleanup', $cloudShareUid, 90)) {
                return redirect()->back()->with('error', 'Cleanup was already requested recently. Please wait a moment.');
            }

            $exitCode = Artisan::call('manual:cloud-share-cleanup', [
                'cloudShareUid' => $cloudShareUid,
            ]);

            $output = trim((string) Artisan::output());

            if ($exitCode !== 0) {
                return redirect()->back()->with('error', $output !== ''
                    ? "Cleanup failed: {$output}"
                    : 'Cleanup command failed.');
            }

            return redirect()->back()->with('success', $output !== ''
                ? $output
                : 'Cleanup command completed successfully.');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to run manual cloud share cleanup command', [
                'cloud_share_uid' => $cloudShareUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to execute cleanup command.');
        }
    }

    /**
     * Delete a cloud share and associated entities/S3 objects.
     */
    public function destroy(Request $request, string $cloudShareUid): RedirectResponse
    {
        $request->validate([
            'confirmDelete' => ['required', 'in:DELETE'],
        ]);

        try {
            $cloudShare = $this->cloudShareService->findByUidOrFail($cloudShareUid);
            $result = $this->cloudShareService->deleteCloudShare($cloudShare);

            $this->logger->info('Admin deleted cloud share', [
                'cloud_share_uid' => $cloudShareUid,
                'deleted_objects' => $result['deleted_objects'],
                'deleted_entities' => $result['deleted_entities'],
                'reclaimed_size' => $result['reclaimed_size'],
            ]);

            return redirect()->route('admin.cloud-shares')->with('success', sprintf(
                'Cloud share deleted. Removed %d entities and %d S3 objects.',
                $result['deleted_entities'],
                $result['deleted_objects']
            ));
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to delete cloud share from admin', [
                'cloud_share_uid' => $cloudShareUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete cloud share.');
        }
    }

    /**
     * Reserve a short-lived lock to avoid accidental duplicate operations.
     */
    protected function reserveAction(string $action, string $cloudShareUid, int $seconds): bool
    {
        return Cache::add(
            sprintf('admin:cloud-share:%s:%s', $action, $cloudShareUid),
            now()->timestamp,
            now()->addSeconds($seconds)
        );
    }
}
