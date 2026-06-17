<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerEmailSetting;
use App\Models\EmailSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class AdminAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            if ($this->authenticatedUserCanAccessAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            $this->clearAuthenticatedSession($request);
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active || $user->status !== 'active') {
            return back()->withErrors([
                'email' => 'The provided admin credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($user->two_factor_enabled) {
            $otp = $this->createOtp($user->id, $user->email, 'two_factor');
            try {
                $this->sendOtpMail($user->email, 'Your admin login OTP', $otp);
            } catch (RuntimeException $exception) {
                return back()->withErrors([
                    'email' => $exception->getMessage(),
                ])->onlyInput('email');
            }

            $request->session()->put('admin_2fa_user_id', $user->id);
            $request->session()->put('admin_2fa_email', $user->email);

            return redirect()->route('admin.verify-otp.form')
                ->with('status', 'An OTP has been sent to your admin email.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.verify-otp', [
            'email' => $request->session()->get('admin_2fa_email'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('admin_2fa_user_id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $otp = DB::table('otp_codes')
            ->where('user_id', $userId)
            ->where('purpose', 'two_factor')
            ->where('code', $request->string('code')->toString())
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (! $otp) {
            return back()->withErrors([
                'code' => 'The OTP is invalid or expired.',
            ]);
        }

        DB::table('otp_codes')
            ->where('id', $otp->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        $user = User::query()->findOrFail($userId);
        Auth::login($user);
        $request->session()->forget(['admin_2fa_user_id', 'admin_2fa_email']);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showForgotPassword(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', 'super_admin')
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'You are not authorize person.',
            ])->onlyInput('email');
        }

        $otp = $this->createOtp($user->id, $user->email, 'forgot_password');

        try {
            $this->sendOtpMail($user->email, 'Your admin password reset OTP', $otp);
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'email' => $exception->getMessage(),
            ])->onlyInput('email');
        }

        return redirect()->route('admin.reset-password.form', ['email' => $validated['email']])
            ->with('status', 'A reset OTP has been sent to your admin email.');
    }

    public function showResetPassword(Request $request): View
    {
        return view('admin.auth.reset-password', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => [
                'required',
                'confirmed',
                Password::min(10)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', 'super_admin')
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'You are not authorize person.',
            ]);
        }

        $otp = DB::table('otp_codes')
            ->where('user_id', $user->id)
            ->where('email', $validated['email'])
            ->where('purpose', 'forgot_password')
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (! $otp) {
            return back()->withErrors([
                'code' => 'The OTP is invalid or expired.',
            ])->onlyInput('email');
        }

        $user->forceFill([
            'password' => $validated['password'],
            'login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        DB::table('otp_codes')
            ->where('id', $otp->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.login')
            ->with('status', 'Password reset successful. Please login with your new password.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function authenticatedUserCanAccessAdmin(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return in_array($user->role, ['super_admin', 'admin', 'manager', 'staff'], true);
    }

    private function clearAuthenticatedSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function createOtp(int $userId, string $email, string $purpose): string
    {
        $code = (string) random_int(100000, 999999);

        DB::table('otp_codes')->insert([
            'user_id' => $userId,
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    private function sendOtpMail(string $email, string $subject, string $otp): void
    {
        foreach ($this->mailProfilesForOtp() as $profile) {
            try {
                $this->applyMailerProfile($profile);
                $this->deliverOtpMail($email, $subject, $otp, $profile['from_address'], $profile['from_name']);
                return;
            } catch (\Throwable $throwable) {
                Log::error('Admin OTP email delivery attempt failed.', [
                    'to' => $email,
                    'subject' => $subject,
                    'mailer' => $profile['label'],
                    'host' => $profile['smtp_host'],
                    'port' => $profile['smtp_port'],
                    'encryption' => $profile['smtp_encryption'],
                    'username' => $profile['smtp_username'],
                    'error' => $throwable->getMessage(),
                ]);
            }
        }

        throw new RuntimeException('Unable to send OTP email right now. Please verify SMTP settings.');
    }

    private function deliverOtpMail(string $email, string $subject, string $otp, string $fromAddress, string $fromName): void
    {
        Mail::raw(
            "Your OTP is {$otp}. It is valid for 10 minutes.\n\nTeam Kanakshi.in",
            function ($message) use ($email, $subject, $fromAddress, $fromName): void {
                $message->to($email)
                    ->from($fromAddress, $fromName)
                    ->subject($subject);
            }
        );
    }

    private function mailProfilesForOtp(): array
    {
        $profiles = [
            $this->resolveAdminMailProfile(),
            $this->alternateTransportProfile($this->resolveAdminMailProfile(), 'admin_alt_transport'),
            $this->resolveCustomerFallbackMailProfile(),
            $this->alternateTransportProfile($this->resolveCustomerFallbackMailProfile(), 'customer_fallback_alt_transport'),
        ];

        $uniqueProfiles = [];
        $seen = [];

        foreach ($profiles as $profile) {
            if (! is_array($profile)) {
                continue;
            }

            $signature = implode('|', [
                $profile['from_address'] ?? '',
                $profile['smtp_host'] ?? '',
                (string) ($profile['smtp_port'] ?? ''),
                $profile['smtp_encryption'] ?? '',
                $profile['smtp_username'] ?? '',
            ]);

            if (isset($seen[$signature])) {
                continue;
            }

            $seen[$signature] = true;
            $uniqueProfiles[] = $profile;
        }

        return $uniqueProfiles;
    }

    private function resolveAdminMailProfile(): array
    {
        $emailSettings = EmailSetting::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        $defaultCustomerAuthAddress = env('CUSTOMER_AUTH_FROM_EMAIL', 'no-reply@kanakshi.in');
        $defaultCustomerAuthUsername = env('CUSTOMER_AUTH_SMTP_USERNAME', $defaultCustomerAuthAddress);
        $defaultCustomerAuthPassword = env('CUSTOMER_AUTH_SMTP_PASSWORD')
            ?: env('CUSTOMER_SMTP_PASSWORD')
            ?: env('MAIL_PASSWORD');

        $fromAddress = $emailSettings?->from_email
            ?: env('ADMIN_MAIL_FROM_EMAIL')
            ?: env('MAIL_FROM_ADDRESS')
            ?: $defaultCustomerAuthAddress;
        $fromName = $emailSettings?->from_name
            ?: env('ADMIN_MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Kanakshi.in Admin'));

        return [
            'label' => 'admin',
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'smtp_host' => $emailSettings?->smtp_host ?: 'smtp.hostinger.com',
            'smtp_port' => (int) ($emailSettings?->smtp_port ?: 465),
            'smtp_encryption' => $emailSettings?->smtp_encryption ?: 'ssl',
            'smtp_username' => $emailSettings?->smtp_username
                ?: env('ADMIN_SMTP_USERNAME')
                ?: env('MAIL_USERNAME')
                ?: $defaultCustomerAuthUsername,
            'smtp_password' => $emailSettings?->smtp_password
                ?: env('ADMIN_SMTP_PASSWORD')
                ?: env('SMTP_SETTINGS_PASSWORD')
                ?: env('MAIL_PASSWORD')
                ?: $defaultCustomerAuthPassword,
        ];
    }

    private function resolveCustomerFallbackMailProfile(): array
    {
        $settings = CustomerEmailSetting::query()->first();

        return [
            'label' => 'customer_fallback',
            'from_address' => $settings?->from_email ?: 'no-reply@kanakshi.in',
            'from_name' => $settings?->from_name ?: 'Kanakshi.in',
            'smtp_host' => $settings?->smtp_host ?: 'smtp.hostinger.com',
            'smtp_port' => (int) ($settings?->smtp_port ?: 465),
            'smtp_encryption' => $settings?->smtp_encryption ?: 'ssl',
            'smtp_username' => $settings?->smtp_username ?: ($settings?->from_email ?: 'no-reply@kanakshi.in'),
            'smtp_password' => $settings?->smtp_password
                ?: env('CUSTOMER_AUTH_SMTP_PASSWORD')
                ?: env('CUSTOMER_SMTP_PASSWORD')
                ?: env('SMTP_SETTINGS_PASSWORD'),
        ];
    }

    private function alternateTransportProfile(array $profile, string $label): array
    {
        $alternate = $profile;
        $alternate['label'] = $label;

        $encryption = strtolower((string) ($profile['smtp_encryption'] ?? 'ssl'));
        $port = (int) ($profile['smtp_port'] ?? 465);

        if ($encryption === 'ssl' || $port === 465) {
            $alternate['smtp_encryption'] = 'tls';
            $alternate['smtp_port'] = 587;
        } else {
            $alternate['smtp_encryption'] = 'ssl';
            $alternate['smtp_port'] = 465;
        }

        return $alternate;
    }

    private function applyMailerProfile(array $profile): void
    {
        $smtpScheme = match (strtolower((string) ($profile['smtp_encryption'] ?? 'ssl'))) {
            'ssl' => 'smtps',
            'tls' => 'tls',
            default => null,
        };

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $profile['smtp_host'],
            'mail.mailers.smtp.port' => $profile['smtp_port'],
            'mail.mailers.smtp.scheme' => $smtpScheme,
            'mail.mailers.smtp.encryption' => $profile['smtp_encryption'],
            'mail.mailers.smtp.username' => $profile['smtp_username'],
            'mail.mailers.smtp.password' => $profile['smtp_password'],
            'mail.from.address' => $profile['from_address'],
            'mail.from.name' => $profile['from_name'],
        ]);
    }
}
