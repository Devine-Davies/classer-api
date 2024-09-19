<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SiteController extends Controller
{
    public function acmStore(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $path = 'app/public/action-camera-matcher-answer.txt';
        $answers = $request->answers;

        if (!file_exists(storage_path($path))) {
            file_put_contents(storage_path($path), '');
        }

        $file = storage_path('app/public/action-camera-matcher-answer.txt');
        $content = file_get_contents($file);
        $content .= now() . ':' . json_encode($answers) . PHP_EOL;
        file_put_contents($file, $content);

        return response()->json([
            'message' => 'Action Camera Matcher stored successfully'
        ], 200);
    }
}
