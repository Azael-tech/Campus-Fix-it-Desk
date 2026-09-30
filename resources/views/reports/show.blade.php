@extends('layouts.app')

@section('title', $report->reference)

@section('content')
    @php $isStaff = auth()->check() && auth()->user()->role === 'staff'; @endphp
    <div class="page-head">
        <a class="back" href="{{ route('reports.index') }}">Back to all reports</a>
    </div>

    <article class="detail ticket--{{ $report->status }}">
        <header class="detail-head">
            <div>
                <span class="ref">{{ $report->reference }}</span>
                <h1>{{ $report->title }}</h1>
                <p class="ticket-place">{{ $report->building }}, {{ $report->room }}</p>
            </div>
            <span class="badge badge--{{ $report->status }} badge--big">{{ $report->status_label }}</span>
        </header>

        <div class="ticket-tags">
            <span class="pill pill--{{ $report->priority }}">{{ $report->priority_label }} priority</span>
            <span class="pill pill--plain">{{ $report->category }}</span>
        </div>

        @if ($report->photo_url)
            <a href="{{ $report->photo_url }}" target="_blank" rel="noopener">
                <img class="detail-photo" src="{{ $report->photo_url }}" alt="Photo of {{ $report->title }}">
            </a>
        @endif

        <h2 class="detail-sub">What happened</h2>
        <p class="detail-text">{{ $report->description }}</p>

        <dl class="facts">
            <div>
                <dt>Reported by</dt>
                <dd>{{ $report->reporter_name }} ({{ $report->reporter_role }})</dd>
            </div>
            <div>
                <dt>Sent on</dt>
                <dd>{{ $report->created_at->format('M d, Y g:i A') }}</dd>
            </div>
            <div>
                <dt>Assigned to</dt>
                <dd>{{ $report->assigned_to ?: 'Not assigned yet' }}</dd>
            </div>
            <div>
                <dt>Fixed on</dt>
                <dd>{{ $report->resolved_at ? $report->resolved_at->format('M d, Y g:i A') : 'Not fixed yet' }}</dd>
            </div>
            @if ($isStaff)
                <div>
                    <dt>Reporter email</dt>
                    <dd>{{ $report->reporter_email ?: 'None given' }}</dd>
                </div>
            @endif
        </dl>

        @if ($isStaff)
            <div class="status-bar">
                <span class="label">Change status</span>
                <div class="status-buttons">
                    @foreach (\App\Models\MaintenanceReport::STATUSES as $key => $label)
                        <form method="POST" action="{{ route('reports.status', $report) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $key }}">
                            <button type="submit"
                                    class="btn btn-status btn-status--{{ $key }} {{ $report->status === $key ? 'is-on' : '' }}"
                                    @disabled($report->status === $key)>{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
                @if ($report->reporter_email && $report->status !== 'resolved')
                    <p class="hint">The reporter will get an email when you choose Resolved.</p>
                @endif
            </div>

            <footer class="detail-actions">
                <a class="btn btn-primary" href="{{ route('reports.edit', $report) }}">Edit report</a>
                <form method="POST" action="{{ route('reports.destroy', $report) }}"
                      data-confirm="Report {{ $report->reference }} ({{ $report->title }}) will be removed for good.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline">Delete report</button>
                </form>
            </footer>
        @else
            <p class="staff-note">
                @guest
                    Maintenance staff can <a href="{{ route('login') }}">log in</a> to update or delete this report.
                @else
                    Only maintenance staff can update or delete this report.
                @endguest
            </p>
        @endif
    </article>
@endsection
