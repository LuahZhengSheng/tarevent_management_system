<!--@extends('layouts.app')-->

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Test: Choose club</h1>

    <form method="POST" action="{{ route('club.select') }}" id="clubPickForm">
        @csrf
        <select name="club_id" class="form-select" onchange="document.getElementById('clubPickForm').submit()">
            <option value="">-- Select a club --</option>
            @foreach($clubs as $club)
            <option value="{{ $club->id }}">
                {{ $club->name }} (ID: {{ $club->id }})
            </option>
            @endforeach
        </select>
    </form>

    <p class="text-muted mt-3">
        Selecting a club will open the real club page (/clubs/{id}), where the forum component should render.
    </p>
</div>

<script>
    function goClub(id) {
        if (!id)
            return;
        window.location.href = `/clubs/${id}`;
    }
</script>
@endsection
