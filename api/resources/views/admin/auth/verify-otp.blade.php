@extends('admin.layout')

@section('title', 'Verify OTP')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Two-Step Verification</div>
            <h1>Verify OTP</h1>
            <p class="lead">Enter the 6-digit OTP sent to <strong>{{ $email }}</strong>.</p>

            @if (session('status'))
                <div class="admin-toast">
    <div>
        <strong>Success!</strong>
        <p>{{ session('status') }}</p>
    </div>
</div>
            @endif

            @if ($errors->any())
                <div class="admin-errors">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.verify-otp.attempt') }}">
                @csrf
                <div class="field">
                    <label for="code">OTP Code</label>
                    <input id="code" type="text" name="code" maxlength="6" required autofocus>
                </div>

                <button class="button" type="submit">Verify & Continue</button>
            </form>

            <div class="helper-links">
                <a href="{{ route('admin.login') }}">Back to Login</a>
            </div>
        </div>
    </div>
@endsection
