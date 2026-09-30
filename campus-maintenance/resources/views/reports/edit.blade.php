@extends('layouts.app')

@section('title', 'Edit ' . $report->reference)

@section('content')
    <div class="page-head">
        <a class="back" href="{{ route('reports.show', $report) }}">Back to {{ $report->reference }}</a>
        <h1>Edit report {{ $report->reference }}</h1>
        <p>Update the details or record what the maintenance team has done.</p>
    </div>

    <form class="form-card" method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data" data-submit-lock>
        @csrf
        @method('PUT')
        @include('reports._form', ['report' => $report])

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('reports.show', $report) }}">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
@endsection
