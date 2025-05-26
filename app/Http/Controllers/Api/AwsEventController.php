<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\AwsEvent;
use App\Models\CloudEntity;
use App\Models\CloudEntityStatus;
use App\Http\Controllers\UserUsageController;

abstract class AWSEventType
{
    const OBJECT_CREATED = 'ObjectCreated:Put';
    const OBJECT_DELETED = 'ObjectRemoved:Delete';
}

class AwsEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function received(Request $request)
    {
        // $events = $this->parseS3Event(json_decode($request->getContent(), true));
        // foreach ($events as $event) {
        //     $eventName = $event['eventName'];
        //     if (in_array($eventName, ['ObjectRemoved:Delete', 'ObjectCreated:Put'])) {
        //         $userId = explode('/', $event['key'])[1];
        //         UserUsageController::CacheUserStorage($userId, 1000000000); // 1GB
        //     }
        // }

        return response()->json([
            'status' => true,
            'message' => 'Event processed',
        ], 200);
    }

    /**
     * Parse and extract important data from an S3 event payload.
     *
     * @param array $event The raw AWS S3 event payload (from SNS or Lambda)
     * @return array<int, array{
     *     eventName: string,
     *     bucket: string,
     *     key: string,
     *     size: int|null,
     *     timestamp: string|null
     * }>
     */
    function parseS3Event(array $event): array
    {
        $parsed = [];

        if (!isset($event['Records']) || !is_array($event['Records'])) {
            return $parsed;
        }

        foreach ($event['Records'] as $record) {
            $parsed[] = [
                'eventName' => $record['eventName'] ?? 'unknown',
                'bucket' => $record['s3']['bucket']['name'] ?? '',
                'key' => urldecode($record['s3']['object']['key'] ?? ''),
                'size' => $record['s3']['object']['size'] ?? null,
                'timestamp' => $record['eventTime'] ?? null,
            ];
        }

        return $parsed;
    }
}




    
        // $record = $event[0];
        // $eventName = $record['eventName'];

        // if (!in_array($eventName, ['ObjectRemoved:Delete', 'ObjectCreated:Put'])) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unable to process the event',
        //     ], 200);
        // }

        

        // $location = $record['s3']['object']['key'];

        // $location = $record['s3']['object']['key'];
        // $cloudId = $this->getCloudIdFromDirectory($location);
        // $cloudEntity = CloudEntity::where('uid', $cloudId)->first();

        // if (!$cloudEntity) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Not found',
        //     ], 404);
        // }

        // $event = AwsEvent::create([
        //     'name' => $eventName,
        //     'entity_id' => $cloudId,
        //     'region' => $record['awsRegion'],
        //     'time' => $record['eventTime'],
        //     'bucket' => $record['s3']['bucket']['name'],
        //     'arn' => $record['s3']['bucket']['arn'],
        //     'user_identity' => $record['userIdentity']['principalId'],
        //     'owner_identity' => $record['s3']['bucket']['ownerIdentity']['principalId'],
        //     'payload' => json_encode($record)
        // ]);

        // if (!$event) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unable to process the event',
        //     ], 200);
        // }

        // if ($eventName == AWSEventType::OBJECT_DELETED) {
        //     $cloudEntity->update([
        //         'status' => CloudEntityStatus::DELETED
        //     ]);
        // } 

        // if ($eventName == AWSEventType::OBJECT_CREATED) {
        //     $size = $record['s3']['object']['size'];
        //     $cloudEntity->update([
        //         'size' => $size,
        //         'location' => $location,
        //         'status' => CloudEntityStatus::ACTIVE
        //     ]);
        // }

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Event received',
        // ], 200);
        
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function received(Request $request)
    // {
    //     $decoded = json_decode($request->getContent(), true);
    //     $record = $decoded["Records"][0];
    //     $eventName = $record['eventName'];

    //     if (!in_array($eventName, ['ObjectRemoved:Delete', 'ObjectCreated:Put'])) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unable to process the event',
    //         ], 200);
    //     }

    //     $location = $record['s3']['object']['key'];
    //     $cloudId = $this->getCloudIdFromDirectory($location);
    //     $cloudEntity = CloudEntity::where('uid', $cloudId)->first();

    //     if (!$cloudEntity) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Not found',
    //         ], 404);
    //     }

    //     $event = AwsEvent::create([
    //         'name' => $eventName,
    //         'entity_id' => $cloudId,
    //         'region' => $record['awsRegion'],
    //         'time' => $record['eventTime'],
    //         'bucket' => $record['s3']['bucket']['name'],
    //         'arn' => $record['s3']['bucket']['arn'],
    //         'user_identity' => $record['userIdentity']['principalId'],
    //         'owner_identity' => $record['s3']['bucket']['ownerIdentity']['principalId'],
    //         'payload' => json_encode($record)
    //     ]);

    //     if (!$event) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unable to process the event',
    //         ], 200);
    //     }

    //     if ($eventName == AWSEventType::OBJECT_DELETED) {
    //         $cloudEntity->update([
    //             'status' => CloudEntityStatus::DELETED
    //         ]);
    //     } 

    //     if ($eventName == AWSEventType::OBJECT_CREATED) {
    //         $size = $record['s3']['object']['size'];
    //         $cloudEntity->update([
    //             'size' => $size,
    //             'location' => $location,
    //             'status' => CloudEntityStatus::ACTIVE
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Event received',
    //     ], 200);
    // }