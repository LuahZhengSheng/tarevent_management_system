@extends('layouts.app')

@section('title', $club->name . ' - Club Details')

@push('styles')
@include('clubs.join-modal')
@if($isMember)
<!--@vite([
'resources/css/forums/forum.css',
'resources/css/forums/forum-media-gallery.css',
'resources/css/forums/media-lightbox.css'
])-->
@endif
<style>
    .club-detail-page {
        min-height: 100vh;
        background: var(--bg-primary, #f8f9fa);
    }

    .club-hero {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: flex-end;
        padding: 3rem 0;
        background: linear-gradient(135deg, var(--primary, #4f46e5) 0%, var(--primary-hover, #6366f1) 100%);
        color: white;
        overflow: hidden;
    }

    .club-hero-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 0;
        object-fit: cover;
        width: 100%;
        height: 100%;
        opacity: 0.3;
    }

    .club-hero-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, var(--primary, #4f46e5) 0%, var(--primary-hover, #6366f1) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 0;
    }

    .club-hero-placeholder i {
        font-size: 8rem;
        opacity: 0.3;
    }

    .club-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.6) 100%);
        z-index: 1;
    }

    .club-hero-content {
        position: relative;
        z-index: 2;
        width: 100%;
    }

    .breadcrumb-custom {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem 0;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .breadcrumb-custom li {
        display: flex;
        align-items: center;
    }

    .breadcrumb-custom li:not(:last-child)::after {
        content: '/';
        margin-left: 0.5rem;
        opacity: 0.7;
    }

    .breadcrumb-custom a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb-custom a:hover {
        color: white;
    }

    .breadcrumb-custom li:last-child {
        color: white;
        font-weight: 600;
    }

    .club-hero-main {
        display: flex;
        align-items: flex-end;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .club-logo-large {
        width: 150px;
        height: 150px;
        border-radius: 20px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        flex-shrink: 0;
    }

    .club-logo-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .club-logo-large i {
        font-size: 4rem;
        color: var(--primary, #4f46e5);
    }

    .club-hero-info {
        flex: 1;
        min-width: 300px;
    }

    .club-meta-tags {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .meta-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .club-title-hero {
        font-size: 3rem;
        font-weight: 800;
        margin: 0 0 1rem 0;
        line-height: 1.2;
    }

    .club-stats-row {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    .club-stat-item {
        display: flex;
        flex-direction: column;
    }

    .club-stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    .club-stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.5rem;
    }

    .club-content-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 3rem 1rem;
    }

    .content-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #212529;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        color: var(--primary, #4f46e5);
    }

    .club-description-text {
        font-size: 1.125rem;
        line-height: 1.8;
        color: #495057;
        white-space: pre-wrap;
    }

    .club-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1.125rem;
        color: #212529;
        font-weight: 500;
    }

    .announcements-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .announcement-card {
        padding: 1.5rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .announcement-card:hover {
        border-color: var(--primary, #4f46e5);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
    }

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        gap: 1rem;
    }

    .announcement-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #212529;
        margin: 0;
    }

    .announcement-date {
        font-size: 0.875rem;
        color: #6c757d;
        white-space: nowrap;
    }

    .announcement-content {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }

    .announcement-image {
        margin-top: 1rem;
        border-radius: 8px;
        overflow: hidden;
    }

    .announcement-image img {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .btn-primary-lg, .btn-secondary-lg {
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary-lg {
        background: var(--primary, #4f46e5);
        color: white;
    }

    .btn-primary-lg:hover {
        background: var(--primary-hover, #6366f1);
        color: white;
    }

    .btn-secondary-lg {
        background: white;
        color: var(--primary, #4f46e5);
        border: 2px solid var(--primary, #4f46e5);
    }

    .btn-secondary-lg:hover {
        background: var(--primary-light, #eef2ff);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .club-title-hero {
            font-size: 2rem;
        }

        .club-hero-main {
            flex-direction: column;
            align-items: flex-start;
        }

        .club-logo-large {
            width: 120px;
            height: 120px;
        }
    }
</style>
@endpush

@section('content')
<div class="club-detail-page">
    <!-- Hero Section -->
    <div class="club-hero">
        @if($club->background_image)
        <img src="{{ asset('storage/' . $club->background_image) }}" 
             alt="{{ $club->name }}" 
             class="club-hero-background">
        @else
        <div class="club-hero-placeholder">
            <i class="bi bi-people"></i>
        </div>
        @endif
        <div class="club-hero-overlay"></div>

        <div class="container club-hero-content">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('clubs.index') }}">Clubs</a></li>
                    <li>{{ Str::limit($club->name, 30) }}</li>
                </ol>
            </nav>

            <div class="club-hero-main">
                <!-- Club Logo -->
                <div class="club-logo-large">
                    @if($club->logo)
                    <img src="{{ asset('storage/' . $club->logo) }}" alt="{{ $club->name }}">
                    @else
                    <i class="bi bi-people"></i>
                    @endif
                </div>

                <!-- Club Info -->
                <div class="club-hero-info">
                    <!-- Meta Tags -->
                    <div class="club-meta-tags">
                        @if($club->category)
                        <span class="meta-tag">
                            <i class="bi bi-bookmark-fill me-1"></i>
                            {{ ucfirst($club->category) }}
                        </span>
                        @endif
                        @if($isMember)
                        <span class="meta-tag">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Member
                            @if($memberRole)
                            ({{ ucfirst($memberRole) }})
                            @endif
                        </span>
                        @endif
                    </div>

                    <!-- Club Title -->
                    <h1 class="club-title-hero">{{ $club->name }}</h1>

                    <!-- Stats -->
                    <div class="club-stats-row">
                        <div class="club-stat-item">
                            <div class="club-stat-value">{{ $stats['members_count'] }}</div>
                            <div class="club-stat-label">Members</div>
                        </div>
                        <div class="club-stat-item">
                            <div class="club-stat-value">{{ $stats['announcements_count'] }}</div>
                            <div class="club-stat-label">Announcements</div>
                        </div>
                        <div class="club-stat-item">
                            <div class="club-stat-value">{{ $stats['events_count'] }}</div>
                            <div class="club-stat-label">Events</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container club-content-container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-12">
                <!-- About Section -->
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="bi bi-info-circle"></i>
                        About
                    </h2>
                    @if($club->description)
                    <div class="club-description-text">{{ $club->description }}</div>
                    @endif

                    <!-- Club Information -->
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e9ecef;">
                        <h3 class="section-title" style="font-size: 1.25rem; margin-bottom: 1.5rem;">
                            <i class="bi bi-info-square"></i>
                            Club Information
                        </h3>
                        <div class="club-info-grid">
                            @if($club->email)
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <a href="mailto:{{ $club->email }}">{{ $club->email }}</a>
                                </div>
                            </div>
                            @endif

                            @if($club->phone)
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $club->phone }}</div>
                            </div>
                            @endif

                            @if($club->category)
                            <div class="info-item">
                                <div class="info-label">Category</div>
                                <div class="info-value">{{ ucfirst($club->category) }}</div>
                            </div>
                            @endif

                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Leadership Members Section -->
                        @if($leadershipMembers->count() > 0)
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e9ecef;">
                            <h3 class="section-title" style="font-size: 1.25rem; margin-bottom: 1rem;">
                                <i class="bi bi-people"></i>
                                Leadership
                            </h3>
                            <div class="leadership-list">
                                @foreach($leadershipMembers as $leader)
                                <div class="leadership-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                                    <div class="leadership-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light, #eef2ff); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; position: relative;">
                                        @if(!empty($leader['profile_photo_url']))
                                            <img src="{{ $leader['profile_photo_url'] }}" alt="{{ $leader['name'] }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <i class="bi bi-person" style="color: var(--primary, #4f46e5); display: none;"></i>
                                        @else
                                            <i class="bi bi-person" style="color: var(--primary, #4f46e5);"></i>
                                        @endif
                                    </div>
                                    <div class="leadership-info" style="flex: 1;">
                                        <div style="font-weight: 600; color: #212529;">{{ $leader['name'] }}</div>
                                        <div style="font-size: 0.875rem; color: #6c757d;">{{ $leader['role_display'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Announcements Section - Only visible to members -->
                @if($isMember && $recentAnnouncements->count() > 0)
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="bi bi-megaphone"></i>
                        Recent Announcements
                    </h2>
                    <div class="announcements-list">
                        @foreach($recentAnnouncements as $announcement)
                        <div class="announcement-card">
                            <div class="announcement-header">
                                <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                <div class="announcement-date">
                                    {{ $announcement->published_at->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="announcement-content">
                                {{ Str::limit(strip_tags($announcement->content), 200) }}
                            </div>
                            @if($announcement->image)
                            <div class="announcement-image">
                                <img src="{{ asset('storage/' . $announcement->image) }}" 
                                     alt="{{ $announcement->title }}">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            <!-- Forum Posts Section -->
            @if($isMember)
            <div class="content-section">
                <h2 class="section-title"><i class="bi bi-chat-dots"></i> Club Forum</h2>

                @include('forums.partials.club_post_feed', [
                'club' => $club,
                'bearerToken' => $bearerToken ?? null,
                ])
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons Sidebar -->
    <div class="container club-content-container">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="content-section">
                    <div class="action-buttons">
                    @if($isMember)
                        {{-- User is already a member, no action button needed --}}
                    @elseif($joinStatus && $joinStatus['status'] === 'pending')
                        <button class="btn-secondary-lg" disabled>
                            <i class="bi bi-clock"></i>
                            Request Pending
                        </button>
                    @elseif($joinStatus && in_array($joinStatus['status'], ['rejected', 'removed']))
                        @if($joinStatus['cooldown_remaining_days'] && $joinStatus['cooldown_remaining_days'] > 0)
                        <button class="btn-secondary-lg" disabled>
                            <i class="bi bi-x-circle"></i>
                            Cooldown: {{ $joinStatus['cooldown_remaining_days'] }} day(s) left
                        </button>
                        @else
                        <button onclick="openJoinModal({{ $club->id }})" class="btn-primary-lg">
                            <i class="bi bi-person-plus"></i>
                            Join Club
                        </button>
                        @endif
                    @else
                    <button onclick="openJoinModal({{ $club->id }})" class="btn-primary-lg">
                        <i class="bi bi-person-plus"></i>
                        Join Club
                    </button>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($isMember)
@vite([
'resources/js/forum.js',
'resources/js/post-feed.js',
'resources/js/media-lightbox.js'
])
@endif

<script>
    function openJoinModal(clubId) {
    if (typeof window.openJoinClubModal === 'function') {
    window.openJoinClubModal(clubId, function(joinedClubId) {
    // Reload page after successful join to update status
    window.location.reload();
    });
    } else {
    alert('Join functionality is being loaded. Please try again in a moment.');
    }
    }
</script>
@endpush

