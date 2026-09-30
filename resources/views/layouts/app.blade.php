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
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
    <script src="{{ asset('js/dark-mode.js') }}"></script>

</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="{{ route('reports.index') }}">
                <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true">
                    <rect width="32" height="32" rx="9" fill="#ffc629"/>
                    <path d="M9 23l2-6 9-9 4 4-9 9z" fill="#16296b"/>
                    <path d="M20 8l4 4" stroke="#ffc629" stroke-width="2"/>
                </svg>
                <span>Campus Fix-It Desk</span>
            </a>
            <nav class="topnav" aria-label="Main">
                @php $isStaff = auth()->check() && auth()->user()->role === 'staff'; @endphp
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.index') && ! request('mine') ? 'is-active' : '' }}">All reports</a>
                @if ($isStaff)
                    <a href="{{ route('reports.summary') }}" class="{{ request()->routeIs('reports.summary') ? 'is-active' : '' }}">Monthly summary</a>
                @elseif (auth()->check())
                    <a href="{{ route('reports.index', ['mine' => 1]) }}" class="{{ request('mine') ? 'is-active' : '' }}">My reports</a>
                @endif
                <a href="{{ route('reports.create') }}" class="btn btn-pencil">Report a problem</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-link-btn">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'is-active' : '' }}">Log in</a>
                    <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'is-active' : '' }}">Sign up</a>
                @endauth
                <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Switch to dark mode" title="Switch to dark mode">
                    <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
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

        @if (session('error'))
            <div class="alert" role="alert">{{ session('error') }}</div>
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
