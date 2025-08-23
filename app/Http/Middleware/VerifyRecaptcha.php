<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Logging\AppLogger;

class VerifyRecaptcha
{
    protected AppLogger $logger;

    public function __construct(AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext('VerifyRecaptcha');
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): JsonResponse|\Illuminate\Http\Response
    {
        if (!config('services.recaptcha.enabled', false)) {
            $this->logger->info('Recaptcha validation skipped (disabled in config)');
            return $next($request);
        }

        // Validate input
        $validated = $request->validate([
            'grc' => ['required', 'string'],
        ]);

        if (!isset($validated['grc']) || empty($validated['grc'])) {
            return response()->json([
                'message' => 'Captcha code is required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$this->validateCaptcha($validated['grc'])) {
            return response()->json([
                'message' => 'Captcha verification failed. Please try again.'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    /**
     * Validate the reCAPTCHA code with Google.
     */
    private function validateCaptcha(string $code): bool
    {
        $secretKey = config('services.recaptcha.secret');
        $googleURL = config('services.recaptcha.url');
        $threshold = config('services.recaptcha.threshold', 0.5);

        try {
            $response = Http::asForm()->timeout(5)->post($googleURL, [
                'secret'   => $secretKey,
                'response' => $code,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Captcha validation request failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        if (!$response->ok()) {
            $this->logger->error('Captcha validation HTTP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        }

        $data = $response->json();

        if (empty($data['success']) || ($data['score'] ?? 0) < $threshold) {
            $this->logger->warning('Captcha validation failed', [
                'success' => $data['success'] ?? false,
                'score'   => $data['score'] ?? null,
            ]);
            return false;
        }

        return true;
    }
}
