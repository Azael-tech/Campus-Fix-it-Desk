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
            <div class="name-grid">
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" type="text" required maxlength="60" autofocus autocomplete="family-name"
                           value="{{ old('last_name') }}" placeholder="e.g. Santos">
                    @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" type="text" required maxlength="60" autocomplete="given-name"
                           value="{{ old('first_name') }}" placeholder="e.g. Maria">
                    @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="middle_initial">Middle initial</label>
                    <input id="middle_initial" name="middle_initial" type="text" maxlength="1" autocomplete="additional-name"
                           value="{{ old('middle_initial') }}" placeholder="e.g. D" style="text-transform: uppercase;">
                    @error('middle_initial') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled @selected(! old('gender'))>Choose one</option>
                        @foreach ($genders as $g)
                            <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('gender') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="suffix">Suffix</label>
                    <select id="suffix" name="suffix">
                        <option value="">None</option>
                        @foreach ($suffixes as $s)
                            <option value="{{ $s }}" @selected(old('suffix') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('suffix') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required maxlength="150" autocomplete="username"
                       value="{{ old('email') }}" placeholder="you@example.com">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-toggle-password
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
                <p class="hint">At least 8 characters.</p>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Type the password again</label>
                <div class="password-wrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-toggle-password
                            aria-label="Show password" aria-pressed="false">Show</button>
                </div>
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