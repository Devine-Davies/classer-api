<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\AWSEvent;

class AwsEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function AwsEvent(Request $request)
    {
        $record = $request->Records[0];
        $bucket = $record['s3']['bucket']['name'];
        $region = $record['awsRegion'];
        $userIdentity = $record['userIdentity'];
        $ownerIdentity = $record['userIdentity'];
        $arn = $record['s3']['bucket']['arn'];
        $time = $record['eventTime'];
        $payload = json_decode($request->getContent(), true);

        AwsEvent::create([
            'name' => 'S3PutEvent',
            'bucket' => $bucket,
            'Region' => $region,
            'userIdentity' => $userIdentity,
            'ownerIdentity' => $ownerIdentity,
            'arn' => $arn,
            'time' => $time,
            'payload' => $payload
        ]);

        return response()->setStatusCode(200);
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
