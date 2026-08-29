# Cloud Share Presign Flow

This document explains how the Cloud Share presign flow works, including authentication, quota checks, storage, verification, and expiry.

## Endpoint

Current registered API route:

- `POST /api/cloud/share` -> create cloud share and return presigned URLs

Related route:

- `GET /api/user/cloud/share` -> list user cloud shares

The active create route is `/api/cloud/share`. A stale route comment and the legacy `tests/Feature/CloudShareTest.php` still reference `/api/cloud/share/presign`, but that endpoint is not registered.

## Auth and Middleware

The create endpoint is protected by:

- `auth:sanctum`
- `abilities:user`
- `UserAccount` middleware
- `has:subscription,cloudStorage` middleware

This means the caller must:

- Be authenticated with a valid user token.
- Have an active subscription.
- Have current cloud usage less than or equal to the subscription plan quota.

The middleware checks existing usage. The controller separately checks whether the complete requested upload fits in the remaining quota.

## Request Payload

Validated by `CloudShareCreateRequest`:

- `resourceId` (string, required)
- `entities` (array, required, min 1)
- `entities[].uid` (string, required)
- `entities[].sourceFile` (string, required)
- `entities[].contentType` (string, required)
- `entities[].size` (integer, required, min 1)

## End-to-End Flow

1. Request hits `CloudShareController@create`.
2. Payload is validated.
3. Total requested upload size is calculated from `entities[].size`.
4. User quota gate is checked via `User::canUpload($sizeSum)`.
5. If quota is exceeded, API returns `403` with remaining/attempted size details.
6. If allowed, `CloudShareManagementService::create(...)` is called:
   - Generates a share UID.
   - Generates S3 presigned `PutObject` and `GetObject` URLs using `S3PresignService`.
   - Creates a `cloud_share` row.
   - Creates related `cloud_entities` rows.
   - Increments `user_cloud_usages.total_usage`.
7. Two async jobs are dispatched:
   - `CloudShareVerifyUpload` on the `cloudshare` connection and `verify` queue, delayed by `classer.cloudShare.verifyDelay`.
   - `CloudShareExpireUpload` on the `cloudshare` connection and `expire` queue, delayed by `classer.cloudShare.expireAfter`.
8. API responds `201` with `CloudShareResource` including entities.

If creation, presign generation, or lifecycle job dispatch throws, the API returns `500` with `Failed to generate presigned URLs.` Because the database transaction finishes before jobs are dispatched, a dispatch failure can occur after the share records have been created.

## Subscription and Cloud Usage Model

### Subscription path

- `User` -> `subscription()` -> `UserSubscription` (active only)
- `UserSubscription` -> `plan()` -> `Plan`
- `Plan.quota` is the storage limit in bytes

### Cloud usage path

- `User` -> `cloudUsage()` -> `UserCloudUsage`
- `UserCloudUsage.total_usage` stores current used bytes

### Methods involved

- `User::canUpload($uploadSize)`
- `User::remainingStorage()`
- `User::updateCloudUsage(int $size)`
- Middleware `Has::hasCloudStorage(...)`

## Response Shape (Create)

Resource includes:

- Cloud share metadata: `id`, `uid`, `userId`, `resourceId`, `size`, `deletedAt`, `createdAt`, and `updatedAt`
- `entities[]` with:
  - `uid`
  - `type`
  - `size`
   - `uploadUrl`
   - `eTag`

The stored entity also has `key`, `public_url`, and `expires_at`, but `CloudEntityResource` does not expose those fields.

## S3 Storage

Cloud Share storage is selected by `classer.cloudShare.disk`, which defaults to the `user-storage` filesystem disk.
The configured disk must exist in `filesystems.disks`; an unknown disk causes initialization to fail rather than falling back to `s3`.

The object prefix is resolved in this order:

1. Read `classer.cloudShare.directory_key` (default `cloud-share`).
2. Look up that key under `filesystems.disks.<disk>.directories`.
3. Fall back to `classer.cloudShare.directory` (default `cloud-share`) when no mapping exists.

With the default configuration, new object keys have this format:

```text
classermedia.com/cloud-share/{shareUid}/{generatedUuid}.{extension}
```

The generated filename preserves the source file extension, if present. Upload URLs use the entity's declared content type. Put and Get URL expiration are controlled by `classer.cloudShare.putObjectTimeout` and `classer.cloudShare.getObjectTimeout`.

## Verification and Expiry

- Verification calls S3 `HeadObject` for each stored entity key, records available ETags, and compares reported object sizes with the stored total.
- Expiry resolves the share directory from its first entity key, rejects protected or unknown paths, deletes that directory from the configured Cloud Share disk, soft-deletes the related Cloud Share and entity records, and decrements `user_cloud_usages.total_usage` without allowing a negative result.

## Related PHP Files

Primary files:

- `app/Http/Controllers/Api/CloudShareController.php`
- `app/Services/CloudShareManagementService.php`
- `app/Services/S3PresignService.php`
- `app/Services/CloudShareCleanupService.php`
- `app/Models/UserCloudUsage.php`
- `app/Models/UserSubscription.php`

Other important files:

- `routes/api.php`
- `app/Http/Requests/CloudShareCreateRequest.php`
- `app/Http/Middleware/Has.php`
- `app/Models/User.php`
- `app/Models/Plan.php`
- `app/Models/CloudShare.php`
- `app/Models/CloudEntity.php`
- `app/Http/Resources/CloudShareResource.php`
- `app/Http/Resources/CloudEntityResource.php`
- `app/Jobs/CloudShare/CloudShareVerifyUpload.php`
- `app/Jobs/CloudShare/CloudShareExpireUpload.php`
- `config/classer.php`
- `config/filesystems.php`
- `config/queue.php`

Related migrations:

- `database/migrations/2014_10_12_000000_create_user_subscriptions_table.php`
- `database/migrations/2014_10_12_000100_create_users_cloud_usage_table.php`
- `database/migrations/2023_12_23_144342_create_cloud_share_table.php`
- `database/migrations/2023_12_23_144342_create_cloud_entities_table.php`
- `database/migrations/2025_07_18_025003_create_cloud_share_jobs_table.php`
- `database/migrations/2026_06_10_000001_migrate_cloud_share_user_id_to_uuid.php`

## Operational Notes

- The create endpoint only generates URLs and records metadata. It does not upload file bytes itself.
- Verification and expiry behavior depends on queue workers processing `verify` and `expire` queues.
- Scheduler defaults for cloud-share queues are defined in `config/classer.php` via `classer.scheduler`.
- The `cloudshare` queue connection uses the `cloud_share_jobs` database table.
- The scheduler starts stop-when-empty workers periodically; the Docker Compose configuration also defines persistent `verify` and `expire` workers.

## Known Alignment Gaps To Be Aware Of

- `routes/api.php` still labels the create route as `/cloud/share/presign` in a comment even though the registered endpoint is `/api/cloud/share`.
- `tests/Feature/CloudShareTest.php` targets the stale `/api/cloud/share/presign` URL and contains no assertions, so it is not effective coverage for the current endpoint.
- Both `s3` and `user-storage` currently use `AWS_URL`. If those buckets require different public hosts or CDNs, `user-storage` needs a separate URL environment variable.
