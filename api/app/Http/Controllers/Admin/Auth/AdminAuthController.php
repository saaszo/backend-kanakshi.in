<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
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
            $this->sendOtpMail($user->email, 'Your admin login OTP', $otp);

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
            ->whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])
            ->first();

        if ($user) {
            $otp = $this->createOtp($user->id, $user->email, 'forgot_password');
            $this->sendOtpMail($user->email, 'Your admin password reset OTP', $otp);
        }

        return redirect()->route('admin.reset-password.form', ['email' => $validated['email']])
            ->with('status', 'If the admin email exists, a reset OTP has been sent.');
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
            ->whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No admin account found for this email.',
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
        Mail::raw(
            "Your OTP is {$otp}. It is valid for 10 minutes.\n\nTeam Little Divinity",
            function ($message) use ($email, $subject): void {
                $message->to($email)->subject($subject);
            }
        );
    }
}
