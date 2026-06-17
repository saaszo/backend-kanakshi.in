@extends('admin.layout')

@section('title', 'Forgot Password')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Password Recovery</div>
            <h1>Forgot Password</h1>
            <p class="lead">We will send a one-time OTP to the admin email so you can reset your password.</p>

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

            <form method="POST" action="{{ route('admin.forgot-password.send') }}">
                @csrf
                <div class="field">
                    <label for="email">Admin Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', 'admin@kanakshi.in') }}" required autofocus>
                </div>

                <button class="button" type="submit">Send Reset OTP</button>
            </form>

            <div class="helper-links">
                <a href="{{ route('admin.login') }}">Back to Login</a>
            </div>
        </div>
    </div>
@endsection
