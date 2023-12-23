<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\AwsEvent;


class AwsController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    static public function GetTotalFolderCountForUser($uid, $folder)
    {
        $s3 = Storage::disk('s3');
        $dir = 'users/' . $uid . '/' . $folder;
        $files = $s3->allFiles($dir);
        return count($files);
    }

    /**
     * Delete files from S3.
     */
    static public function DeleteFiles($files)
    {
        print('Deleting files from S3');
        print_r($files);
        $s3 = Storage::disk('s3');
        return $s3->delete(["users/08cfd9e2-3c9b-40b2-b97e/shorts/classer-1697847956931.mp4"]);
    }


    static public function StoreEvent(AwsEvent $event)
    {
        return AwsEvent::create($event);
    }
}
