@extends('layouts.app')

@section('title', 'Report a problem')

@section('content')
    <div class="page-head">
        <a class="back" href="{{ route('reports.index') }}">Back to all reports</a>
        <h1>Report a problem</h1>
        <p>The more detail you share, the faster the maintenance team can fix it.</p>
    </div>

    <form class="form-card" method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" data-submit-lock>
        @csrf
        @include('reports._form')

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Cancel</a>
            <button type="submit" class="btn btn-primary">Send report</button>
        </div>
    </form>
@endsection
