<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify Google reCAPTCHA v3 token.
     *
     * @param string|null $token
     * @param string|null $expectedAction
     * @param string|null $ip
     * @return bool
     */
    public function verify(?string $token, ?string $expectedAction = null, ?string $ip = null): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        $minScore  = (float) config('services.recaptcha.min_score', 0.5);

        // If no secret key is configured (e.g. initial setup / CI test environments without keys),
        // allow the request so local development is not broken.
        if (empty($secretKey)) {
            Log::warning('reCAPTCHA verification skipped: RECAPTCHA_SECRET_KEY is not configured.');
            return true;
        }

        if (empty($token)) {
            Log::warning('reCAPTCHA verification failed: missing token.');
            return false;
        }

        // Test token support for local/development and integration tests
        if (app()->environment('local', 'testing', 'development') && $token === 'test-recaptcha-token') {
            return true;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (!$response->successful()) {
                Log::error('reCAPTCHA HTTP error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            $success = (bool) ($data['success'] ?? false);
            $score   = (float) ($data['score'] ?? 0.0);
            $action  = $data['action'] ?? null;

            if (!$success) {
                Log::warning('reCAPTCHA verification rejected by Google', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'hostname'    => $data['hostname'] ?? null,
                ]);
                return false;
            }

            if ($score < $minScore) {
                Log::warning('reCAPTCHA score below threshold', [
                    'score'     => $score,
                    'min_score' => $minScore,
                    'action'    => $action,
                ]);
                return false;
            }

            if ($expectedAction && $action && $action !== $expectedAction) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $expectedAction,
                    'actual'   => $action,
                ]);
                // Depending on security posture, you may return false or allow if score is very high.
                // We verify action when provided.
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
