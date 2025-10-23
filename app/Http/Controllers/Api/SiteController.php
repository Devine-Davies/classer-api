<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;

use App\Models\User;
use App\Logging\AppLogger;

/**
 * Site Controller
 */
class SiteController extends Controller
{
    /**
     * Constructor
     * @param AppLogger $logger
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AuthController');
    }

    /**
     * Store Action Camera Matcher Answers
     * @param Request $request
     * @return 200, 401
     */
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
            'message' => 'Action Camera Matcher stored'
        ], 200);
    }

    /**
     * Accept Insider Invite
     * @param Request $request
     * @return 200, 400
     */
    public function acceptInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $code = 'T017A42C';
        $email = $request->input('email');

        try {
            $user = User::where('email', $email)->firstOrFail();

            // Check if the user has an active subscription
            if ($user->activeSubscription()) {
                $errorPayload = [
                    'message' => 'You already have an active subscription to accept the invite.',
                ];
                return response()->json($errorPayload, Response::HTTP_OK);
            }

            Artisan::call('subscription:activate', [
                'email' => $user->email,
                'code' => $code,
            ]);

            $successPayload = [
                'message' => 'You have successfully accepted the invite. Thank you for joining Classer Insiders!',
            ];
            return response()->json($successPayload, Response::HTTP_OK);
        } catch (\Throwable $th) {
            $this->logger->error('Accept invite failed', [
                'request' => $request->all(),
                'error' => $th->getMessage(),
            ]);

            $errorPayload = [
                'message' => 'You do not have an active subscription to accept the invite.',
            ];
            return response()->json($errorPayload, Response::HTTP_OK);
        }
    }
}
