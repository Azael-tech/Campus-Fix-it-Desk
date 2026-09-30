@extends('layouts.app')

@section('title', 'Monthly summary')

@section('content')
    <div class="page-head no-print">
        <h1>Monthly summary</h1>
        <p>A one-page report for the school administrator. Pick a month, then print it or save it as a PDF.</p>
    </div>

    <form class="month-bar no-print" method="GET" action="{{ route('reports.summary') }}">
        <a class="btn btn-ghost" href="{{ route('reports.summary', ['month' => $prevMonth]) }}">Previous month</a>
        <label class="field">
            <span class="sr-only">Month</span>
            <input type="month" name="month" value="{{ $month }}" data-autosubmit>
        </label>
        <a class="btn btn-ghost" href="{{ route('reports.summary', ['month' => $nextMonth]) }}">Next month</a>
        <button type="button" class="btn btn-primary month-print" data-print>Print or save as PDF</button>
    </form>

    <div class="sheet">
        <header class="sheet-head">
            <h2>Campus Maintenance Report: {{ $monthLabel }}</h2>
            <p>Prepared on {{ now()->format('F d, Y') }} by {{ auth()->user()->name }}</p>
        </header>

        <section class="sum-stats">
            <div class="sum-stat">
                <span class="stat-number">{{ $reports->count() }}</span>
                <span class="stat-label">Reported this month</span>
            </div>
            <div class="sum-stat">
                <span class="stat-number">{{ $fixedCount }}</span>
                <span class="stat-label">Fixed this month</span>
            </div>
            <div class="sum-stat">
                <span class="stat-number">{{ $openNow }}</span>
                <span class="stat-label">Still open right now</span>
            </div>
            <div class="sum-stat">
                <span class="stat-number">{{ $avgDays !== null ? $avgDays : '-' }}</span>
                <span class="stat-label">Average days to fix</span>
            </div>
        </section>

        <div class="sum-grid">
            @include('reports._bars', ['title' => 'By status', 'rows' => $statusRows])
            @include('reports._bars', ['title' => 'By priority', 'rows' => $priorityRows])
            @include('reports._bars', ['title' => 'By category', 'rows' => $categoryRows])
            @include('reports._bars', ['title' => 'By building', 'rows' => $buildingRows])
        </div>

        <section class="sum-block">
            <h2>All reports sent in {{ $monthLabel }}</h2>

            @if ($reports->isEmpty())
                <p class="hint">No reports were sent this month.</p>
            @else
                <div class="table-wrap">
                    <table class="sum-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Problem</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td>{{ $report->reference }}</td>
                                    <td>{{ $report->title }}</td>
                                    <td>{{ $report->building }}, {{ $report->room }}</td>
                                    <td>{{ $report->category }}</td>
                                    <td>{{ $report->priority_label }}</td>
                                    <td>{{ $report->status_label }}</td>
                                    <td>{{ $report->created_at->format('M d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
