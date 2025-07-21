<?php

namespace App\Services;

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
        return Storage::disk('s3')->deleteDirectory($directory);
    }

    /**
     * Sum up the sizes in a collection of entities.
     * 
     * @throws \InvalidArgumentException
     */
    public function calculateNewStorageSize(User $user, CloudShare $cloudShare): int
    {
        $entities = collect($cloudShare->cloudEntities);

        // Ensure the entities collection is not empty
        if ($entities->isEmpty()) {
            throw new \InvalidArgumentException(
                sprintf('No entities found. User ID: %d, Share ID: %d', $user->id, $cloudShare->id)
            );
        }

        // Ensure all entities have a size attribute
        if ($entities->contains(
            fn($entity) => !isset($entity->size)
        )) {
            throw new \InvalidArgumentException(
                sprintf('At least one entity is missing the size attribute. User ID: %d, Share ID: %d', $user->id, $cloudShare->id)
            );
        }

        $currentUsage = $user->cloudUsage->total_usage;
        $reclaimedSize = $entities->sum('size');
        $newUsage = $currentUsage - $reclaimedSize;
        $isNegative = $newUsage < 0;

        ($isNegative) && $this->logger->warning('Reclaiming more space than available, setting to zero', [
            'user_id' => $user->id,
            'reclaimed_size' => abs($newUsage),
            'current_usage' => $user->cloudUsage->total_usage,
        ]);

        // Ensure we do not return negative usage
        return $isNegative ? 0 : $newUsage;
    }

    /**
     * Finalize cleanup: delete entities, delete share, reclaim usage.
     * 
     * @throws \RuntimeException
     */
    public function finalize(CloudShare $cloudShare): void
    {
        DB::transaction(function () use ($cloudShare) {
            // Reclaim space for the user
            $user = User::find($cloudShare->user_id);

            // Compute the total size of entities to reclaim
            $newUsage = $this->calculateNewStorageSize($user, $cloudShare);

            // Delete the CloudShare entities
            $cloudShare->cloudEntities->each->delete();

            // Delete the CloudShare record itself
            $cloudShare->delete();

            // Update the user's cloud usage
            $user->cloudUsage->total_usage = $newUsage;
            $user->cloudUsage->save();
        });
    }
}
