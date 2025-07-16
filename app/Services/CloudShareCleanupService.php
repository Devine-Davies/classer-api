<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;

class CloudShareCleanupService
{
    protected string $cloudShareDir;

    public function __construct(
        protected AppLogger $logger
    ) {
        $this->logger->setContext('CloudShareCleanupService');
        $this->cloudShareDir = Config::get('classer.cloud_share_directory', 'cloud-share');
    }

    /**
     * Determine the target directory for a given share.
     */
    public function resolveDirectory(CloudShare $share): ?string
    {
        $entities = collect($share->cloudEntities);

        if ($entities->isEmpty()) {
            $this->logger->warning('No entities to process', ['share_id' => $share->id]);
            return null;
        }

        $firstKey = $entities->first()->key;

        if (! Str::contains($firstKey, '/')) {
            $this->logger->error('Invalid key format', [
                'share_id' => $share->id,
                'key'      => $firstKey,
            ]);
            return null;
        }

        [$root, $dirKey] = explode('/', $firstKey, 3) + [2 => null];

        return "{$this->cloudShareDir}/{$dirKey}";
    }

    /**
     * Check if a directory is protected.
     */
    public function isProtected(string $directory, array $extra = []): bool
    {
        $protected = [null, '.', '..', $this->cloudShareDir];
        $protected = array_merge($protected, $extra);

        return in_array($directory, $protected, true);
    }

    /**
     * Delete the S3 directory; return false on failure.
     */
    public function deleteDirectory(string $directory): bool
    {
        $this->logger->info('Deleting S3 directory', ['directory' => $directory]);
        return Storage::disk('s3')->deleteDirectory($directory);
    }

    /**
     * Sum up the sizes in a collection of entities.
     */
    public function computeReclaimSize(Collection $entities): int
    {
        return $entities->sum('size');
    }

    /**
     * Adjust the user’s cloud usage record.
     */
    public function reclaimSpaceForUser(int $userId, int $size): void
    {
        $user = User::find($userId);

        if (! $user || ! $user->cloudUsage) {
            $this->logger->error('User or usage record missing', ['user_id' => $userId]);
            return;
        }

        $usage = $user->cloudUsage;
        $original = $usage->total_usage;

        if ($original < $size) {
            $this->logger->warning('Reclaiming more than current usage', [
                'user_id'        => $userId,
                'total_usage'    => $original,
                'size_to_remove' => $size,
            ]);
        }

        $usage->total_usage = max(0, $original - $size);
        $usage->save();
    }

    /**
     * Finalize cleanup: delete entities, delete share, reclaim usage.
     */
    public function finalizeCleanup(CloudShare $share, int $reclaimed): void
    {
        DB::transaction(function () use ($share, $reclaimed) {
            $share->cloudEntities->each->delete();
            $this->reclaimSpaceForUser($share->user_id, $reclaimed);
            $share->delete();
        });
    }
}