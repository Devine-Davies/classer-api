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
        $totalUsage = 0;

        foreach ($allCloudMedia as $media) {
            $totalUsage += $media->size;
        }

        return [
            'totalUsage' => $totalUsage,
            'totalFiles' => count($allCloudMedia)
        ];
    }
}
