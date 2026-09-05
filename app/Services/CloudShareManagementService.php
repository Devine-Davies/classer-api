<?php

namespace App\Services;

use App\Enums\CloudEntityStatus;
use App\Enums\CloudShareStatus;
use App\Enums\CloudStorageKind;
use App\Exceptions\InvalidCloudShareStateException;
use App\Jobs\CloudShare\CloudShareExpireUpload;
use App\Jobs\CloudShare\CloudShareVerifyUpload;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CloudShareManagementService
{
    public function __construct(
        protected AppLogger $logger,
        protected CloudStorageService $storageService,
        protected CloudQuotaService $quotaService
    ) {
        $this->logger->setContext('CloudShareManagementService');
    }

    /**
     * List all cloud shares for a given user, including soft-deleted shares.
     *
     * @param  User  $user  The user whose cloud shares to list.
     * @return Collection A collection of CloudShare models with their entities loaded.
     */
    public function listForUser(User $user): Collection
    {
        return CloudShare::where('user_id', $user->uid)
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
     * @return CloudShare The created CloudShare model instance with its entities loaded.
     *
     * @throws RuntimeException if entity payloads are empty or if cloud usage record is missing.
     */
    public function create(
        User $user,
        string $resourceId,
        array $entityPayloads
    ): CloudShare {
        $this->logger->info('Starting CloudShare creation flow', [
            'user_id' => $user->id,
            'user_uid' => $user->uid,
            'resource_id' => $resourceId,
            'entity_payload_count' => count($entityPayloads),
        ]);

        if (empty($entityPayloads)) {
            throw new RuntimeException('Cannot create CloudShare without entity payloads.');
        }

        $shareUid = (string) Str::uuid();
        $totalSize = $this->calculatePayloadSize($entityPayloads);

        $this->logger->info('Calculated cloud share payload size', [
            'user_id' => $user->id,
            'resource_id' => $resourceId,
            'share_uid' => $shareUid,
            'total_size' => $totalSize,
        ]);

        $cloudShare = DB::transaction(function () use ($user, $resourceId, $shareUid, $entityPayloads, $totalSize): CloudShare {
            $this->quotaService->reserve($user, CloudStorageKind::SHARE, $totalSize);

            $uploadPayloads = $this->generateUploadPayloads(
                $shareUid,
                $entityPayloads
            );

            if (empty($uploadPayloads)) {
                throw new RuntimeException(sprintf(
                    'No upload payloads generated for CloudShare. User ID: %d, Resource ID: %s',
                    $user->id,
                    $resourceId
                ));
            }

            $this->logger->info('Persisting CloudShare database records', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'share_uid' => $shareUid,
                'entity_count' => count($uploadPayloads),
                'total_size' => $totalSize,
            ]);

            $cloudShare = CloudShare::create([
                'uid' => $shareUid,
                'user_id' => $user->uid,
                'resource_id' => $resourceId,
                'expected_size' => $totalSize,
                'status' => CloudShareStatus::UPLOADING,
                'upload_expires_at' => now()->addMinutes(
                    (int) config('classer.cloudShare.uploadSessionTtlMinutes', 30)
                ),
                'expires_at' => $this->shareExpiresAt($user),
            ]);

            $cloudEntities = $cloudShare->cloudEntities()->createMany($uploadPayloads);

            $cloudEntities->each(function ($entity) use ($uploadPayloads): void {
                $payload = collect($uploadPayloads)->firstWhere('uid', $entity->uid);
                $entity->setAttribute('upload_url', $payload['upload_url'] ?? null);
            });
            $cloudShare->setRelation('cloudEntities', $cloudEntities);

            $this->logger->info('CloudShare created', [
                'user_id' => $user->id,
                'share_uid' => $cloudShare->uid,
                'resource_id' => $resourceId,
                'entity_count' => count($uploadPayloads),
                'total_size' => $totalSize,
            ]);

            return $cloudShare;
        });

        CloudShareExpireUpload::dispatch($cloudShare)
            ->onConnection('cloudshare')
            ->delay($cloudShare->upload_expires_at);

        return $cloudShare;
    }

    public function complete(User $user, CloudShare $share): CloudShare
    {
        if ($share->user_id !== $user->uid) {
            throw new AuthorizationException;
        }

        $shouldDispatchVerification = false;

        $share = DB::transaction(function () use ($share, &$shouldDispatchVerification): CloudShare {
            $lockedShare = CloudShare::query()
                ->whereKey($share->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedShare->status, [CloudShareStatus::VALIDATING, CloudShareStatus::ACTIVE], true)) {
                return $lockedShare;
            }

            if ($lockedShare->status !== CloudShareStatus::UPLOADING) {
                throw new InvalidCloudShareStateException(sprintf(
                    'Cloud share %s cannot be completed from status %s.',
                    $lockedShare->uid,
                    $lockedShare->status->value
                ));
            }

            $lockedShare->update([
                'status' => CloudShareStatus::VALIDATING,
                'completed_at' => now(),
            ]);
            $shouldDispatchVerification = true;

            return $lockedShare;
        });

        if ($shouldDispatchVerification) {
            CloudShareVerifyUpload::dispatch($share)
                ->onConnection('cloudshare');
        }

        return $share->fresh('cloudEntities');
    }

    /**
     * Verify the integrity of a cloud share by comparing local and S3-reported sizes.
     *
     * @param  CloudShare  $share  The cloud share to verify.
     *
     * @throws RuntimeException if verification fails.
     */
    public function verify(CloudShare $share): void
    {
        $share->refresh();

        if ($share->status === CloudShareStatus::ACTIVE) {
            return;
        }

        if ($share->status !== CloudShareStatus::VALIDATING) {
            throw new InvalidCloudShareStateException(sprintf(
                'Cloud share %s cannot be verified from status %s.',
                $share->uid,
                $share->status->value
            ));
        }

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

        $totalEntitySize = (int) $share->cloudEntities->sum('expected_size');
        $s3Verification = $this->calculateS3ReportedSizeAndEtags($share);
        $s3ReportedSize = (int) ($s3Verification['size'] ?? 0);
        $verifiedEntities = (int) ($s3Verification['verified_entities'] ?? 0);
        $scannedEntities = (int) ($s3Verification['scanned_entities'] ?? 0);

        $share->update(['actual_size' => $s3ReportedSize]);

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

            $this->syncVerifiedEntityMetadata($share, $s3Verification['entities'] ?? []);

            $this->markValidated($share);

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

        $this->syncVerifiedEntityMetadata($share, $s3Verification['entities'] ?? []);
        $this->markValidated($share);

        $this->logger->info('CloudShare verification passed', [
            'share_uid' => $share->uid,
            'user_id' => $share->user_id,
            'local_size' => $totalEntitySize,
            's3_size' => $s3ReportedSize,
            'scanned_entities' => $scannedEntities,
            'verified_entities' => $verifiedEntities,
        ]);
    }

    /**
     * Sum payload entity sizes and guard against invalid negative totals.
     *
     * @param  array  $uploadPayloads  Presigned upload payload rows.
     * @return int Total payload size in bytes.
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

    protected function shareExpiresAt(User $user): ?Carbon
    {
        $duration = (int) ($user->subscription?->plan?->duration ?? 0);

        return $duration > 0 ? now()->addSeconds($duration) : null;
    }

    protected function generateUploadPayloads(string $shareUid, array $entities): array
    {
        $directory = $this->cloudShareDirectory();
        $expires = (string) config('classer.cloudShare.putObjectTimeout', '+1 minute');

        return collect($entities)
            ->map(function (array $entity) use ($shareUid, $directory, $expires): array {
                $sourceFile = (string) $entity['sourceFile'];
                $contentType = (string) $entity['contentType'];
                $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
                $filename = (string) Str::uuid().($extension ? ".{$extension}" : '');
                $key = "{$directory}/{$shareUid}/{$filename}";

                return [
                    'uid' => (string) $entity['uid'],
                    'key' => $key,
                    'object_role' => $this->objectRole($contentType),
                    'original_name' => basename($sourceFile),
                    'mime_type' => $contentType,
                    'expected_size' => (int) $entity['size'],
                    'upload_url' => $this->storageService->createUploadUrl($key, $contentType, $expires),
                ];
            })
            ->values()
            ->all();
    }

    protected function cloudShareDirectory(): string
    {
        $disk = (string) config('classer.userStorage.disk', 'user-storage');
        $directoryKey = trim((string) config('classer.cloudShare.directory_key', 'cloud-share'));
        $mappedDirectory = (string) config("filesystems.disks.{$disk}.directories.{$directoryKey}", '');

        return trim(
            $mappedDirectory !== ''
                ? $mappedDirectory
                : (string) config('classer.cloudShare.directory', 'cloud-share'),
            '/'
        );
    }

    protected function objectRole(string $contentType): ?string
    {
        $roles = [
            'video/' => 'video',
            'image/' => 'thumbnail',
            'application/json' => 'metadata',
            'text/' => 'subtitle',
        ];

        return collect($roles)->first(
            fn (string $role, string $prefix): bool => str_starts_with($contentType, $prefix)
        );
    }

    protected function markValidated(CloudShare $share): void
    {
        $share->update([
            'status' => CloudShareStatus::ACTIVE,
            'validated_at' => now(),
        ]);
    }

    /**
     * Get the S3 directory path for a given cloud share, unless it is protected.
     *
     * @param  CloudShare  $share  The cloud share for which to get the directory path.
     * @return string|null The S3 directory path, or null if the directory is protected.
     */
    protected function calculateS3ReportedSizeAndEtags(CloudShare $share): array
    {
        $totalSize = 0;
        $scannedEntities = 0;
        $verifiedEntities = 0;
        $entities = [];

        foreach ($share->cloudEntities as $entity) {
            if (! filled($entity->key ?? null)) {
                continue;
            }

            $scannedEntities++;
            $meta = $this->storageService->headObject($entity->key);
            $actualSize = (int) ($meta->size ?? 0);
            $totalSize += $actualSize;

            $resolvedEtag = isset($meta->e_tag)
                ? trim((string) $meta->e_tag)
                : null;

            if ($resolvedEtag !== null && $resolvedEtag !== '') {
                $verifiedEntities++;
            }

            $entities[$entity->getKey()] = [
                'actual_size' => $actualSize,
                'e_tag' => $resolvedEtag ?: null,
            ];
        }

        return [
            'size' => $totalSize,
            'scanned_entities' => $scannedEntities,
            'verified_entities' => $verifiedEntities,
            'entities' => $entities,
        ];
    }

    /**
     * Persist S3 metadata after the complete share verification has passed.
     */
    protected function syncVerifiedEntityMetadata(CloudShare $share, array $metadata): void
    {
        foreach ($share->cloudEntities as $entity) {
            $entityKey = $entity->getKey();

            if (! array_key_exists($entityKey, $metadata)) {
                continue;
            }

            $entity->update([
                'actual_size' => $metadata[$entityKey]['actual_size'],
                'e_tag' => $metadata[$entityKey]['e_tag'],
                'status' => CloudEntityStatus::ACTIVE,
                'uploaded_at' => $entity->uploaded_at ?? now(),
                'validated_at' => now(),
            ]);
        }
    }

    /**
     * Get the S3 directory path for a given cloud share, unless it is protected.
     *
     * @param  CloudShare  $share  The cloud share for which to get the directory path.
     * @return string|null The S3 directory path, or null if the directory is protected.
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
