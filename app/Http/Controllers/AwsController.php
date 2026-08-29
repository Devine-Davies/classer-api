<?php

namespace App\Http\Controllers;

use App\Models\AwsEvent;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class AwsController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    public static function GetTotalFolderCountForUser($uid, $folder)
    {
        $s3 = Storage::disk(self::resolveUserStorageDisk());
        $dir = 'users/'.$uid.'/'.$folder;
        $files = $s3->allFiles($dir);

        return count($files);
    }

    /**
     * Delete files from S3.
     */
    public static function DeleteFiles($paths)
    {
        $s3 = Storage::disk(self::resolveUserStorageDisk());

        return $s3->delete($paths);
    }

    /**
     * Store an event in the database.
     */
    public static function StoreEvent(AwsEvent $event)
    {
        return AwsEvent::create($event);
    }

    protected static function resolveUserStorageDisk(): string
    {
        $disk = (string) Config::get('classer.userStorage.disk', 'user-storage');
        $configuredDisks = (array) Config::get('filesystems.disks', []);

        if (! array_key_exists($disk, $configuredDisks)) {
            return 's3';
        }

        return $disk;
    }
}
