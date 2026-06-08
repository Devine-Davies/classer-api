<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CloudShareCleanupService
{
    protected string $cloudShareDir;

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('CloudShareCleanupService');
        $this->cloudShareDir = Config::get('classer.cloudShare.directory', 'cloud-share');
    }

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

    public function isProtected(string $directory, array $extra = []): bool
    {
        $protected = [null, '', '.', '..', $this->cloudShareDir];

        return in_array($directory, array_merge($protected, $extra), true);
    }

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