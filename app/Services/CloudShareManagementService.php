<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CloudShareManagementService
{
    protected string $cloudShareDir;

    public function __construct(
        protected AppLogger $logger,
        protected S3PresignService $presignService
    ) {
        $this->logger->setContext('CloudShareManagementService');
        $this->cloudShareDir = Config::get('classer.cloudShare.directory', 'cloud-share');
    }

    /**
     * List all cloud shares for a given user, including soft-deleted shares.
     *
     * @param  User  $user  The user whose cloud shares to list.
     * @return Collection  A collection of CloudShare models with their entities loaded.
     */
    public function listForUser(User $user): Collection
    {
        return CloudShare::where('user_id', $user->id)
            ->withTrashed()
            ->with('cloudEntities')
            ->latest()
            ->get();
    }

    /**
     * Create a new cloud share for a user with the given resource ID and entity payloads.
     *
     * @param  User  $user  The user for whom to create the cloud share.
     * @param  string  $resourceId  An identifier for the resource associated with the share.
     * @param  array  $entityPayloads  An array of payloads describing the entities to be included in the share.
     * @return CloudShare  The created CloudShare model instance with its entities loaded.
     *
     * @throws RuntimeException if entity payloads are empty or if cloud usage record is missing.
     */
    public function create(
        User $user,
        string $resourceId,
        array $entityPayloads
    ): CloudShare {
        if (empty($entityPayloads)) {
            throw new RuntimeException('Cannot create CloudShare without entity payloads.');
        }

        $user->loadMissing('cloudUsage');

        if (! $user->cloudUsage) {
            $this->logger->error('Cloud usage record not found for user', [
                'user_id' => $user->id,
            ]);

            throw new RuntimeException(sprintf(
                'Cloud usage record missing. User ID: %d',
                $user->id
            ));
        }

        $shareUid = (string) Str::uuid();

        /*
         * Generate presigned URLs outside the DB transaction.
         * This avoids holding database locks while making external S3 calls.
         */
        $uploadPayloads = $this->presignService->generateUrlsForShare(
            $shareUid,
            $entityPayloads
        );

        if (empty($uploadPayloads)) {
            $this->logger->error('Failed to generate upload payloads for CloudShare creation', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'entity_payload_count' => count($entityPayloads),
            ]);

            throw new RuntimeException(sprintf(
                'No upload payloads generated for CloudShare. User ID: %d, Resource ID: %s',
                $user->id,
                $resourceId
            ));
        }

        $totalSize = $this->calculatePayloadSize($uploadPayloads);

        return DB::transaction(function () use ($user, $resourceId, $shareUid, $uploadPayloads, $totalSize): CloudShare {
            $cloudShare = CloudShare::create([
                'uid' => $shareUid,
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'size' => $totalSize,
            ]);

            $cloudShare->cloudEntities()->createMany($uploadPayloads);

            $user->updateCloudUsage($totalSize);

            $this->logger->info('CloudShare created', [
                'user_id' => $user->id,
                'share_uid' => $cloudShare->uid,
                'resource_id' => $resourceId,
                'entity_count' => count($uploadPayloads),
                'total_size' => $totalSize,
            ]);

            return $cloudShare->load('cloudEntities');
        });
    }

    /**
     * Verify the integrity of a cloud share by comparing local and S3-reported sizes.
     *
     * @param  CloudShare  $share  The cloud share to verify.
     * @throws RuntimeException if verification fails.
     */
    public function verify(CloudShare $share): void
    {
        $share->loadMissing('cloudEntities');

        if ($share->cloudEntities->isEmpty()) {
            $this->logger->error('CloudShare verification failed for zero-entity share', [
                'share_uid' => $share->uid,
                'user_id' => $share->user_id,
            ]);

            throw new RuntimeException(sprintf(
                'CloudShare verification failed for user %d, share %s: no cloud entities found.',
                $share->user_id,
                $share->uid
            ));
        }

        $totalEntitySize = (int) $share->cloudEntities->sum('size');
        $s3ReportedSize = $this->calculateS3ReportedSize($share);

        $relativeTolerance = 0.05;
        $absoluteTolerance = 1 * 1024 * 1024;

        if ($totalEntitySize === 0) {
            if ($s3ReportedSize > $absoluteTolerance) {
                $this->logger->error('CloudShare verification failed for zero-size share', [
                    'share_uid' => $share->uid,
                    'user_id' => $share->user_id,
                    'local_size' => $totalEntitySize,
                    's3_size' => $s3ReportedSize,
                ]);

                throw new RuntimeException(sprintf(
                    'CloudShare verification failed for user %d, share %s: local total is 0B but S3 reports %dB.',
                    $share->user_id,
                    $share->uid,
                    $s3ReportedSize
                ));
            }

            return;
        }

        $allowedDelta = max(
            (int) ceil($totalEntitySize * $relativeTolerance),
            $absoluteTolerance
        );

        $upperBound = $totalEntitySize + $allowedDelta;
        $lowerBound = max(0, $totalEntitySize - $allowedDelta);

        if ($s3ReportedSize > $upperBound) {
            $this->throwVerificationException(
                $share,
                $s3ReportedSize,
                $totalEntitySize,
                $allowedDelta,
                'exceeds'
            );
        }

        if ($s3ReportedSize < $lowerBound) {
            $this->throwVerificationException(
                $share,
                $s3ReportedSize,
                $totalEntitySize,
                $allowedDelta,
                'is smaller than'
            );
        }

        $this->logger->info('CloudShare verification passed', [
            'share_uid' => $share->uid,
            'user_id' => $share->user_id,
            'local_size' => $totalEntitySize,
            's3_size' => $s3ReportedSize,
        ]);
    }

    /**
     * Sum payload entity sizes and guard against invalid negative totals.
     *
     * @param  array  $uploadPayloads  Presigned upload payload rows.
     * @return int  Total payload size in bytes.
     */
    protected function calculatePayloadSize(array $uploadPayloads): int
    {
        $totalSize = collect($uploadPayloads)->sum(
            fn (array $payload): int => (int) ($payload['size'] ?? 0)
        );

        if ($totalSize < 0) {
            $this->logger->warning('Calculated total size of CloudShare payload is negative', [
                'total_size' => $totalSize,
                'payload_count' => count($uploadPayloads),
            ]);

            throw new RuntimeException('CloudShare payload size cannot be negative.');
        }

        return (int) $totalSize;
    }

    /**
     * Get the S3 directory path for a given cloud share, unless it is protected.
     *
     * @param  CloudShare  $share  The cloud share for which to get the directory path.
     * @return string|null  The S3 directory path, or null if the directory is protected.
     */
    protected function calculateS3ReportedSize(CloudShare $share): int
    {
        return (int) $share->cloudEntities
            ->map(function ($entity): int {
                if (! filled($entity->key ?? null)) {
                    return 0;
                }

                return (int) ($this->presignService->getObjectMeta($entity->key)->size ?? 0);
            })
            ->sum();
    }

    /**
     * Get the S3 directory path for a given cloud share, unless it is protected.
     *
     * @param  CloudShare  $share  The cloud share for which to get the directory path.
     * @return string|null  The S3 directory path, or null if the directory is protected.
     */
    protected function throwVerificationException(
        CloudShare $share,
        int $s3ReportedSize,
        int $totalEntitySize,
        int $allowedDelta,
        string $comparison
    ): void {
        $diffBytes = abs($s3ReportedSize - $totalEntitySize);
        $diffPct = $totalEntitySize > 0
            ? ($diffBytes / $totalEntitySize) * 100
            : 0;

        $this->logger->error('CloudShare verification failed', [
            'share_uid' => $share->uid,
            'user_id' => $share->user_id,
            'local_size' => $totalEntitySize,
            's3_size' => $s3ReportedSize,
            'allowed_delta' => $allowedDelta,
            'diff_bytes' => $diffBytes,
            'diff_pct' => $diffPct,
            'comparison' => $comparison,
        ]);

        throw new RuntimeException(sprintf(
            'CloudShare verification failed for user %d, share %s: S3=%dB %s local=%dB by %dB (%.2f%%), tolerance=%dB.',
            $share->user_id,
            $share->uid,
            $s3ReportedSize,
            $comparison,
            $totalEntitySize,
            $diffBytes,
            $diffPct,
            $allowedDelta
        ));
    }
}