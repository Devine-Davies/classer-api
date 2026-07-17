<?php

namespace App\Http\Middleware;

use App\Logging\AppLogger;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

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
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|Response
    {
        if (! config('services.recaptcha.enabled', false)) {
            $this->logger->info('Recaptcha validation skipped (disabled in config)');

            return $next($request);
        }

        // Validate input
        $validated = $request->validate([
            'grc' => ['required', 'string'],
        ]);

        if (! isset($validated['grc']) || empty($validated['grc'])) {
            return $this->failedValidationResponse(
                request: $request,
                message: 'Captcha code is required.',
                status: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (! $this->validateCaptcha($validated['grc'])) {
            return $this->failedValidationResponse(
                request: $request,
                message: 'Captcha verification failed. Please try again.',
                status: JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }

    /**
     * Build an error response for both API and web form requests.
     */
    private function failedValidationResponse(
        Request $request,
        string $message,
        int $status
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return back()
            ->withErrors([
                'grc' => $message,
            ])
            ->withInput($request->except('password'));
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
                'secret' => $secretKey,
                'response' => $code,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Captcha validation request failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->ok()) {
            $this->logger->error('Captcha validation HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $data = $response->json();

        if (empty($data['success']) || ($data['score'] ?? 0) < $threshold) {
            $this->logger->warning('Captcha validation failed', [
                'success' => $data['success'] ?? false,
                'score' => $data['score'] ?? null,
            ]);

            return false;
        }

        return true;
    }
}
