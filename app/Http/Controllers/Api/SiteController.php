<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use Illuminate\Http\Request;

/**
 * Site Controller
 */
class SiteController extends Controller
{
    /**
     * Constructor
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext(context: 'SiteController');
    }

    /**
     * Store Action Camera Matcher Answers
     *
     * @return 200, 401
     */
    public function acmStore(Request $request)
    {
        $request->validate([
            'answers' => ['required', 'array', 'max:100'],
            'answers.*' => ['required', 'string', 'max:500'],
        ]);

        $path = 'app/admin/action-camera-matcher-answer.txt';
        $file = storage_path($path);

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        $entry = now().':'.json_encode($request->validated('answers'), JSON_THROW_ON_ERROR).PHP_EOL;
        file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);

        return response()->json([
            'message' => 'Action Camera Matcher stored',
        ], 200);
    }
}
