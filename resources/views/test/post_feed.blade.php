{{-- resources/views/test/post_feed.blade.php --}}
@extends('layouts.app')

@section('title', 'Test Post Feed')

@push('styles')
@vite([
'resources/css/forums/forum.css',
'resources/css/forums/forum-media-gallery.css',
'resources/css/forums/media-lightbox.css'
])
@endpush

@section('content')
<div class="container py-4">
    <x-post-feed
        api-url="{{ route('api.v1.clubs.posts', ['club' => $club->id]) }}"
        :initial-posts="null"
        :show-filters="true"
        />
</div>
@endsection

@push('scripts')
@vite([
'resources/js/forum.js',       {{-- UI effects only：不要包含旧 infinite/AJAX loader --}}
'resources/js/post-feed.js',   {{-- 关键：AJAX + infinite scroll --}}
'resources/js/media-lightbox.js'
])
@endpush
