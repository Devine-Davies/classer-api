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
    static public function DeleteFiles($paths)
    {
        print('Deleting paths from S3');
        print_r($paths);
        $s3 = Storage::disk('s3');
        return $s3->delete($paths);
    }


    static public function StoreEvent(AwsEvent $event)
    {
        return AwsEvent::create($event);
    }
}
