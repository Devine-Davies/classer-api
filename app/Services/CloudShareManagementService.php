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
        // 1) Gather sizes as integers
        $totalEntitySize = (int) $share->cloudEntities->sum('size');
        $s3ReportedSize = (int) collect($share->cloudEntities)
            ->map(fn($entity) => (int) ($this->presignService->getObjectMeta($entity->key)->size ?? 0))
            ->sum();

        // 2) Tolerances
        $relativeTolerance = 0.05;                     // 5%
        $absoluteTolerance = 1 * 1024 * 1024;          // 1 MB absolute wiggle room

        // 3) Zero-local guard
        if ($totalEntitySize === 0) {
            // accept tiny S3 remnants up to absolute tolerance
            if ($s3ReportedSize > $absoluteTolerance) {
                throw new \RuntimeException(sprintf(
                    'CloudShare verification failed for user %d, share %s: local total is 0B but S3 reports %dB (> %dB tolerance).',
                    $share->user_id,
                    $share->uid,
                    $s3ReportedSize,
                    $absoluteTolerance
                ));
            }
            return; // OK
        }

        // 4) Compute allowed window
        $allowedDelta = max(
            (int) ceil($totalEntitySize * $relativeTolerance),
            $absoluteTolerance
        );
        $upperBound = $totalEntitySize + $allowedDelta;
        $lowerBound = max(0, $totalEntitySize - $allowedDelta);

        // 5) Compare
        if ($s3ReportedSize > $upperBound) {
            $diffBytes = $s3ReportedSize - $totalEntitySize;
            $diffPct   = ($diffBytes / $totalEntitySize) * 100;

            throw new \RuntimeException(sprintf(
                'CloudShare verification failed for user %d, share %s: S3=%dB exceeds local=%dB by %dB (%.2f%%), tolerance=%dB (~%.2f%%).',
                $share->user_id,
                $share->uid,
                $s3ReportedSize,
                $totalEntitySize,
                $diffBytes,
                $diffPct,
                $allowedDelta,
                $relativeTolerance * 100
            ));
        }

        if ($s3ReportedSize < $lowerBound) {
            $diffBytes = $totalEntitySize - $s3ReportedSize;
            $diffPct   = ($diffBytes / $totalEntitySize) * 100;

            throw new \RuntimeException(sprintf(
                'CloudShare verification failed for user %d, share %s: S3=%dB is smaller than local=%dB by %dB (%.2f%%), tolerance=%dB (~%.2f%%).',
                $share->user_id,
                $share->uid,
                $s3ReportedSize,
                $totalEntitySize,
                $diffBytes,
                $diffPct,
                $allowedDelta,
                $relativeTolerance * 100
            ));
        }
    }
}
