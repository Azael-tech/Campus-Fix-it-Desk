@extends('layouts.app')

@section('title', 'Staff login')

@section('content')
    <div class="page-head">
        <h1>Staff login</h1>
        <p>Only the maintenance team can edit, delete, or change the status of reports.</p>
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
                       value="{{ old('email') }}" placeholder="staff@school.test">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <label class="check">
                <input type="checkbox" name="remember" value="1"> Keep me logged in on this computer
            </label>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Back to reports</a>
            <button type="submit" class="btn btn-primary">Log in</button>
        </div>
    </form>
@endsection
