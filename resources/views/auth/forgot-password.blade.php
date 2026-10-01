@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <div class="page-head">
        <h1>Forgot your password?</h1>
        <p>Type your account email and we will send you a link to choose a new password.</p>
    </div>

    <form class="form-card form-card--narrow" method="POST" action="{{ route('password.email') }}">
        @csrf

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

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
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('login') }}">Back to log in</a>
            <button type="submit" class="btn btn-primary">Send reset link</button>
        </div>
    </form>
@endsection