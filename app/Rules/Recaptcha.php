<?php

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    public function __construct(
        protected ?string $action = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(RecaptchaService::class);
        $ip = request()->ip();

        if (!$service->verify(is_string($value) ? $value : null, $this->action, $ip)) {
            $fail('reCAPTCHA verification failed. Please refresh the page and try again.');
        }
    }
}
