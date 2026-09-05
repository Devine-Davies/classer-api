<?php

namespace App\Http\Controllers\Api;

use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Exceptions\InvalidCloudBackupStateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloudBackupActionRequest;
use App\Http\Requests\CloudBackupCreateRequest;
use App\Http\Resources\CloudBackupResource;
use App\Logging\AppLogger;
use App\Models\CloudBackup;
use App\Models\User;
use App\Services\CloudBackupManagementService;
use App\Utils\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CloudBackupController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        protected CloudBackupManagementService $managementService
    ) {
        $this->logger->setContext('CloudBackupController');
    }

    /**
     * Display a listing of the cloud backups for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            CloudBackupResource::collection(
                $this->managementService->listForUser($request->user())
            )
        );
    }

    /**
     * Display the specified cloud backup.
     */
    public function show(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): JsonResponse
    {
        return response()->json(
            new CloudBackupResource($cloudBackupUID->load('cloudEntities'))
        );
    }

    /**
     * Create a new cloud backup for the authenticated user.
     */
    public function create(CloudBackupCreateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = $request->user();

        try {
            $backup = $this->managementService->create(
                $user,
                (string) $payload['resourceId'],
                $payload['entities']
            );

            return response()->json(new CloudBackupResource($backup), 201);
        } catch (CloudStorageQuotaExceededException $exception) {
            return $this->limitExceededResponse($user, $exception->attemptedBytes());
        } catch (InvalidCloudBackupStateException $exception) {
            return $this->invalidStateResponse($exception);
        } catch (\Throwable $exception) {
            $this->logger->error('Error creating backup upload session', [
                'user_id' => $user->id,
                'resource_id' => $payload['resourceId'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create backup upload session.',
            ], 500);
        }
    }

    /**
     * Complete the specified cloud backup.
     */
    public function complete(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): JsonResponse
    {
        try {
            return response()->json(new CloudBackupResource(
                $this->managementService->complete($request->user(), $cloudBackupUID)
            ));
        } catch (InvalidCloudBackupStateException $exception) {
            return $this->invalidStateResponse($exception);
        }
    }

    /**
     * Restore the specified cloud backup.
     */
    public function restore(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): JsonResponse
    {
        try {
            return response()->json([
                'uid' => $cloudBackupUID->uid,
                'files' => $this->managementService->restore($request->user(), $cloudBackupUID),
            ]);
        } catch (InvalidCloudBackupStateException $exception) {
            return $this->invalidStateResponse($exception);
        }
    }

    /**
     * Delete the specified cloud backup.
     */
    public function destroy(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): Response|JsonResponse
    {
        try {
            $this->managementService->delete($request->user(), $cloudBackupUID);

            return response()->noContent();
        } catch (InvalidCloudBackupStateException $exception) {
            return $this->invalidStateResponse($exception);
        }
    }

    /**
     * Respond with an error indicating that the cloud backup is in an invalid state.
     */
    protected function invalidStateResponse(InvalidCloudBackupStateException $exception): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }

    /**
     * Respond with an error indicating that the user has exceeded their storage limit.
     */
    protected function limitExceededResponse(User $user, int $totalSize): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => sprintf(
                'Subscription limit reached. Remaining: %s, Attempted: %s',
                Format::niceBytes($user->remainingStorage(CloudStorageKind::BACKUP)),
                Format::niceBytes($totalSize)
            ),
            'totalUploadSize' => $totalSize,
            'maxUploadSize' => $user->subscription?->plan?->entitlementQuota(
                CloudStorageKind::BACKUP->capability()
            ),
        ], 403);
    }
}
