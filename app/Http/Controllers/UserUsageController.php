<?php

namespace App\Http\Controllers;

use Aws\S3;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\CloudEntity;



class UserUsageController extends Controller
{
    /**
     * Get Cloud Usage
     */
    static public function CacheUserStorage($userId, $entities, $maxSize)
    {
        $totalUsed = array_sum(array_column($entities, 'Size'));
        $remaining = $maxSize - $totalUsed;

        return [
            'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
            'totalUsed' =>  $totalUsed,
            'remaining' =>  $remaining,
            'entities' => $entities,
            'lastChecked' => now(),
        ];

        // // // cache the results in the database
        // CloudEntity::create([
        //     'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
        //     'totalUsed' =>  $totalUsed,
        //     'remaining' =>  $remaining,
        //     'lastChecked' => now(),
        // ]);

        $allEntities = CloudEntity::where('user_id', $userId)->get();


        // get all cloud entities that have user_id and entity_id
    }

    /**
     * Save Storage entities
     */
    static public function SaveStorageEntities($userId, $entities)
    {
        foreach ($entities as $entity) {
            CloudEntity::create([
                'uid' => substr(Str::uuid(), 0, strrpos(Str::uuid(), '-')),
                'user_id' => $userId,
                'entity_id' => $entity['Key'],
                'entity_type' => 'file',
                'size' => $entity['Size'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
