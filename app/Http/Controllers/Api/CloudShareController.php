<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CloudShare;
use App\Http\Controllers\Controller;
use App\Services\S3PresignService;

/**
 * CloudShareController
 * @package App\Http\Controllers\Api
 */
class CloudShareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $data = CloudShare::where('user_id', $userId)->get();
            return response()->json($data);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
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
            $userId = $request->user()->id;
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

            $data = CloudShare::create([
                'uid' => Str::uuid(),
                'user_id' => $userId,
                'resource_id' => $resourceId,
            ]);

            $uploadUrls = (new S3PresignService())->generateUploadUrls($reqEntities);
            $data->cloudEntities()->createMany($uploadUrls);

            return response()->json(
                $data->load('cloudEntities')
            );
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm the upload by checking the file sizes and storing the metadata.
     * @param string $entityUid
     */
    public function confirm(String $entityUid)
    {
        try {
            $entity = CloudShare::where('uid', $entityUid)->firstOrFail();
            $cloudEntities = $entity->cloudEntities()->get();
            $expiresAt = now()->addSeconds(604800); // ✅ Exact max limit
            collect($cloudEntities)->each(function ($entity) use ($expiresAt) {
                if (!$entity->e_tag) {
                    $s3PresignService = new S3PresignService();
                    $verify = $s3PresignService->confirm($entity, $expiresAt);
                    $entity->expires_at = $expiresAt;
                    $entity->e_tag = $verify->e_tag;
                    $entity->size = $verify->size;
                    $entity->public_url = $verify->public_url;
                    $entity->save();
                }
            });

            $entity->expires_at = $expiresAt;
            $entity->size = $cloudEntities->sum('size');
            $entity->save();
            return response()->json($entity);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}