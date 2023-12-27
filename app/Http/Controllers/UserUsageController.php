<?php

namespace App\Http\Controllers;

use App\Models\CloudEntity;


class UserUsageController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    static public function GetTotalUserUsage($uid)
    {
        $allCloudMedia = CloudEntity::where('user_id', $uid)->get();
        $totalSize = 0;

        foreach ($allCloudMedia as $media) {
            $totalSize += $media->size;
        }

        return [
            'totalSize' => $totalSize,
            'totalFiles' => count($allCloudMedia)
        ];
    }
}
