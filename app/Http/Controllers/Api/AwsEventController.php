<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\AwsEvent;
use App\Models\CloudEntity;
use App\Models\CloudEntityStatus;

class AwsEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function received(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        $record = $decoded["Records"][0];

        if (!in_array($record['eventName'], ['ObjectRemoved:Delete', 'ObjectCreated:Put'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process the event',
            ], 200);
        }

        $region = $record['awsRegion'];
        $time = $record['eventTime'];
        $eventName = $record['eventName'];
        $userIdentity = $record['userIdentity']['principalId'];
        $ownerIdentity = $record['s3']['bucket']['ownerIdentity']['principalId'];
        $bucket = $record['s3']['bucket']['name'];
        $arn = $record['s3']['bucket']['arn'];
        $location = $record['s3']['object']['key'];
        $payload = json_encode($record);

        $cloudId = $this->getCloudIdFromDirectory($location);
        $cloudEntity = CloudEntity::where('uid', $cloudId)->first();

        if (!$cloudEntity) {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
            ], 404);
        }

        $event = AwsEvent::create([
            'region' => $region,
            'time' => $time,
            'name' => $eventName,
            'bucket' => $bucket,
            'arn' => $arn,
            'user_identity' => $userIdentity,
            'owner_identity' => $ownerIdentity,
            'payload' => $payload
        ]);

        if ($eventName == 'ObjectRemoved:Delete') {
            $cloudEntity->update([
                'event_id' => $event->id,
                'status' => CloudEntityStatus::DELETED
            ]);
        } 
        
        else if ($eventName == 'ObjectCreated:Put') {
            $size = $record['s3']['object']['size'];
            $cloudEntity->update([
                'event_id' => $event->id,
                'location' => $location,
                'size' => $size,
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
