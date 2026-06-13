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

    /**
     * List all cloud shares for the authenticated user.
     *
     * @param  CloudShareIndexRequest  $request  The incoming request containing the authenticated user.
     * @return JsonResponse A JSON response containing a collection of cloud shares.
     */
    public function index(CloudShareIndexRequest $request): JsonResponse
    {
        $shares = $this->managementService->listForUser($request->user());

        return response()->json(
            CloudShareResource::collection($shares)
        );
    }

    /**
     * Create a new cloud share upload session for the authenticated user.
     *
     * @param  CloudShareCreateRequest  $request  The incoming request containing the authenticated user and upload details.
     * @return JsonResponse A JSON response containing the created cloud share resource or an error message if creation fails.
     */
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

    /**
     * Schedule background jobs to verify and expire the cloud share upload after specified delays.
     *
     * @param  CloudShare  $share  The cloud share for which to schedule the jobs.
     */
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

    /**
     * Convert a relative time string from configuration into a DateTimeInterface instance for job scheduling.
     *
     * @param  string  $relativeTime  A relative time string (e.g., '+1 minute', '+2 hours').
     * @return DateTimeInterface The calculated future time based on the current time and the relative offset.
     */
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

    /**
     * Generate a JSON response indicating that the user's subscription limit has been exceeded for the attempted upload.
     *
     * @param  User  $user  The user who attempted the upload.
     * @param  int  $totalSize  The total size of the attempted upload in bytes.
     * @return JsonResponse A JSON response with a 403 status code and details about the limit exceeded error.
     */
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
            'maxUploadSize' => $user->subscription?->plan?->quota,
        ], 403);
    }
}
