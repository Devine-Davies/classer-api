<?php

namespace App\Services;

use App\Enums\CloudBackupStatus;
use App\Enums\CloudEntityStatus;
use App\Enums\CloudStorageKind;
use App\Exceptions\InvalidCloudBackupStateException;
use App\Jobs\CloudBackup\CloudBackupVerifyUpload;
use App\Models\CloudBackup;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CloudBackupManagementService
{
    public function __construct(
        protected CloudStorageService $storageService,
        protected CloudQuotaService $quotaService
    ) {}

    /**
     * List all cloud backups for a given user.
     *
     * @description List all cloud backups for a given user.
     */
    public function listForUser(User $user): Collection
    {
        return CloudBackup::query()
            ->where('user_id', $user->uid)
            ->with('cloudEntities')
            ->latest()
            ->get();
    }

    /**
     * Create a new cloud backup for a given user with the specified entities.
     *
     * @description Create a new cloud backup for a given user with the specified entities.
     */
    public function create(User $user, string $resourceId, array $entities): CloudBackup
    {
        if ($entities === []) {
            throw new RuntimeException('Cannot create a cloud backup without entities.');
        }

        $backupUid = (string) Str::uuid();
        $totalSize = (int) collect($entities)->sum(
            fn (array $entity): int => (int) ($entity['size'] ?? 0)
        );

        if ($totalSize <= 0) {
            throw new RuntimeException('Cloud backup size must be greater than zero.');
        }

        $backup = DB::transaction(function () use ($user, $resourceId, $entities, $backupUid, $totalSize): CloudBackup {
            $this->quotaService->reserve($user, CloudStorageKind::BACKUP, $totalSize);

            $backup = CloudBackup::create([
                'uid' => $backupUid,
                'user_id' => $user->uid,
                'resource_id' => $resourceId,
                'expected_size' => $totalSize,
                'status' => CloudBackupStatus::UPLOADING,
            ]);
            $payloads = $this->generateUploadPayloads($backupUid, $entities);
            $cloudEntities = $backup->cloudEntities()->createMany($payloads);

            $cloudEntities->each(function ($entity) use ($payloads): void {
                $payload = collect($payloads)->firstWhere('uid', $entity->uid);
                $entity->setAttribute('upload_url', $payload['upload_url'] ?? null);
            });
            $backup->setRelation('cloudEntities', $cloudEntities);

            return $backup;
        });

        return $backup;
    }

    /**
     * Mark a cloud backup as complete and initiate verification.
     *
     * @description This method updates the status of the cloud backup to VALIDATING,
     *              sets the completed_at timestamp, and dispatches a verification job.
     */
    public function complete(User $user, CloudBackup $backup): CloudBackup
    {
        $this->authorize($user, $backup);
        $shouldDispatch = false;

        $backup = DB::transaction(function () use ($backup, &$shouldDispatch): CloudBackup {
            $lockedBackup = CloudBackup::query()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedBackup->status, [CloudBackupStatus::VALIDATING, CloudBackupStatus::ACTIVE], true)) {
                return $lockedBackup;
            }

            if ($lockedBackup->status !== CloudBackupStatus::UPLOADING) {
                throw $this->invalidState($lockedBackup, 'completed');
            }

            $lockedBackup->update([
                'status' => CloudBackupStatus::VALIDATING,
                'completed_at' => now(),
            ]);
            $shouldDispatch = true;

            return $lockedBackup;
        });

        if ($shouldDispatch) {
            CloudBackupVerifyUpload::dispatch($backup)->onConnection('cloudbackup');
        }

        return $backup->fresh('cloudEntities');
    }

    /**
     * Verify the integrity of a cloud backup.
     *
     * @description This method checks the status of the cloud backup, validates the sizes of all cloud entities,
     *              and updates the backup and entity statuses accordingly.
     */
    public function verify(CloudBackup $backup): void
    {
        $backup->refresh();

        if ($backup->status === CloudBackupStatus::ACTIVE) {
            return;
        }

        if ($backup->status !== CloudBackupStatus::VALIDATING) {
            throw $this->invalidState($backup, 'verified');
        }

        $backup->load('cloudEntities');

        if ($backup->cloudEntities->isEmpty()) {
            throw new RuntimeException("Cloud backup {$backup->uid} has no cloud entities.");
        }

        $metadata = [];
        $actualSize = 0;

        foreach ($backup->cloudEntities as $entity) {
            $object = $this->storageService->headObject($entity->key);
            $entitySize = (int) ($object->size ?? -1);

            if ($entitySize !== (int) $entity->expected_size) {
                throw new RuntimeException(sprintf(
                    'Cloud backup entity %s size mismatch: expected %d bytes, got %d bytes.',
                    $entity->uid,
                    $entity->expected_size,
                    $entitySize
                ));
            }

            $actualSize += $entitySize;
            $metadata[$entity->getKey()] = [
                'actual_size' => $entitySize,
                'e_tag' => $object->e_tag ?? null,
            ];
        }

        DB::transaction(function () use ($backup, $metadata, $actualSize): void {
            $lockedBackup = CloudBackup::query()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBackup->status === CloudBackupStatus::ACTIVE) {
                return;
            }

            if ($lockedBackup->status !== CloudBackupStatus::VALIDATING) {
                throw $this->invalidState($lockedBackup, 'verified');
            }

            foreach ($lockedBackup->cloudEntities as $entity) {
                $entityMetadata = $metadata[$entity->getKey()];
                $entity->update([
                    'actual_size' => $entityMetadata['actual_size'],
                    'e_tag' => $entityMetadata['e_tag'],
                    'status' => CloudEntityStatus::ACTIVE,
                    'uploaded_at' => $entity->uploaded_at ?? now(),
                    'validated_at' => now(),
                ]);
            }

            $lockedBackup->update([
                'actual_size' => $actualSize,
                'status' => CloudBackupStatus::ACTIVE,
                'validated_at' => now(),
            ]);
        });
    }

    /**
     * Restore a cloud backup for a given user.
     *
     * @description This method locks the backup for update, checks its status,
     *              generates download URLs for all cloud entities, and updates the backup status.
     */
    public function restore(User $user, CloudBackup $backup): array
    {
        $this->authorize($user, $backup);

        $backup = DB::transaction(function () use ($backup): CloudBackup {
            $lockedBackup = CloudBackup::query()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBackup->status !== CloudBackupStatus::ACTIVE) {
                throw $this->invalidState($lockedBackup, 'restored');
            }

            $lockedBackup->update(['status' => CloudBackupStatus::RESTORING]);

            return $lockedBackup->load('cloudEntities');
        });

        try {
            $expires = (string) config('classer.cloudBackup.getObjectTimeout', '+15 minutes');
            $manifest = $backup->cloudEntities->map(fn ($entity): array => [
                'uid' => $entity->uid,
                'role' => $entity->object_role?->value,
                'originalName' => $entity->original_name,
                'contentType' => $entity->mime_type,
                'size' => $entity->actual_size,
                'downloadUrl' => $this->storageService->createDownloadUrl($entity->key, $expires),
            ])->values()->all();

            $backup->update([
                'status' => CloudBackupStatus::ACTIVE,
                'last_restored_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            CloudBackup::query()
                ->whereKey($backup->getKey())
                ->where('status', CloudBackupStatus::RESTORING->value)
                ->update(['status' => CloudBackupStatus::ACTIVE->value]);

            throw $exception;
        }

        return $manifest;
    }

    /**
     * Delete a cloud backup for a given user.
     *
     * @description This method locks the backup for update, checks its status,
     *              deletes all associated cloud entities, updates the backup and user usage,
     *              and finally deletes the backup record.
     */
    public function delete(User $user, CloudBackup $backup): void
    {
        $this->authorize($user, $backup);

        $backup = DB::transaction(function () use ($backup): CloudBackup {
            $lockedBackup = CloudBackup::query()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBackup->status === CloudBackupStatus::RESTORING) {
                throw $this->invalidState($lockedBackup, 'deleted');
            }

            if ($lockedBackup->status !== CloudBackupStatus::SCHEDULED_FOR_DELETION) {
                $lockedBackup->update(['status' => CloudBackupStatus::SCHEDULED_FOR_DELETION]);
            }

            return $lockedBackup->load('cloudEntities');
        });

        foreach ($backup->cloudEntities as $entity) {
            if (! $this->storageService->deleteObject($entity->key)) {
                throw new RuntimeException("Failed to delete backup object {$entity->key}.");
            }
        }

        DB::transaction(function () use ($backup): void {
            $lockedBackup = CloudBackup::query()
                ->whereKey($backup->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedBackup || $lockedBackup->status !== CloudBackupStatus::SCHEDULED_FOR_DELETION) {
                return;
            }

            $lockedBackup->load(['cloudEntities', 'user']);
            $lockedBackup->cloudEntities->each->delete();
            $lockedBackup->update(['status' => CloudBackupStatus::DELETED]);
            $lockedBackup->delete();
            $this->quotaService->release(
                $lockedBackup->user,
                CloudStorageKind::BACKUP,
                (int) $lockedBackup->expected_size
            );
        });
    }

    /**
     * Generate upload payloads for cloud backup entities.
     *
     * @description This method creates the necessary payloads for uploading cloud backup entities,
     *              including generating unique keys, determining object roles, and creating upload URLs.
     */
    protected function generateUploadPayloads(string $backupUid, array $entities): array
    {
        $directory = $this->cloudBackupDirectory();
        $expires = (string) config('classer.cloudBackup.putObjectTimeout', '+5 minutes');

        return collect($entities)->map(function (array $entity) use ($backupUid, $directory, $expires): array {
            $sourceFile = (string) $entity['sourceFile'];
            $contentType = (string) $entity['contentType'];
            $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
            $filename = (string) Str::uuid().($extension ? ".{$extension}" : '');
            $key = "{$directory}/{$backupUid}/{$filename}";

            return [
                'uid' => (string) $entity['uid'],
                'key' => $key,
                'object_role' => $this->objectRole($contentType),
                'original_name' => basename($sourceFile),
                'mime_type' => $contentType,
                'expected_size' => (int) $entity['size'],
                'status' => CloudEntityStatus::UPLOADING,
                'upload_url' => $this->storageService->createUploadUrl($key, $contentType, $expires),
            ];
        })->values()->all();
    }

    /**
     * Get the directory path for storing cloud backups.
     *
     * @description This method retrieves the configured directory for cloud backups,
     *              falling back to a default value if not set.
     */
    protected function cloudBackupDirectory(): string
    {
        $disk = (string) config('classer.userStorage.disk', 'user-storage');
        $directoryKey = (string) config('classer.cloudBackup.directory_key', 'cloud-backups');
        $mappedDirectory = (string) config("filesystems.disks.{$disk}.directories.{$directoryKey}", '');

        return trim($mappedDirectory !== '' ? $mappedDirectory : 'cloud-backups', '/');
    }

    /**
     * Determine the object role based on the content type.
     *
     * @description This method maps content types to specific object roles,
     *              such as video, thumbnail, metadata, or subtitle.
     */
    protected function objectRole(string $contentType): ?string
    {
        return collect([
            'video/' => 'video',
            'image/' => 'thumbnail',
            'application/json' => 'metadata',
            'text/' => 'subtitle',
        ])->first(fn (string $role, string $prefix): bool => str_starts_with($contentType, $prefix));
    }

    /**
     * Authorize a user for a specific cloud backup.
     *
     * @description This method checks if the given user is authorized to access the specified cloud backup.
     */
    protected function authorize(User $user, CloudBackup $backup): void
    {
        if ($backup->user_id !== $user->uid) {
            throw new AuthorizationException;
        }
    }

    /**
     * Generate an exception for an invalid cloud backup state.
     *
     * @description This method creates an InvalidCloudBackupStateException with a message indicating
     *              that the specified action cannot be performed on the cloud backup from its current status.
     */
    protected function invalidState(CloudBackup $backup, string $action): InvalidCloudBackupStateException
    {
        return new InvalidCloudBackupStateException(sprintf(
            'Cloud backup %s cannot be %s from status %s.',
            $backup->uid,
            $action,
            $backup->status->name
        ));
    }
}
