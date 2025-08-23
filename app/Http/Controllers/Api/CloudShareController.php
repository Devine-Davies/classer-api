<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudShareIndexRequest;
use App\Http\Requests\CloudShareCreateRequest;
use App\Http\Resources\CloudShareResource;
use App\Logging\AppLogger;
use App\Services\CloudShareManagementService;
use App\Utils\Format;
use Illuminate\Http\JsonResponse;
use App\Jobs\CloudShareVerifyUpload;
use App\Jobs\CloudShareExpireUpload;

class CloudShareController extends Controller
{
    /**
     * CloudShareController constructor.
     *
     * @param AppLogger                    $logger
     * @param CloudShareManagementService  $managementService
     */
    public function __construct(
        protected AppLogger $logger,
        protected CloudShareManagementService $managementService
    ) {
        $this->logger = $logger;
        $this->logger->setContext('CloudShareController');
    }

    /**
     * Display a list of active cloud shares for the authenticated user.
     *
     * @param  CloudShareIndexRequest  $request
     * @return JsonResponse
     */
    public function index(CloudShareIndexRequest $request): JsonResponse
    {
        $shares = $this->managementService->listForUser($request->user());
        return response()->json(
            CloudShareResource::collection($shares)
        );
    }

    /**
     * Generate presigned S3 URLs and create a new CloudShare with its entities.
     *
     * @param  CloudShareCreateRequest  $request
     * @return JsonResponse
     */
    public function create(CloudShareCreateRequest $request): JsonResponse
    {
        $user    = $request->user();
        $payload = $request->validated();
        $sizeSum = collect($payload['entities'])->sum('size');

        if (! $user->canUpload($sizeSum)) {
            return $this->limitExceededResponse($user, $sizeSum);
        }

        try {
            $share = $this->managementService
                ->create($user, $payload['resourceId'], $payload['entities']);

            // Don't assume the size the user has given us is correct,
            // we need to verify the upload size against the actual S3 objects. 
            $cloudShareVerifyAfter = strtotime(config('classer.cloudShare.verifyDelay', '+1 minute')) - time();
            CloudShareVerifyUpload::dispatch($share)
                ->onConnection('cloudshare')
                ->delay(now()->addSeconds($cloudShareVerifyAfter));

            // Set expiration time for the share
            $cloudShareExpireAfter = strtotime(config('classer.cloudShare.getObjectTimeout', '+1 hour')) - time();
            CloudShareExpireUpload::dispatch($share)
                ->onConnection('cloudshare')
                ->delay(now()->addSeconds($cloudShareExpireAfter));

            // Return the created share with its entities
            return response()->json(
                new CloudShareResource($share->load('cloudEntities')),
                201
            );
        } catch (\Throwable $th) {
            $this->logger->error("Error generating presigned URLs for cloud share", [
                'error' => $th->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to generate presigned URLs.',
            ], 500);
        }
    }

    /**
     * Return a standardized response when the user exceeds their storage quota.
     *
     * @param  \App\Models\User  $user
     * @param  int               $totalSize
     * @return JsonResponse
     */
    protected function limitExceededResponse($user, int $totalSize): JsonResponse
    {
        return response()->json([
            'status'           => false,
            'message'          => sprintf(
                "Subscription limit reached. Remaining: %s, Attempted: %s",
                Format::niceBytes($user->remainingStorage()),
                Format::niceBytes($totalSize)
            ),
            'totalUploadSize'  => $totalSize,
            'maxUploadSize'    => $user->subscription->type->quota,
        ], 403);
    }
}