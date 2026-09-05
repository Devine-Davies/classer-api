<?php

namespace App\Services;

use App\Enums\CloudShareStatus;
use App\Enums\CloudStorageKind;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CloudShareCleanupService
{
    protected string $cloudShareDisk;

    protected string $cloudShareDir;

    public function __construct(
        protected AppLogger $logger,
        protected CloudStorageService $storageService,
        protected CloudQuotaService $quotaService
    ) {
        $this->logger->setContext('CloudShareCleanupService');

        $this->cloudShareDisk = (string) Config::get('classer.userStorage.disk', 'user-storage');
        $configuredDisks = (array) Config::get('filesystems.disks', []);

        if (! array_key_exists($this->cloudShareDisk, $configuredDisks)) {
            throw new RuntimeException("Cloud Share filesystem disk is not configured: {$this->cloudShareDisk}");
        }

        $cloudShareDirectoryKey = trim((string) Config::get('classer.cloudShare.directory_key', 'cloud-share'));
        $mappedCloudShareDir = (string) Config::get("filesystems.disks.{$this->cloudShareDisk}.directories.{$cloudShareDirectoryKey}", '');
        $this->cloudShareDir = trim($mappedCloudShareDir !== '' ? $mappedCloudShareDir : (string) Config::get('classer.cloudShare.directory', 'cloud-share'), '/');
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

        $firstKey = trim((string) $share->cloudEntities->first()->key, '/');
        $prefix = $this->cloudShareDir.'/';

        if (str_starts_with($firstKey, $prefix)) {
            $relativeKey = substr($firstKey, strlen($prefix));
            $shareUid = explode('/', $relativeKey, 2)[0];

            if ($shareUid !== '') {
                return $this->cloudShareDir.'/'.$shareUid;
            }
        }

        $this->logger->error('Unexpected cloud share directory structure', [
            'share_id' => $share->id,
            'key' => $firstKey,
            'expected_root' => $this->cloudShareDir,
        ]);

        return null;
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

        if ($this->resolveDiskForPath($directory) === null) {
            $this->logger->error('Refused to delete unrecognized cloud share directory', [
                'directory' => $directory,
            ]);

            return false;
        }

        return $this->storageService->deleteDirectory($directory);
    }

    public function resolveDiskForPath(string $path): ?string
    {
        $normalizedPath = trim($path, '/');

        if ($normalizedPath === $this->cloudShareDir || str_starts_with($normalizedPath, $this->cloudShareDir.'/')) {
            return $this->cloudShareDisk;
        }

        return null;
    }

    public function claimExpiredUpload(CloudShare $cloudShare): ?CloudShare
    {
        return DB::transaction(function () use ($cloudShare): ?CloudShare {
            $share = CloudShare::query()
                ->whereKey($cloudShare->getKey())
                ->lockForUpdate()
                ->first();

            if (! $share) {
                return null;
            }

            if ($share->status === CloudShareStatus::CLEANING) {
                return $share;
            }

            if ($share->status !== CloudShareStatus::UPLOADING) {
                return null;
            }

            if ($share->upload_expires_at === null || $share->upload_expires_at->isFuture()) {
                return null;
            }

            $share->update(['status' => CloudShareStatus::CLEANING]);

            return $share;
        });
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
        DB::transaction(function () use ($cloudShare): void {
            $lockedShare = CloudShare::query()
                ->whereKey($cloudShare->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedShare || $lockedShare->status !== CloudShareStatus::CLEANING) {
                return;
            }

            $lockedShare->load(['cloudEntities', 'user']);

            $user = $lockedShare->user;

            if (! $user instanceof User) {
                throw new RuntimeException(sprintf(
                    'Cloud share user missing. Share ID: %d',
                    $lockedShare->id
                ));
            }

            $reclaimedSize = (int) $lockedShare->cloudEntities->sum('expected_size');

            $lockedShare->cloudEntities->each->delete();

            $lockedShare->delete();

            $this->quotaService->release($user, CloudStorageKind::SHARE, $reclaimedSize);
        });
    }
}
