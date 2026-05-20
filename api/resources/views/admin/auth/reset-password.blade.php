@extends('admin.layout')

@section('title', 'Reset Password')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Reset Admin Password</div>
            <h1>Set New Password</h1>
            <p class="lead">Use the OTP you received by email and set a strong admin password.</p>

            @if (session('status'))
                <div class="message">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.reset-password.attempt') }}">
                @csrf
                <div class="field">
                    <label for="email">Admin Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
                </div>

                <div class="field">
                    <label for="code">OTP Code</label>
                    <input id="code" type="text" name="code" maxlength="6" required>
                </div>

                <div class="field">
                    <label for="password">New Password</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <button class="button" type="submit">Reset Password</button>
            </form>

            <ul class="rule-list">
                <li>Minimum 10 characters</li>
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character</li>
            </ul>

            <div class="helper-links">
                <a href="{{ route('admin.login') }}">Back to Login</a>
            </div>
        </div>
    </div>
@endsection
