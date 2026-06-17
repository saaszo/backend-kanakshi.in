@extends('admin.layout')

@section('title', 'Admin Login')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Kanakshi.in Admin</div>
            <h1>Admin Login</h1>
            <p class="lead">Login only for admin panel access. Signup is disabled for this panel.</p>

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

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                <div class="field">
                    <label for="email">Admin Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', 'admin@kanakshi.in') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <button class="button" type="submit">Login To Admin</button>
            </form>

            <div class="helper-links">
                <span>Admin route: <strong>/admin</strong></span>
                <a href="{{ route('admin.forgot-password.form') }}">Forgot Password?</a>
            </div>
        </div>
    </div>
@endsection
