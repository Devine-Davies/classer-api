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

        if (!$this->validateCaptcha($request->grc)) {
            return response()->json([
                'message' => 'Something went wrong, please try again..'
            ], 401);
        }

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

    /**
     * Validate Captcha
     * @param string $code
     */
    private function validateCaptcha($code)
    {
        $secretKey = '6LdNKLMpAAAAAAROGY9QuLqt4e-wbxgCmSZzIXEU';
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$code");
        $responseData = json_decode($response);

        if (!$responseData->success) {
            return false;
        }

        if ($responseData->score < 0.5) {
            return false;
        }

        return true;
    }
}
