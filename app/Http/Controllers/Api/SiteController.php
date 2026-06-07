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
        $this->logger = $logger;
        $this->logger->setContext(context: 'AuthController');
    }

    /**
     * Store Action Camera Matcher Answers
     *
     * @return 200, 401
     */
    public function acmStore(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $path = 'app/public/action-camera-matcher-answer.txt';
        $answers = $request->answers;

        if (! file_exists(storage_path($path))) {
            file_put_contents(storage_path($path), '');
        }

        $file = storage_path('app/public/action-camera-matcher-answer.txt');
        $content = file_get_contents($file);
        $content .= now().':'.json_encode($answers).PHP_EOL;
        file_put_contents($file, $content);

        return response()->json([
            'message' => 'Action Camera Matcher stored',
        ], 200);
    }
}
