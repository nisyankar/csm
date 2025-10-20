<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre gereklidir.',
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

        // Check if user exists and account is not locked
        $user = User::where('email', $this->email)->first();
        
        if ($user) {
            // Check if account is locked
            if ($user->account_locked_until && now() < $user->account_locked_until) {
                $remainingTime = now()->diffInMinutes($user->account_locked_until);
                throw ValidationException::withMessages([
                    'email' => "Hesabınız kilitlenmiştir. {$remainingTime} dakika sonra tekrar deneyiniz.",
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Hesabınız pasif durumda. Lütfen yöneticinizle iletişime geçiniz.',
                ]);
            }

            // Check if account is expired (for temporary users)
            if ($user->is_temporary_user && $user->access_expires_at && now() > $user->access_expires_at) {
                throw ValidationException::withMessages([
                    'email' => 'Geçici hesabınızın süresi dolmuştur.',
                ]);
            }
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Record failed login attempt
            if ($user) {
                $this->recordFailedLoginAttempt($user);
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Girilen bilgiler kayıtlarımızla uyuşmuyor.',
            ]);
        }

        // Reset failed login attempts on successful login
        if ($user && $user->failed_login_attempts > 0) {
            $user->update([
                'failed_login_attempts' => 0,
                'account_locked_until' => null,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

    /**
     * Record failed login attempt and handle account locking
     */
    private function recordFailedLoginAttempt(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        
        $updateData = [
            'failed_login_attempts' => $attempts,
        ];
        
        // Lock account after 5 failed attempts for 15 minutes
        if ($attempts >= 5) {
            $updateData['account_locked_until'] = now()->addMinutes(15);
        }
        
        $user->update($updateData);
    }
}