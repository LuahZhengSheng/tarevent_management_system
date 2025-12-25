{{-- resources/views/test/forum_user_stats.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1>Forum User Stats API Result</h1>

        <pre style="background:#f8f9fa;padding:1rem;border-radius:0.5rem;">
{{ json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>
@endsection
