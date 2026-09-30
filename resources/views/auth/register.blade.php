@extends('layouts.app')

@section('title', 'Sign up')

@section('content')
    <div class="page-head">
        <h1>Create an account</h1>
        <p>With an account, your name and email are filled in for you and you can see the reports you sent.</p>
    </div>

    <form class="form-card form-card--narrow" method="POST" action="{{ route('register') }}">
        @csrf

        @if ($errors->any())
            <div class="alert" role="alert">
                <strong>Please fix {{ $errors->count() === 1 ? 'this problem' : 'these ' . $errors->count() . ' problems' }} and try again.</strong>
            </div>
        @endif

        <div class="form-section">
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" required maxlength="100" autofocus autocomplete="name"
                       value="{{ old('name') }}" placeholder="e.g. Maria Santos">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required maxlength="150" autocomplete="username"
                       value="{{ old('email') }}" placeholder="you@example.com">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                <p class="hint">At least 8 characters.</p>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Type the password again</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>

            <div class="field">
                <label for="staff_code">Staff code (optional)</label>
                <input id="staff_code" name="staff_code" type="text" maxlength="100" autocomplete="off"
                       value="{{ old('staff_code') }}">
                <p class="hint">Only for the maintenance team. Students, teachers, and parents can leave this empty.</p>
                @error('staff_code') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Cancel</a>
            <button type="submit" class="btn btn-primary">Create account</button>
        </div>
    </form>

    <p class="alt-link">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
