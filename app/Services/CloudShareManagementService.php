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

    public function __construct(
        protected AppLogger $logger,
        protected S3PresignService $presignService
    ) {
        $logger->setContext('CloudShareManagementService');
        $this->cloudShareDir  = Config::get('classer.cloud_share_directory', 'cloud-share');
    }

    /**
     * List all active (and trashed) shares for a user.
     */
    public function listForUser(User $user): Collection
    {
        return CloudShare::where('user_id', $user->id)
            ->withTrashed()
            ->get();
    }

    /**
     * Create a CloudShare record and its entities,
     * generating presigned URLs in one transaction.
     */
    public function create(
        User   $user,
        string $resourceId,
        array  $entityPayloads
    ): CloudShare {
        // 0) Generate a share UID up front so presignService can use it in the key
        $shareUid = (string) Str::uuid();

        // 1) Generate the presigned URLs and payloads OUTSIDE the DB transaction
        //    This can do N calls to S3 without blocking any locks in MySQL/Postgres.
        $uploadUrls = $this->presignService->generateUrlsForShare($shareUid, $entityPayloads);

        // 2) Now open a transaction _just_ for the inserts
        return DB::transaction(function () use ($user, $resourceId, $shareUid, $uploadUrls) {
            // a) create the CloudShare record
            $cloudShare = CloudShare::create([
                'uid'         => $shareUid,
                'user_id'     => $user->id,
                'resource_id' => $resourceId,
                'size'        => collect($uploadUrls)->sum('size'),
            ]);

            // b) create all the CloudEntity rows
            //    each uploadUrl array already has 'key', 'e_tag', 'size', etc.
            $cloudShare->cloudEntities()->createMany($uploadUrls);

            // c) Load the CloudEntities relationship to return
            $totalSize = collect($uploadUrls)->sum('size');
            $user->updateCloudUsage($totalSize);

            return $cloudShare;
        });
    }

    /**
     * Verify the integrity of a CloudShare by checking
     * that the total size of entities matches the S3-reported size.
     *
     * This method applies a 5% tolerance to account for minor discrepancies.
     * If the S3-reported size exceeds the local total by more than 5%
     * it throws an exception.
     */
    public function verify(CloudShare $share): void
    {
        // calculate local total and S3‐reported total
        $totalEntitySize = $share->cloudEntities->sum('size');
        $s3ReportedSize = collect($share->cloudEntities)
            ->map(fn($entity) => $this->presignService->getObjectMeta($entity->key)->size)
            ->sum();

        // apply a 5% margin
        $tolerance      = 0.05;
        $allowedSize    = (int) ceil($totalEntitySize * (1 + $tolerance));
        $differenceInPercentage = (($s3ReportedSize - $totalEntitySize) / $totalEntitySize) * 100;

        // check and handle overflow
        if ($s3ReportedSize > $allowedSize) {
            throw new \RuntimeException(
                sprintf(
                    'CloudShare verification failed for user %d, share %s: S3 reported size (%d) exceeds local total (%d) by more than 5%% (%.2f%%)',
                    $share->user_id,
                    $share->uid,
                    $s3ReportedSize,
                    $totalEntitySize,
                    $differenceInPercentage
                )
            );
        }
    }
}
