@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="page-head">
        <h1>Log in</h1>
        <p>Log in to see the reports you sent. Maintenance staff also get tools to update them.</p>
    </div>

    <form class="form-card form-card--narrow" method="POST" action="{{ route('login') }}">
        @csrf

        @if ($errors->any())
            <div class="alert" role="alert">
                <strong>{{ $errors->first() }}</strong>
            </div>
        @endif

        <div class="form-section">
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="username"
                       value="{{ old('email') }}" placeholder="you@example.com">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="button" class="toggle-password" data-toggle-password
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
            </div>

            <div class="login-options">
                <label class="check">
                    <input type="checkbox" name="remember" value="1"> Keep me logged in
                </label>
                <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Back to reports</a>
            <button type="submit" class="btn btn-primary">Log in</button>
        </div>
    </form>

    <p class="alt-link">New here? <a href="{{ route('register') }}">Create an account</a></p>
@endsection