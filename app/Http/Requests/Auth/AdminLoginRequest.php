<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), true)) {
            $this->recordFailedAttempt();
            
            throw ValidationException::withMessages([
                'email' => 'Incorrect email or password.',
            ]);
        }

        // Must be an admin
        if (Auth::user()->role !== 'admin') {
            Auth::logout();
            $this->recordFailedAttempt();
            
            throw ValidationException::withMessages([
                'email' => 'Unauthorized access. Admin privileges required.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Record a failed attempt and log for security monitoring.
     */
    protected function recordFailedAttempt(): void
    {
        RateLimiter::hit($this->throttleKey(), 900); // 900 seconds = 15 minutes

        $attempts = RateLimiter::attempts($this->throttleKey());

        Log::warning('Failed Admin Login Attempt', [
            'ip' => $this->ip(),
            'email' => $this->input('email'),
            'attempt_count' => $attempts,
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => 'Too many failed login attempts. Please try again later.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip().'|admin-login');
    }
}
