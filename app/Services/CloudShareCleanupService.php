<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CloudShareCleanupService
{
    protected string $cloudShareDir;

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('CloudShareCleanupService');
        $this->cloudShareDir = Config::get('classer.cloudShare.directory', 'cloud-share');
    }

    /**
     * Resolve the S3 directory for a given cloud share based on its associated entities.
     *
     * @param  CloudShare  $share  The cloud share to resolve the directory for.
     * @return string|null The resolved directory path, or null if it cannot be determined.
     */
    public function resolveDirectory(CloudShare $share): ?string
    {
        $share->loadMissing('cloudEntities');

        if ($share->cloudEntities->isEmpty()) {
            $this->logger->warning('No entities to process', [
                'share_id' => $share->id,
            ]);

            return null;
        }

        $firstKey = (string) $share->cloudEntities->first()->key;
        $parts = explode('/', $firstKey, 3);

        if (count($parts) < 2) {
            $this->logger->error('Invalid key format', [
                'share_id' => $share->id,
                'key' => $firstKey,
            ]);

            return null;
        }

        $root = $parts[0];
        $dirKey = $parts[1];

        if ($root !== $this->cloudShareDir || $dirKey === '') {
            $this->logger->error('Unexpected cloud share directory structure', [
                'share_id' => $share->id,
                'key' => $firstKey,
                'expected_root' => $this->cloudShareDir,
                'actual_root' => $root,
            ]);

            return null;
        }

        return "{$this->cloudShareDir}/{$dirKey}";
    }

    /**
     * Determine if a given directory is protected and should not be deleted.
     *
     * @param  string  $directory  The directory to check.
     * @param  array  $extra  Additional directory names to consider as protected.
     * @return bool True if the directory is protected, false otherwise.
     */
    public function isProtected(string $directory, array $extra = []): bool
    {
        $protected = [null, '', '.', '..', $this->cloudShareDir];

        return in_array($directory, array_merge($protected, $extra), true);
    }

    /**
     * Delete a directory from S3 if it is not protected.
     *
     * @param  string  $directory  The directory to delete.
     * @return bool True if the directory was deleted, false if it was protected or deletion failed.
     */
    public function deleteDirectory(string $directory): bool
    {
        if ($this->isProtected($directory)) {
            $this->logger->warning('Refused to delete protected cloud share directory', [
                'directory' => $directory,
            ]);

            return false;
        }

        return Storage::disk('s3')->deleteDirectory($directory);
    }

    /**
     * Calculate the updated cloud usage for a user after reclaiming space from a cloud share.
     *
     * @param  User  $user  The user whose usage is being calculated.
     * @param  CloudShare  $cloudShare  The cloud share being cleaned up.
     * @return int The new total usage for the user after reclamation.
     *
     * @throws RuntimeException if required data is missing or invalid
     */
    public function calculateUpdatedUsage(User $user, CloudShare $cloudShare): int
    {
        $cloudShare->loadMissing('cloudEntities');
        $user->loadMissing('cloudUsage');

        if (! $user->cloudUsage) {
            $this->logger->error('Cloud usage record not found for user', [
                'user_id' => $user->id,
                'share_id' => $cloudShare->id,
            ]);

            throw new RuntimeException(sprintf(
                'Cloud usage record missing. User ID: %d, Share ID: %d',
                $user->id,
                $cloudShare->id
            ));
        }

        if ($cloudShare->cloudEntities->isEmpty()) {
            $this->logger->error('No cloud entities found for share', [
                'user_id' => $user->id,
                'share_id' => $cloudShare->id,
            ]);

            throw new RuntimeException(sprintf(
                'No entities found. User ID: %d, Share ID: %d',
                $user->id,
                $cloudShare->id
            ));
        }

        if ($cloudShare->cloudEntities->contains(fn ($entity): bool => ! isset($entity->size))) {
            $this->logger->error('One or more cloud entities missing size attribute', [
                'user_id' => $user->id,
                'share_id' => $cloudShare->id,
            ]);

            throw new RuntimeException(sprintf(
                'At least one entity is missing the size attribute. User ID: %d, Share ID: %d',
                $user->id,
                $cloudShare->id
            ));
        }

        $currentUsage = (int) $user->cloudUsage->total_usage;
        $reclaimedSize = (int) $cloudShare->cloudEntities->sum('size');
        $newUsage = $currentUsage - $reclaimedSize;

        if ($newUsage < 0) {
            $this->logger->warning('Reclaiming more space than available, setting usage to zero', [
                'user_id' => $user->id,
                'share_id' => $cloudShare->id,
                'reclaimed_size' => $reclaimedSize,
                'current_usage' => $currentUsage,
            ]);

            return 0;
        }

        return $newUsage;
    }

    /**
     * Finalize the cleanup of a cloud share by deleting associated S3 objects, removing database records, and updating user usage.
     *
     * @param  CloudShare  $cloudShare  The cloud share to finalize cleanup for.
     *
     * @throws RuntimeException if the cloud share is missing required relationships or if usage calculation fails
     */
    public function finalize(CloudShare $cloudShare): void
    {
        if (! $cloudShare->exists) {
            return;
        }

        DB::transaction(function () use ($cloudShare): void {
            $cloudShare->loadMissing(['cloudEntities', 'user.cloudUsage']);

            $user = $cloudShare->user;

            if (! $user instanceof User) {
                throw new RuntimeException(sprintf(
                    'Cloud share user missing. Share ID: %d',
                    $cloudShare->id
                ));
            }

            $newUsage = $this->calculateUpdatedUsage($user, $cloudShare);

            $cloudShare->cloudEntities->each->delete();

            $cloudShare->delete();

            $user->cloudUsage->total_usage = $newUsage;
            $user->cloudUsage->save();
        });
    }
}
