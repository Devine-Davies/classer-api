<?php

namespace App\Http\Controllers\Api;

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

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            CloudBackupResource::collection(
                $this->managementService->listForUser($request->user())
            )
        );
    }

    public function show(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): JsonResponse
    {
        return response()->json(
            new CloudBackupResource($cloudBackupUID->load('cloudEntities'))
        );
    }

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

    public function destroy(CloudBackupActionRequest $request, CloudBackup $cloudBackupUID): Response|JsonResponse
    {
        try {
            $this->managementService->delete($request->user(), $cloudBackupUID);

            return response()->noContent();
        } catch (InvalidCloudBackupStateException $exception) {
            return $this->invalidStateResponse($exception);
        }
    }

    protected function invalidStateResponse(InvalidCloudBackupStateException $exception): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $exception->getMessage(),
        ], 409);
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
            'maxUploadSize' => $user->subscription?->plan?->quota,
        ], 403);
    }
}
