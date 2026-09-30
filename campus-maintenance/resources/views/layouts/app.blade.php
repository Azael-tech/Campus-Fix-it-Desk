<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Reports') | Campus Fix-It Desk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="{{ route('reports.index') }}">
                <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true">
                    <rect width="32" height="32" rx="9" fill="#ffc629"/>
                    <path d="M9 23l2-6 9-9 4 4-9 9-6 2z" fill="#16296b"/>
                    <path d="M20 8l4 4" stroke="#ffc629" stroke-width="2"/>
                </svg>
                <span>Campus Fix-It Desk</span>
            </a>
            <nav class="topnav" aria-label="Main">
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.index') ? 'is-active' : '' }}">All reports</a>
                @auth
                    <a href="{{ route('reports.summary') }}" class="{{ request()->routeIs('reports.summary') ? 'is-active' : '' }}">Monthly summary</a>
                @endauth
                <a href="{{ route('reports.create') }}" class="btn btn-pencil">Report a problem</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-link-btn">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'is-active' : '' }}">Staff login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="container">
        @if (session('success'))
            <div class="toast" role="status">
                <span>{{ session('success') }}</span>
                <button type="button" class="toast-close" aria-label="Dismiss message">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            Help us keep the school safe and comfortable. Every report gets a reference number you can follow.
        </div>
    </footer>

    <dialog id="confirmDialog" class="dialog">
        <h2>Delete this report?</h2>
        <p data-dialog-text>This cannot be undone.</p>
        <div class="dialog-actions">
            <button type="button" class="btn btn-ghost" data-cancel>Keep report</button>
            <button type="button" class="btn btn-danger" data-ok>Delete report</button>
        </div>
    </dialog>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
