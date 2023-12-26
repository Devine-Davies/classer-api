<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\AwsEvent;
use App\Models\CloudEntity;
use App\Models\CloudEntityStatus;

abstract class AWSEventType {
    const OBJECT_CREATED = 'ObjectCreated:Put';
    const OBJECT_DELETED = 'ObjectRemoved:Delete';
}

class AwsEventController extends Controller
{

    public function credentials()
    {
        $credentials = [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET')
        ];

        return response()->json([
            'status' => true,
            'message' => 'Credentials fetched successfully',
            'data' => $credentials
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function received(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        $record = $decoded["Records"][0];
        $eventName = $record['eventName'];

        if (!in_array($eventName, ['ObjectRemoved:Delete', 'ObjectCreated:Put'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process the event',
            ], 200);
        }

        $location = $record['s3']['object']['key'];
        $cloudId = $this->getCloudIdFromDirectory($location);
        $cloudEntity = CloudEntity::where('uid', $cloudId)->first();

        if (!$cloudEntity) {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
            ], 404);
        }

        $event = AwsEvent::create([
            'name' => $eventName,
            'entity_id' => $cloudId,
            'region' => $record['awsRegion'],
            'time' => $record['eventTime'],
            'bucket' => $record['s3']['bucket']['name'],
            'arn' => $record['s3']['bucket']['arn'],
            'user_identity' => $record['userIdentity']['principalId'],
            'owner_identity' => $record['s3']['bucket']['ownerIdentity']['principalId'],
            'payload' => json_encode($record)
        ]);

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process the event',
            ], 200);
        }

        if ($eventName == AWSEventType::OBJECT_DELETED) {
            $cloudEntity->update([
                'status' => CloudEntityStatus::DELETED
            ]);
        } 
        
        if ($eventName == AWSEventType::OBJECT_CREATED) {
            $size = $record['s3']['object']['size'];
            $cloudEntity->update([
                'size' => $size,
                'location' => $location,
                'status' => CloudEntityStatus::ACTIVE
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Event received successfully',
        ], 200);
    }

    /**
     * Get the details from the directory.
     * return the file name from the directory, without extension
     */
    public function getCloudIdFromDirectory($directory)
    {
        return pathinfo($directory, PATHINFO_FILENAME);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function S3PutEvent(Request $request)
    {
        $uid = $request->user()->uid;
        $subType = $request->input('subType');
        $subscription = Subscription::create([
            'uid' => $uid,
            'sub_type' => $subType,
            'status' => 1,
            'issue_date' => now(),
            'expiration_date' => now()->addDays(30)
        ]);
    }
}
