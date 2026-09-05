<?php

namespace App\Services\Admin;

use App\Enums\CloudStorageKind;
use App\Models\CloudShare;
use App\Services\CloudQuotaService;
use App\Services\CloudShareCleanupService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CloudShareService
{
    public function __construct(
        protected CloudShareCleanupService $cleanupService,
        protected CloudQuotaService $quotaService
    ) {}

    /**
     * Build paginated cloud share list for the admin table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $state = strtolower(trim((string) $request->query('state', 'all')));
        $search = trim((string) $request->query('q', ''));

        $query = CloudShare::query()
            ->withTrashed()
            ->with(['user'])
            ->withCount(['cloudEntities as entities_count' => function ($entityQuery): void {
                $entityQuery->withTrashed();
            }])
            ->withCount(['cloudEntities as verified_entities_count' => function ($entityQuery): void {
                $entityQuery->withTrashed()->whereNotNull('e_tag');
            }])
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

    /**
     * Find a cloud share by UID including related entities and user.
     */
    public function findByUidOrFail(string $cloudShareUid): CloudShare
    {
        return CloudShare::query()
            ->withTrashed()
            ->with([
                'user.cloudUsage',
                'cloudEntities' => function ($query): void {
                    $query->withTrashed()->orderByDesc('created_at');
                },
            ])
            ->where('uid', $cloudShareUid)
            ->firstOrFail();
    }

    /**
     * Delete cloud share data and S3 objects for an admin initiated cleanup.
     *
     * @return array{deleted_objects:int, deleted_entities:int, reclaimed_size:int}
     */
    public function deleteCloudShare(CloudShare $cloudShare): array
    {
        $cloudShare->loadMissing([
            'user.cloudUsage',
            'cloudEntities' => function ($query): void {
                $query->withTrashed();
            },
        ]);

        $entities = $cloudShare->cloudEntities;
        $deletedEntities = $entities->count();
        $reclaimedSize = (int) $entities->sum('expected_size');

        $keys = $entities
            ->pluck('key')
            ->filter(fn ($key): bool => filled($key))
            ->unique()
            ->values();

        if ($keys->isNotEmpty()) {
            $keysByDisk = $keys->groupBy(function (string $key): string {
                $disk = $this->cleanupService->resolveDiskForPath($key);

                if ($disk === null) {
                    throw new RuntimeException("Unrecognized cloud share object key: {$key}");
                }

                return $disk;
            });

            foreach ($keysByDisk as $disk => $diskKeys) {
                if (! Storage::disk($disk)->delete($diskKeys->all())) {
                    throw new RuntimeException("Failed to delete cloud share objects from disk: {$disk}");
                }
            }
        }

        DB::transaction(function () use ($cloudShare, $reclaimedSize): void {
            $cloudShare->loadMissing('user.cloudUsage');

            $cloudShare->cloudEntities()->withTrashed()->get()->each(function ($entity): void {
                $entity->forceDelete();
            });

            if ($cloudShare->user) {
                $this->quotaService->release(
                    $cloudShare->user,
                    CloudStorageKind::SHARE,
                    $reclaimedSize
                );
            }

            $cloudShare->forceDelete();
        });

        return [
            'deleted_objects' => $keys->count(),
            'deleted_entities' => $deletedEntities,
            'reclaimed_size' => $reclaimedSize,
        ];
    }
}
