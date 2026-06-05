# Cloud Share Presign Flow

This document explains how the cloud-share presign flow works, including subscription and usage checks.

## Endpoint

Current registered API route:

- `POST /api/cloud/share` -> create cloud share and return presigned URLs

Related route:

- `GET /api/user/cloud/share` -> list user cloud shares

Note:

- Some code comments/tests still reference `/api/cloud/share/presign`.
- The currently registered create route is `/api/cloud/share`.

## Auth and Middleware

The create endpoint is protected by:

- `auth:sanctum`
- `abilities:user`
- `UserAccount` middleware
- `has:subscription,cloudStorage` middleware

This means the caller must:

- Be authenticated with a valid user token.
- Have an active subscription.
- Have cloud storage access based on subscription quota vs usage.

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
   - `CloudShareVerifyUpload` on queue `verify` (delayed by `classer.cloudShare.verifyDelay`).
   - `CloudShareExpireUpload` on queue `expire` (delayed by `classer.cloudShare.getObjectTimeout`).
8. API responds `201` with `CloudShareResource` including entities.

## Subscription and Cloud Usage Model

### Subscription path

- `User` -> `subscription()` -> `UserSubscription` (active only)
- `UserSubscription` -> `type()` -> `Subscription`
- `Subscription.quota` is the storage limit in bytes

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

- Cloud share metadata (`uid`, `resource_id`, `size`, timestamps)
- `entities[]` with:
  - `uid`
  - `type`
  - `size`
  - `upload_url`
  - `e_tag`

## Related PHP Files

Primary files:

- `app/Http/Controllers/Api/CloudShareController.php`
- `app/Models/UserCloudUsage.php`
- `app/Models/UserSubscription.php`

Other important files:

- `routes/api.php`
- `app/Http/Requests/CloudShareCreateRequest.php`
- `app/Http/Middleware/Has.php`
- `app/Models/User.php`
- `app/Models/Subscription.php`
- `app/Models/CloudShare.php`
- `app/Models/CloudEntity.php`
- `app/Http/Resources/CloudShareResource.php`
- `app/Http/Resources/CloudEntityResource.php`
- `app/Services/CloudShareManagementService.php`
- `app/Services/S3PresignService.php`
- `app/Services/CloudShareCleanupService.php`
- `app/Jobs/CloudShareVerifyUpload.php`
- `app/Jobs/CloudShareExpireUpload.php`
- `config/classer.php`

Related migrations:

- `database/migrations/2013_10_11_165450_create_subscriptions_table.php`
- `database/migrations/2014_10_12_000000_create_user_subscriptions_table.php`
- `database/migrations/2014_10_12_000100_create_users_cloud_usage_table.php`
- `database/migrations/2023_12_23_144342_create_cloud_share_table.php`
- `database/migrations/2023_12_23_144342_create_cloud_entities_table.php`

## Operational Notes

- The create endpoint only generates URLs and records metadata. It does not upload file bytes itself.
- Verification and expiry behavior depends on queue workers processing `verify` and `expire` queues.
- Scheduler defaults for cloud-share queues are defined in `config/classer.php` via `classer.scheduler`.

## Known Alignment Gaps To Be Aware Of

- Route naming mismatch: tests/comments mention `/api/cloud/share/presign`, but active route is `/api/cloud/share`.
- Usage field consistency should be reviewed in quota checks (`total` vs `total_usage`) to ensure checks always use the same field.
