<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Http\Controllers\Controller;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Utils\Format;

/**
 * CloudShareController
 * @package App\Http\Controllers\Api
 */
class CloudShareController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'CloudShareController');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $data = CloudShare::where('user_id', $userId)
                // Let's get only active shares, 
                // items will null expires_at will be cleaned up by a scheduled task
                // Scheduled should account for enough time to allow users to confirm uploads
                ->whereNotNull('expires_at')
                ->withTrashed()
                ->get();

            return response()->json($data);
        } catch (\Throwable $th) {
            $this->logger->error("Error fetching cloud shares", [
                'error' => $th->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**x
     * Store a newly created resource in storage.
     */
    public function presign(Request $request)
    {
        try {
            $resourceId = $request->validate([
                'resourceId' => 'required|string',
            ])['resourceId'];

            $reqEntities = $request->validate([
                'entities' => 'required|array',
                'entities.*.uid' => 'required|string',
                'entities.*.sourceFile' => 'required|string',
                'entities.*.contentType' => 'required|string',
                'entities.*.size'  => 'required|integer',
            ])['entities'];

            $user = $request->user();

            // Check if the user can upload the total size
            $totalUploadSize = collect($reqEntities)->sum('size');
            if (!$user->canUpload($totalUploadSize)) {
                return $this->limitExceededResponse($user, $totalUploadSize);
            }

            // Generate presigned URLs for each entity
            $uploadUrls = (new S3PresignService())->generateUploadUrls($reqEntities);
            $data = $this->createCloudShareWithEntities($user, $resourceId, $uploadUrls);
            return response()->json($data->load('cloudEntities'));
        } catch (\Throwable $th) {
            $this->logger->error("Error generating presigned URLs for cloud share", [
                'error' => $th->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
            ], 500);
        }
    }

    /**
     * Create a CloudShare with its entities in a transaction.
     */
    private function createCloudShareWithEntities($user, $resourceId, $uploadUrls)
    {
        return DB::transaction(function () use ($user, $resourceId, $uploadUrls) {
            $cloudShare = CloudShare::create([
                'uid' => Str::uuid(),
                'user_id' => $user->id,
                'resource_id' => $resourceId,
            ]);

            $cloudShare->cloudEntities()->createMany($uploadUrls);

            return $cloudShare;
        });
    }

    /**
     * Confirm the upload by checking the file sizes and storing the metadata.
     * @param string $entityUid
     */
    public function confirm(String $entityUid, Request $request)
    {
        try {
            $s3PresignService = new S3PresignService();
            $entity = CloudShare::where('uid', $entityUid)->firstOrFail();
            $cloudEntities = $entity->cloudEntities()->get();

            // Set expiration time for the share
            $expiresAfter = config('classer.cloud_share_expire_after', '604800'); // Default to 7 days
            $expiresAt = now()->addSeconds(value: $expiresAfter);

            // Pre-process and map updated entities (outside transaction)
            $updatedEntities = $cloudEntities->map(function ($cloudEntity) use ($expiresAt, $s3PresignService) {
                if (!$cloudEntity->e_tag) {
                    $verify = $s3PresignService->confirm($cloudEntity, $expiresAt);
                    $cloudEntity->expires_at = $expiresAt;
                    $cloudEntity->e_tag = $verify->e_tag;
                    $cloudEntity->size = $verify->size;
                    $cloudEntity->public_url = $verify->public_url;
                }
                return $cloudEntity;
            });

            // Calculate total size after processing,
            $user = $request->user();
            $totalSize = $updatedEntities->sum('size');
            if (!$user->canUpload($totalSize)) {
                return $this->limitExceededResponse($user, $totalSize);
            }

            // Persist using DB::transaction
            DB::transaction(function () use ($updatedEntities, $entity, $expiresAt, $totalSize, $user) {
                // Save all updated entities in a single transaction
                $updatedEntities->each->save();

                // Update the main CloudShare entity
                $entity->size = $totalSize;
                $entity->expires_at = $expiresAt;
                $entity->save();

                // Cache the user's cloud storage usage
                $user->updateCloudUsage($totalSize);
            });

            return response()->json($entity);
        } catch (\Throwable $th) {
            $this->logger->error("Failed to confirm upload", [
                'entity_uid' => $entityUid,
                'request' => $request->all(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to confirm upload.',
            ], 500);
        }
    }

    /**
     * Can’t upload files larger than the user’s subscription quota.§
     */
    function limitExceededResponse(User $user, $totalSize): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => sprintf(
                "You have reached your subscription limit. Remaining storage: %s. Attempted upload size: %s.",
                Format::niceBytes($user->remainingStorage()),
                Format::niceBytes($totalSize)
            ),
            'totalUploadSize' => $totalSize,
            'maxUploadSize' => $user->subscription->type->quota,
        ], 403); // Forbidden
    }
}
