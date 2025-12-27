@extends('layouts.club')

@section('title', 'Forum - TAREvent')

@push('styles')
@vite([
  'resources/css/forums/forum.css',
  'resources/css/forums/forum-media-gallery.css',
  'resources/css/forums/media-lightbox.css'
])
@endpush

@section('content')
<div class="club-forum-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Forum</span>
                    </div>
                    <h1 class="page-title">Club Forum</h1>
                    <p class="page-description">
                        @if($club)
                            View and manage forum posts for {{ $club->name }}
                        @else
                            View and manage forum posts
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        @if(!$club)
        <div class="forum-placeholder">
            <div class="empty-state">
                <i class="bi bi-exclamation-triangle empty-icon"></i>
                <h3>Club Not Found</h3>
                <p class="empty-text">Your account is not associated with any club.</p>
                <p class="text-muted small">Please contact an administrator to associate your account with a club.</p>
            </div>
        </div>
        @elseif(config('app.debug') && Route::has('api.v1.clubs.posts'))
        <x-post-feed
            api-url="{{ route('api.v1.clubs.posts', ['club' => $club->id]) }}"
            :initial-posts="null"
            :show-filters="true"
        />
        @else
        <div class="forum-placeholder">
            <div class="empty-state">
                <i class="bi bi-chat-dots empty-icon"></i>
                <h3>Forum Module (Reserved)</h3>
                <p class="empty-text">Forum functionality will be integrated here.</p>
                <p class="text-muted small">This section is reserved for the Forum module integration.</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
@vite([
  'resources/js/forum.js',
  'resources/js/post-feed.js',
  'resources/js/media-lightbox.js'
])
@endpush
@endsection

