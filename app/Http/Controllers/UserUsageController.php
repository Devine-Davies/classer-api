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
        $totalSize = 0;
        $entities = CloudEntity::where('user_id', $uid)->get();

        foreach ($entities as $entity) {
            $totalSize += $entity->size;
        }

        return [
            'totalSize' => $totalSize,
            'totalFiles' => count($entities)
        ];
    }
}
