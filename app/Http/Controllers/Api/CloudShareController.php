<?php

namespace App\Http\Controllers\Api;

use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Exceptions\InvalidCloudShareStateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloudShareConfirmRequest;
use App\Http\Requests\CloudShareCreateRequest;
use App\Http\Requests\CloudShareIndexRequest;
use App\Http\Resources\CloudShareResource;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;
use App\Services\CloudShareManagementService;
use App\Utils\Format;
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

        try {
            $share = $this->managementService->create(
                $user,
                $resourceId,
                $entities
            );

            return response()->json(
                new CloudShareResource($share),
                201
            );
        } catch (CloudStorageQuotaExceededException $exception) {
            return $this->limitExceededResponse(
                $user,
                $exception->attemptedBytes()
            );
        } catch (\Throwable $exception) {
            $this->logger->error('Error creating cloud share upload session', [
                'user_id' => $user->id,
                'resource_id' => $resourceId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create cloud share upload session.',
            ], 500);
        }
    }

    public function complete(
        CloudShareConfirmRequest $request,
        CloudShare $cloudShareUID
    ): JsonResponse {
        try {
            $share = $this->managementService->complete(
                $request->user(),
                $cloudShareUID
            );

            return response()->json(new CloudShareResource($share));
        } catch (InvalidCloudShareStateException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 409);
        }
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
                Format::niceBytes($user->remainingStorage(CloudStorageKind::SHARE)),
                Format::niceBytes($totalSize)
            ),
            'totalUploadSize' => $totalSize,
            'maxUploadSize' => $user->subscription?->plan?->entitlementQuota(
                CloudStorageKind::SHARE->capability()
            ),
        ], 403);
    }
}
