# Cloud Backup Domain

Cloud Backup stores long-lived user media in the shared user-storage bucket. It reuses `CloudEntity` for object metadata and `CloudStorageService` for S3 operations, but owns its lifecycle independently from Cloud Share.

## API

- `POST /api/cloud-backups` creates an upload session and returns transient signed PUT URLs.
- `GET /api/cloud-backups` lists the authenticated user's cloud backups.
- `GET /api/cloud-backups/{uid}` returns one owned cloud backup.
- `POST /api/cloud-backups/{uid}/complete` idempotently queues verification.
- `POST /api/cloud-backups/{uid}/restore` returns a transient signed download manifest for an active cloud backup.
- `DELETE /api/cloud-backups/{uid}` deletes its objects and releases reserved storage quota.

All routes require an authenticated, verified user with an active cloud-storage subscription.

## Lifecycle

Uploads transition from `UPLOADING` to `VALIDATING` and then `ACTIVE`. Completion and successful verification are idempotent. Verification uses S3 `HeadObject` and requires each entity's actual size to exactly match its expected size. ETag is recorded as storage metadata and is not treated as a cryptographic checksum.

Restore temporarily claims `RESTORING` while generating fresh signed GET URLs, then returns the backup to `ACTIVE` and records `last_restored_at`.

Deletion first claims `SCHEDULED_FOR_DELETION`, deletes every owned S3 object, and only then soft-deletes database records and releases quota. A failed object deletion leaves the backup claimed and retryable without releasing quota.

## Storage And Quota

Backup keys use the `cloud-backups` directory mapping on `classer.userStorage.disk`:

```text
classermedia.com/cloud-backups/{backupUid}/{generatedUuid}.{extension}
```

Backup and Cloud Share currently consume the same `UserCloudUsage.total_usage` ledger and plan quota because both occupy the same user-storage capacity. A separate backup allowance requires an explicit billing capability and separate usage ledger before the middleware or service should distinguish it.

Signed upload and download URLs are transient and never persisted. SHA-256 verification remains a separate future contract; size verification and ETag recording must not be described as checksum verification.