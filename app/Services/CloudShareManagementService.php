<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\User;
use App\Models\CloudShare;
use App\Services\S3PresignService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CloudShareManagementService
{
    protected string $cloudShareDir;
    protected int $expireAfter;

    public function __construct(
        protected AppLogger $logger,
        protected S3PresignService $presignService
    ) {
        $logger->setContext('CloudShareManagementService');
        $this->cloudShareDir  = Config::get('classer.cloud_share_directory', 'cloud-share');
        $this->expireAfter    = Config::get('classer.cloud_share_expire_after', 604800);
    }

    /**
     * List all active (and trashed) shares for a user.
     */
    public function listForUser(User $user): Collection
    {
        return CloudShare::where('user_id', $user->id)
            ->whereNotNull('expires_at')
            ->withTrashed()
            ->get();
    }

    /**
     * Create a CloudShare record and its entities,
     * generating presigned URLs in one transaction.
     */
    public function createWithEntities(
        User   $user,
        string $resourceId,
        array  $entityPayloads
    ): CloudShare {
        // 0) Generate a share UID up front so presignService can use it in the key
        $shareUid = (string) Str::uuid();

        // 1) Generate the presigned URLs and payloads OUTSIDE the DB transaction
        //    This can do N calls to S3 without blocking any locks in MySQL/Postgres.
        $uploadUrls = $this->presignService->generateUploadUrlsForShare($shareUid, $entityPayloads);

        // 2) Now open a transaction _just_ for the inserts
        return DB::transaction(function () use ($user, $resourceId, $shareUid, $uploadUrls) {
            // a) create the CloudShare record
            $cloudShare = CloudShare::create([
                'uid'         => $shareUid,
                'user_id'     => $user->id,
                'resource_id' => $resourceId,
            ]);

            // b) create all the CloudEntity rows
            //    each uploadUrl array already has 'key', 'e_tag', 'size', etc.
            $cloudShare->cloudEntities()->createMany($uploadUrls);

            return $cloudShare;
        });
    }

    /**
     * Confirm that each entity was uploaded, update metadata,
     * set expiration, and adjust user usage.
     */
    public function confirmUpload(CloudShare $share): CloudShare
    {
        $expiresAt     = now()->addSeconds($this->expireAfter);
        $entities      = $share->cloudEntities;
        $totalSize     = 0;

        // 1. Pre-validate & update each entity
        foreach ($entities as $entity) {
            if (empty($entity->e_tag)) {
                $verification = $this->presignService->confirm($entity, $expiresAt);
                $entity->e_tag       = $verification->e_tag;
                $entity->size        = $verification->size;
                $entity->public_url  = $verification->public_url;
                $entity->expires_at  = $expiresAt;
            }

            $totalSize += $entity->size;
        }

        // 2. Persist updates & adjust user quota in one transaction
        DB::transaction(function () use ($share, $entities, $expiresAt, $totalSize) {
            foreach ($entities as $entity) {
                $entity->save();
            }

            $share->size       = $totalSize;
            $share->expires_at = $expiresAt;
            $share->save();

            /** @var \App\Models\User $user */
            $user = auth()->user();

            // Update the user’s cached cloud usage
            $user->updateCloudUsage($totalSize);
        });

        return $share->load('cloudEntities');
    }
}
