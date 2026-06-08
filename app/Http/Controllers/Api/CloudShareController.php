<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudShareCreateRequest;
use App\Http\Requests\CloudShareIndexRequest;
use App\Http\Resources\CloudShareResource;
use App\Jobs\CloudShareExpireUpload;
use App\Jobs\CloudShareVerifyUpload;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use App\Services\CloudShareManagementService;
use App\Utils\Format;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;

class CloudShareController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        protected CloudShareManagementService $managementService
    ) {
        $this->logger->setContext('CloudShareController');
    }

    public function index(CloudShareIndexRequest $request): JsonResponse
    {
        $shares = $this->managementService->listForUser($request->user());

        return response()->json(
            CloudShareResource::collection($shares)
        );
    }

    public function create(CloudShareCreateRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        $entities = $payload['entities'] ?? [];
        $resourceId = (string) ($payload['resourceId'] ?? '');
        $totalSize = (int) collect($entities)->sum('size');

        if (! $user->canUpload($totalSize)) {
            return $this->limitExceededResponse($user, $totalSize);
        }

        try {
            $share = $this->managementService->create(
                $user,
                $resourceId,
                $entities
            );

            $this->scheduleUploadLifecycleJobs($share);

            return response()->json(
                new CloudShareResource($share->load('cloudEntities')),
                201
            );
        } catch (\Throwable $exception) {
            $this->logger->error('Error creating cloud share upload session', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'entity_count' => count($entities),
                'total_size' => $totalSize,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to generate presigned URLs.',
            ], 500);
        }
    }

    protected function scheduleUploadLifecycleJobs(CloudShare $share): void
    {
        CloudShareVerifyUpload::dispatch($share)
            ->onConnection('cloudshare')
            ->delay($this->delayFromRelativeTime(
                (string) config('classer.cloudShare.verifyDelay', '+1 minute')
            ));

        CloudShareExpireUpload::dispatch($share)
            ->onConnection('cloudshare')
            ->delay($this->delayFromRelativeTime(
                (string) config('classer.cloudShare.getObjectTimeout', '+2 minutes')
            ));
    }

    protected function delayFromRelativeTime(string $relativeTime): DateTimeInterface
    {
        $timestamp = strtotime($relativeTime);

        if ($timestamp === false) {
            $this->logger->warning('Invalid relative delay config, defaulting to immediate dispatch', [
                'relative_time' => $relativeTime,
            ]);

            return now();
        }

        return now()->addSeconds(
            max(0, $timestamp - time())
        );
    }

    protected function limitExceededResponse(User $user, int $totalSize): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => sprintf(
                'Subscription limit reached. Remaining: %s, Attempted: %s',
                Format::niceBytes($user->remainingStorage()),
                Format::niceBytes($totalSize)
            ),
            'totalUploadSize' => $totalSize,
            'maxUploadSize' => $user->subscription?->type?->quota,
        ], 403);
    }
}
