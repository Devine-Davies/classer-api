<?php

namespace App\Services\Admin;

use App\Enums\CloudBackupStatus;
use App\Enums\CloudStorageKind;
use App\Models\CloudBackup;
use App\Services\CloudQuotaService;
use App\Services\CloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CloudBackupService
{
    public function __construct(
        protected CloudStorageService $storageService,
        protected CloudQuotaService $quotaService
    ) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $state = strtolower(trim((string) $request->query('state', 'all')));
        $search = trim((string) $request->query('q', ''));

        $query = CloudBackup::query()
            ->withTrashed()
            ->with('user')
            ->withCount('cloudEntities')
            ->latest('id');

        if ($state === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($state === 'deleted') {
            $query->onlyTrashed();
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like): void {
                $nested
                    ->where('uid', 'like', $like)
                    ->orWhere('resource_id', 'like', $like)
                    ->orWhere('user_id', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like): void {
                        $userQuery
                            ->where('email', 'like', $like)
                            ->orWhere('name', 'like', $like)
                            ->orWhere('uid', 'like', $like);
                    });
            });
        }

        return $query->paginate($limit)->appends($request->query());
    }

    public function findByUidOrFail(string $backupUid): CloudBackup
    {
        return CloudBackup::query()
            ->withTrashed()
            ->with([
                'user.cloudUsage',
                'cloudEntities' => fn ($query) => $query->withTrashed()->latest('id'),
            ])
            ->where('uid', $backupUid)
            ->firstOrFail();
    }

    /**
     * @return array{deleted_objects:int, deleted_entities:int, reclaimed_size:int}
     */
    public function deleteCloudBackup(CloudBackup $backup): array
    {
        $backup->loadMissing(['user', 'cloudEntities' => fn ($query) => $query->withTrashed()]);
        $entities = $backup->cloudEntities;
        $keys = $entities->pluck('key')->filter()->unique()->values();

        foreach ($keys as $key) {
            $this->assertBackupKey($key);

            if (! $this->storageService->deleteObject($key)) {
                throw new RuntimeException("Failed to delete backup object {$key}.");
            }
        }

        $reclaimedSize = (int) $entities->sum('expected_size');

        DB::transaction(function () use ($backup, $reclaimedSize): void {
            $lockedBackup = CloudBackup::query()
                ->withTrashed()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBackup->trashed()) {
                return;
            }

            $lockedBackup->load(['user', 'cloudEntities' => fn ($query) => $query->withTrashed()]);
            $lockedBackup->cloudEntities->each->forceDelete();
            $lockedBackup->update(['status' => CloudBackupStatus::DELETED]);

            if ($lockedBackup->user) {
                $this->quotaService->release($lockedBackup->user, CloudStorageKind::BACKUP, $reclaimedSize);
            }

            $lockedBackup->forceDelete();
        });

        return [
            'deleted_objects' => $keys->count(),
            'deleted_entities' => $entities->count(),
            'reclaimed_size' => $reclaimedSize,
        ];
    }

    private function assertBackupKey(string $key): void
    {
        $directory = trim((string) config('classer.cloudBackup.directory_key', 'cloud-backups'), '/');
        $configuredDirectory = trim((string) config('filesystems.disks.'.config('classer.userStorage.disk', 'user-storage').".directories.{$directory}", ''), '/');
        $prefix = $configuredDirectory !== '' ? $configuredDirectory : $directory;

        if (! str_starts_with($key, $prefix.'/')) {
            throw new RuntimeException("Unrecognized cloud backup object key: {$key}");
        }
    }
}
