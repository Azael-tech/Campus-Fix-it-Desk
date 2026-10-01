@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
    <div class="page-head">
        <h1>Choose a new password</h1>
        <p>Type a new password for your account.</p>
    </div>

    <form class="form-card form-card--narrow" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @if ($errors->any())
            <div class="alert" role="alert">
                <strong>{{ $errors->first() }}</strong>
            </div>
        @endif

        <div class="form-section">
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required autocomplete="username"
                       value="{{ old('email', $email) }}" placeholder="you@example.com">
            </div>

            <div class="field">
                <label for="password">New password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required minlength="8" autofocus autocomplete="new-password">
                    <button type="button" class="toggle-password" data-toggle-password
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <p class="hint">At least 8 characters.</p>
            </div>

            <div class="field">
                <label for="password_confirmation">Type the new password again</label>
                <div class="password-wrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-toggle-password
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('login') }}">Cancel</a>
            <button type="submit" class="btn btn-primary">Change password</button>
        </div>
    </form>
@endsection