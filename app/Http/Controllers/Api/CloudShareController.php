<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudShareCreateRequest;
use App\Http\Requests\CloudShareIndexRequest;
use App\Http\Resources\CloudShareResource;
use App\Jobs\CloudShare\CloudShareExpireUpload;
use App\Jobs\CloudShare\CloudShareVerifyUpload;
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

        $this->logger->info('Cloud share create request received', [
            'user_id' => $user->id,
            'user_uid' => $user->uid,
            'resource_id' => $resourceId,
            'entity_count' => count($entities),
            'total_size' => $totalSize,
            'request_ip' => $request->ip(),
            'request_user_agent' => (string) $request->userAgent(),
            'x_app_platform' => (string) $request->header('x-app-platform', ''),
            'x_app_version' => (string) $request->header('x-app-version', ''),
            'x_app_architecture' => (string) $request->header('x-app-architecture', ''),
            'x_app_uid' => (string) $request->header('x-app-uid', ''),
            'x_request_id' => (string) $request->header('x-request-id', ''),
        ]);

        if (! $user->canUpload($totalSize)) {
            $this->logger->warning('Cloud share create rejected by quota check', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'entity_count' => count($entities),
                'total_size' => $totalSize,
                'remaining_storage' => $user->remainingStorage(),
                'plan_quota' => $user->subscription?->plan?->quota,
            ]);

            return $this->limitExceededResponse($user, $totalSize);
        }

        try {
            $this->logger->info('Cloud share create starting management service create', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'entity_count' => count($entities),
                'total_size' => $totalSize,
            ]);

            $share = $this->managementService->create(
                $user,
                $resourceId,
                $entities
            );

            $this->logger->info('Cloud share create management service create succeeded', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'share_uid' => $share->uid,
                'share_size' => $share->size,
            ]);

            $this->scheduleUploadLifecycleJobs($share);

            $this->logger->info('Cloud share create completed successfully', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'share_uid' => $share->uid,
            ]);

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
                'exception_class' => get_class($exception),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
                'error' => $exception->getMessage(),
                'trace_preview' => implode("\n", array_slice(explode("\n", $exception->getTraceAsString()), 0, 8)),
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
                (string) config('classer.cloudShare.expireAfter', '+2 minutes')
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
