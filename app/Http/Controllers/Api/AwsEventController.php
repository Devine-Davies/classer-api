<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\AwsEvent;
use App\Models\CloudEntity;

class AwsEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function received(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        $record = $decoded["Records"][0];

        if ($record['eventName'] != 'ObjectCreated:Put') {
            return response()->json([
                'status' => false,
                'message' => 'Not able to process event',
            ], 200);
        }

        [
            $region,
            $userIdentity,
            $ownerIdentity,
            $bucket,
            $arn,
            $location,
            $size,
            $time,
            $payload
        ] = [
            $record['awsRegion'],
            $record['userIdentity']['principalId'],
            $record['s3']['bucket']['ownerIdentity']['principalId'],
            $record['s3']['bucket']['name'],
            $record['s3']['bucket']['arn'],
            $record['s3']['object']['key'],
            $record['s3']['object']['size'],
            $record['eventTime'],
            json_encode($record)
        ];

        $cloudId = $this->getCloudIdFromDirectory($location);
        $cloudEntity = CloudEntity::where('uid', $cloudId)
        ->where('status', 3) // Status is processing
        ->first();

        if (!$cloudEntity) {
            return response()->json([
                'status' => false,
                'message' => 'Cloud entity not found',
            ], 404);
        }

        $event = AwsEvent::create([
            'name' => 'S3PutEvent',
            'bucket' => $bucket,
            'Region' => $region,
            'userIdentity' => $userIdentity,
            'ownerIdentity' => $ownerIdentity,
            'arn' => $arn,
            'time' => $time,
            'payload' => $payload
        ]);

        $cloudEntity->update([
            'event_id' => $event->id,
            'location' => $location,
            'size' => $size,
            'status' => 1 // Status is completed
        ]);

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
