<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Logging\AppLogger;

class VerifyRecaptcha
{
    protected AppLogger $logger;

    /**
     * Create a new middleware instance.
     * @param AppLogger $logger
     */
    public function __construct(AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext('VerifyRecaptcha');
    }

    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $isEnabled = config('services.recaptcha.enabled', false);
        $passed = !$isEnabled || $this->validateCaptcha($request->input('grc'));

        if (!$passed) {
            return response()->json([
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$isEnabled) {
            $this->logger->info('Recaptcha validation skipped (disabled)');
        }

        return $next($request);
    }

    /**
     * Validate the reCAPTCHA code.
     * @param string|null $code
     * @return bool
     */
    private function validateCaptcha(?string $code): bool
    {
        if (!$code) {
            $this->logger->warning('No captcha code provided');
            return false;
        }

        $secretKey = config('services.recaptcha.secret');
        $googleURL = config('services.recaptcha.url');
        $response = @file_get_contents("$googleURL?secret=$secretKey&response=$code");

        if (!$response) {
            $this->logger->error('Failed to fetch captcha validation response', ['code' => $code]);
            return false;
        }

        $responseData = json_decode($response);

        if (!$responseData->success) {
            $this->logger->warning('Captcha validation failed', [
                'response' => $responseData,
                'code' => $code
            ]);
            return false;
        }

        if ($responseData->score < 0.5) {
            $this->logger->warning('Captcha score too low', [
                'score' => $responseData->score,
                'code' => $code
            ]);
            return false;
        }

        return true;
    }
}
