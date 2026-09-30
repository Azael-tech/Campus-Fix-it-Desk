@extends('layouts.app')

@section('title', 'All reports')

@section('content')
    @php $isStaff = auth()->check() && auth()->user()->role === 'staff'; @endphp
    <section class="notebook">
        <h1>See something broken? Let us know.</h1>
        <p>Flickering lights, leaky faucets, wobbly chairs. Report it here and follow the fix.</p>
        <a class="btn btn-primary" href="{{ route('reports.create') }}">Report a problem</a>
    </section>

    <section class="stats" aria-label="Report totals">
        <a class="stat {{ ! request('status') ? 'is-current' : '' }}" href="{{ route('reports.index') }}">
            <span class="stat-number">{{ $stats['all'] }}</span>
            <span class="stat-label">All reports</span>
        </a>
        <a class="stat stat--pending {{ request('status') === 'pending' ? 'is-current' : '' }}" href="{{ route('reports.index', ['status' => 'pending']) }}">
            <span class="stat-number">{{ $stats['pending'] }}</span>
            <span class="stat-label">Waiting</span>
        </a>
        <a class="stat stat--in_progress {{ request('status') === 'in_progress' ? 'is-current' : '' }}" href="{{ route('reports.index', ['status' => 'in_progress']) }}">
            <span class="stat-number">{{ $stats['in_progress'] }}</span>
            <span class="stat-label">Being fixed</span>
        </a>
        <a class="stat stat--resolved {{ request('status') === 'resolved' ? 'is-current' : '' }}" href="{{ route('reports.index', ['status' => 'resolved']) }}">
            <span class="stat-number">{{ $stats['resolved'] }}</span>
            <span class="stat-label">Fixed</span>
        </a>
    </section>

    <form class="filters" method="GET" action="{{ route('reports.index') }}" data-filters>
        @if (auth()->check() && request()->boolean('mine'))
            <input type="hidden" name="mine" value="1">
        @endif
        <label class="field field--grow">
            <span class="sr-only">Search reports</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title, room, building or name">
        </label>

        <label class="field">
            <span class="sr-only">Status</span>
            <select name="status">
                <option value="">Any status</option>
                @foreach (\App\Models\MaintenanceReport::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span class="sr-only">Priority</span>
            <select name="priority">
                <option value="">Any priority</option>
                @foreach (\App\Models\MaintenanceReport::PRIORITIES as $key => $label)
                    <option value="{{ $key }}" @selected(request('priority') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span class="sr-only">Category</span>
            <select name="category">
                <option value="">Any category</option>
                @foreach (\App\Models\MaintenanceReport::CATEGORIES as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </label>

        @if (request()->hasAny(['search', 'status', 'priority', 'category']))
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Clear filters</a>
        @endif
    </form>

    @if (auth()->check() && request()->boolean('mine'))
        <p class="mine-note">Showing only the reports you sent. <a href="{{ route('reports.index') }}">Show all reports</a></p>
    @endif

    @if ($reports->isEmpty())
        <div class="empty">
            <h2>No reports match yet</h2>
            <p>Try clearing the filters, or be the first to report a problem.</p>
            <a class="btn btn-primary" href="{{ route('reports.create') }}">Report a problem</a>
        </div>
    @else
        <section class="ticket-grid" aria-label="Maintenance reports">
            @foreach ($reports as $report)
                <article class="ticket ticket--{{ $report->status }}">
                    <div class="ticket-top">
                        <span class="ref">{{ $report->reference }}</span>
                        <span class="badge badge--{{ $report->status }}">{{ $report->status_label }}</span>
                    </div>

                    @if ($report->photo_url)
                        <img class="ticket-photo" src="{{ $report->photo_url }}" alt="Photo of {{ $report->title }}" loading="lazy">
                    @endif

                    <h2 class="ticket-title">
                        <a href="{{ route('reports.show', $report) }}">{{ $report->title }}</a>
                    </h2>

                    <p class="ticket-place">{{ $report->building }}, {{ $report->room }}</p>
                    <p class="ticket-text">{{ \Illuminate\Support\Str::limit($report->description, 90) }}</p>

                    <div class="ticket-tags">
                        <span class="pill pill--{{ $report->priority }}">{{ $report->priority_label }} priority</span>
                        <span class="pill pill--plain">{{ $report->category }}</span>
                    </div>

                    <div class="ticket-foot">
                        <span class="ticket-who">{{ $report->reporter_name }}, {{ $report->created_at->diffForHumans() }}</span>
                        <div class="row-actions">
                            <a href="{{ route('reports.show', $report) }}">View</a>
                            @if ($isStaff)
                                <a href="{{ route('reports.edit', $report) }}">Edit</a>
                                <form method="POST" action="{{ route('reports.destroy', $report) }}"
                                      data-confirm="Report {{ $report->reference }} ({{ $report->title }}) will be removed for good.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{ $reports->links('pagination.custom') }}
    @endif
@endsection
