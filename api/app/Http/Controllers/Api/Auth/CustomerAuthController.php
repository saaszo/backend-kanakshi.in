<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\CustomerAccessToken;
use App\Models\CustomerEmailSetting;
use App\Models\OtpProviderSetting;
use App\Models\OtpVerificationSetting;
use App\Services\CustomerEmailService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class CustomerAuthController
{
    private const EMAIL_VERIFICATION_OTP_PURPOSE = 'email_verification';
    private const PASSWORD_RESET_OTP_PURPOSE = 'forgot_password';

    public function config(): JsonResponse
    {
        $verification = $this->verificationSettings();
        $hasMobileProvider = $this->hasActiveMobileProvider();
        $customerEmailService = new CustomerEmailService();

        return response()->json([
            'success' => true,
            'message' => 'Customer auth configuration fetched successfully.',
            'data' => [
                'email_verification_enabled' => (bool) ($verification?->email_verification_enabled ?? true),
                'mobile_verification_enabled' => $hasMobileProvider && (bool) ($verification?->mobile_verification_enabled ?? false),
                'email_otp_enabled' => (bool) ($verification?->email_otp_enabled ?? true),
                'sms_otp_enabled' => $hasMobileProvider && (bool) ($verification?->sms_otp_enabled ?? false),
                'whatsapp_otp_enabled' => $hasMobileProvider && (bool) ($verification?->whatsapp_otp_enabled ?? false),
                'default_otp_channel' => $hasMobileProvider
                    ? ($verification?->default_otp_channel ?? 'email')
                    : 'email',
                'otp_length' => (int) ($verification?->otp_length ?? 6),
                'otp_expiry_minutes' => (int) ($verification?->otp_expiry_minutes ?? 10),
                'resend_wait_seconds' => (int) ($verification?->resend_wait_seconds ?? 60),
                'customer_email_active' => $customerEmailService->isCustomerEmailDeliveryActive(),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        try {
            $verification = $this->verificationSettings();
            $requiresEmailVerification = (bool) ($verification?->email_verification_enabled ?? true);

            if ($requiresEmailVerification) {
                $this->ensureCustomerVerificationCanSend('verification');
                $email = strtolower($validated['email']);
                $this->upsertPendingRegistration($validated);
                DB::table('otp_codes')
                    ->whereNull('user_id')
                    ->where('email', $email)
                    ->where('purpose', self::EMAIL_VERIFICATION_OTP_PURPOSE)
                    ->delete();

                $otp = $this->createOtp(null, $email, self::EMAIL_VERIFICATION_OTP_PURPOSE);
                $this->sendCustomerMail(
                    $email,
                    'Verify your Kanakshi.in account',
                    "Your verification OTP is {$otp}. It is valid for {$this->otpExpiryMinutes()} minutes.\n\nTeam Kanakshi.in"
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Registration received. Please verify your email with the OTP we sent before your account is created.',
                    'data' => [
                        'user' => null,
                        'requires_verification' => true,
                    ],
                ], 201);
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'role' => 'customer',
                'status' => 'active',
                'is_active' => true,
                'two_factor_enabled' => false,
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            if ($this->canSendCustomerMail('account_creation')) {
                try {
                    $this->sendCustomerMail(
                        $user->email,
                        'Welcome to Kanakshi.in',
                        "Your account has been created successfully.\n\nTeam Kanakshi.in"
                    );
                } catch (RuntimeException) {
                    // Non-critical welcome email failures should not invalidate a successful account creation.
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.',
                'data' => [
                    'user' => $this->serializeUser($user->fresh()),
                    'requires_verification' => false,
                ],
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->where('role', 'customer')
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'The provided customer credentials do not match our records.',
            ], 422);
        }

        $verification = $this->verificationSettings();
        $requiresEmailVerification = (bool) ($verification?->email_verification_enabled ?? true);

        if ($requiresEmailVerification && ! $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before logging in.',
                'data' => [
                    'requires_verification' => true,
                    'email' => $user->email,
                ],
            ], 403);
        }

        [$plainTextToken, $token] = $this->issueToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Customer logged in successfully.',
            'data' => [
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => optional($token->expires_at)->toIso8601String(),
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer profile fetched successfully.',
            'data' => [
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $this->resolveTokenModelFromRequest($request);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer logged out successfully.',
        ]);
    }

    public function resendVerificationOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($validated['email']);
        $pending = $this->pendingRegistration($email);
        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        if (! $pending && ! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If this email is eligible, a fresh verification OTP has been sent.',
            ]);
        }

        if ($user?->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already verified.',
            ], 422);
        }

        try {
            $this->ensureCustomerVerificationCanSend('verification');
            $this->ensureOtpResendCooldown($user?->id, $email, self::EMAIL_VERIFICATION_OTP_PURPOSE);

            $otp = $this->createOtp($user?->id, $email, self::EMAIL_VERIFICATION_OTP_PURPOSE);
            $this->sendCustomerMail(
                $email,
                'Verify your Kanakshi.in account',
                "Your verification OTP is {$otp}. It is valid for {$this->otpExpiryMinutes()} minutes.\n\nTeam Kanakshi.in"
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'A fresh verification OTP has been sent to your email.',
        ]);
    }

    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        $email = strtolower($validated['email']);
        $pending = $this->pendingRegistration($email);
        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->first();

        if (! $pending && ! $user) {
            return response()->json([
                'success' => false,
                'message' => 'The OTP is invalid or expired.',
            ], 422);
        }

        $otp = $this->resolveValidOtp($user?->id, $email, self::EMAIL_VERIFICATION_OTP_PURPOSE, $validated['code']);

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'The OTP is invalid or expired.',
            ], 422);
        }

        if (! $user) {
            if (! $pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'The OTP is invalid or expired.',
                ], 422);
            }

            $user = DB::transaction(function () use ($pending): User {
                $createdUser = User::query()->create([
                    'name' => $pending->name,
                    'email' => $pending->email,
                    'phone' => $pending->phone,
                    'role' => 'customer',
                    'status' => 'active',
                    'is_active' => true,
                    'two_factor_enabled' => false,
                    'password' => $pending->password_hash,
                    'email_verified_at' => now(),
                ]);

                DB::table('pending_customer_registrations')
                    ->where('id', $pending->id)
                    ->delete();

                return $createdUser;
            });
        } else {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $this->markOtpUsed((int) $otp->id);
        [$plainTextToken, $token] = $this->issueToken($user);

        if ($this->canSendCustomerMail('account_creation')) {
            try {
                $this->sendCustomerMail(
                    $user->email,
                    'Welcome to Kanakshi.in',
                    "Your email has been verified and your account is now active.\n\nTeam Kanakshi.in"
                );
            } catch (RuntimeException) {
                // Verification is already complete; ignore non-critical welcome mail failures.
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'data' => [
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => optional($token->expires_at)->toIso8601String(),
                'user' => $this->serializeUser($user->fresh()),
            ],
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->where('role', 'customer')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If the account is eligible, a password reset OTP has been sent to the email.',
            ]);
        }

        try {
            $this->ensureCustomerVerificationCanSend('password_reset');
            $this->ensureOtpResendCooldown($user->id, $user->email, self::PASSWORD_RESET_OTP_PURPOSE);

            $otp = $this->createOtp($user->id, $user->email, self::PASSWORD_RESET_OTP_PURPOSE);
            $this->sendCustomerMail(
                $user->email,
                'Your Kanakshi.in password reset OTP',
                "Your password reset OTP is {$otp}. It is valid for {$this->otpExpiryMinutes()} minutes.\n\nTeam Kanakshi.in"
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'A password reset OTP has been sent to your email.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->where('role', 'customer')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'The OTP is invalid or expired.',
            ], 422);
        }

        $otp = $this->resolveValidOtp($user->id, $user->email, self::PASSWORD_RESET_OTP_PURPOSE, $validated['code']);

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'The OTP is invalid or expired.',
            ], 422);
        }

        if (Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose a new password instead of reusing your current one.',
            ], 422);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $this->markOtpUsed((int) $otp->id);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful. Please log in with your new password.',
        ]);
    }

    private function resolveCustomerFromRequest(Request $request): ?User
    {
        $token = $this->resolveTokenModelFromRequest($request);

        if (! $token) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $token->user;
    }

    private function resolveTokenModelFromRequest(Request $request): ?CustomerAccessToken
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return null;
        }

        return CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    private function issueToken(User $user): array
    {
        $plainTextToken = Str::random(64);
        $token = CustomerAccessToken::query()->create([
            'user_id' => $user->id,
            'name' => 'customer-web',
            'token_hash' => hash('sha256', $plainTextToken),
            'expires_at' => now()->addDays(30),
        ]);

        return [$plainTextToken, $token];
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'pincode' => $user->pincode,
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
            'role' => $user->role,
        ];
    }

    private function verificationSettings(): ?OtpVerificationSetting
    {
        return OtpVerificationSetting::query()->first();
    }

    private function hasActiveMobileProvider(): bool
    {
        return OtpProviderSetting::query()
            ->where('is_active', true)
            ->whereIn('channel', ['sms', 'whatsapp'])
            ->exists();
    }

    private function otpLength(): int
    {
        return (int) ($this->verificationSettings()?->otp_length ?? 6);
    }

    private function otpExpiryMinutes(): int
    {
        return (int) ($this->verificationSettings()?->otp_expiry_minutes ?? 10);
    }

    private function otpResendWaitSeconds(): int
    {
        return (int) ($this->verificationSettings()?->resend_wait_seconds ?? 60);
    }

    private function createOtp(?int $userId, string $email, string $purpose): string
    {
        $length = max(4, min(8, $this->otpLength()));
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;
        $code = (string) random_int($min, $max);

        DB::table('otp_codes')->insert([
            'user_id' => $userId,
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes($this->otpExpiryMinutes()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    private function ensureOtpResendCooldown(?int $userId, string $email, string $purpose): void
    {
        $query = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->orderByDesc('id');

        if ($userId === null) {
            $query->whereNull('user_id');
        } else {
            $query->where('user_id', $userId);
        }

        $lastOtp = $query->first();

        if (! $lastOtp) {
            return;
        }

        $lastCreatedAt = Carbon::parse($lastOtp->created_at);
        $diff = now()->diffInSeconds($lastCreatedAt);
        $remaining = $this->otpResendWaitSeconds() - $diff;

        if ($remaining > 0) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => "Please wait {$remaining} seconds before requesting another OTP.",
            ], 429));
        }
    }

    private function findOtp(?int $userId, string $email, string $purpose, string $code): ?object
    {
        $query = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('code', $code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->whereColumn('attempts', '<', 'max_attempts')
            ->orderByDesc('id');

        if ($userId === null) {
            $query->whereNull('user_id');
        } else {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    private function resolveValidOtp(?int $userId, string $email, string $purpose, string $code): ?object
    {
        $query = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id');

        if ($userId === null) {
            $query->whereNull('user_id');
        } else {
            $query->where('user_id', $userId);
        }

        $latestOtp = $query->first();

        if (! $latestOtp) {
            return null;
        }

        if ((int) $latestOtp->attempts >= (int) $latestOtp->max_attempts) {
            return null;
        }

        if (! hash_equals((string) $latestOtp->code, $code)) {
            $attempts = (int) $latestOtp->attempts + 1;

            DB::table('otp_codes')
                ->where('id', $latestOtp->id)
                ->update([
                    'attempts' => $attempts,
                    'used_at' => $attempts >= (int) $latestOtp->max_attempts ? now() : null,
                    'updated_at' => now(),
                ]);

            return null;
        }

        return $latestOtp;
    }

    private function markOtpUsed(int $otpId): void
    {
        DB::table('otp_codes')
            ->where('id', $otpId)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function ensureCustomerVerificationCanSend(string $event): void
    {
        $emailSettings = CustomerEmailSetting::query()->first();
        $verification = $this->verificationSettings();

        $eventEnabled = match ($event) {
            'verification' => (bool) ($emailSettings?->send_email_verification_emails ?? false),
            'password_reset' => (bool) ($emailSettings?->send_password_reset_emails ?? false),
            default => false,
        };

        if (! $emailSettings?->is_active || ! $eventEnabled) {
            throw new RuntimeException(
                $event === 'verification'
                    ? 'Customer email verification is not configured right now.'
                    : 'Customer password reset email is not configured right now.'
            );
        }

        if (($verification?->email_otp_enabled ?? true) !== true) {
            throw new RuntimeException('Email OTP verification is disabled right now.');
        }
    }

    private function canSendCustomerMail(string $event): bool
    {
        return app(CustomerEmailService::class)->canSendAuthEvent($event);
    }

    private function sendCustomerMail(string $email, string $subject, string $body): void
    {
        app(CustomerEmailService::class)->sendAuthMail($email, $subject, $body);
    }

    private function pendingRegistration(string $email): ?object
    {
        return DB::table('pending_customer_registrations')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }

    private function upsertPendingRegistration(array $validated): void
    {
        $email = strtolower($validated['email']);

        DB::table('pending_customer_registrations')->updateOrInsert(
            ['email' => $email],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'password_hash' => Hash::make($validated['password']),
                'expires_at' => now()->addMinutes($this->otpExpiryMinutes()),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
