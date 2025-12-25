<!-- Author: Auto-generated -->
@extends('layouts.admin')

@section('title', 'Club Details')

@section('content')
<div class="admin-club-detail-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.clubs.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Clubs
        </a>
    </div>

    <!-- Club Header -->
    <div class="admin-club-header-card mb-4">
        <div class="club-header-content">
            <div class="club-header-logo">
                <img 
                    src="{{ $club->logo ? '/storage/' . $club->logo : asset('images/default-club-avatar.png') }}" 
                    alt="{{ $club->name }}"
                    class="club-header-logo-img"
                    onerror="this.src='{{ asset('images/default-club-avatar.png') }}'"
                >
            </div>
            <div class="club-header-info">
                <h1 class="club-header-name">{{ $club->name }}</h1>
                <div class="club-header-meta">
                    @if($club->category)
                    <span class="category-badge">{{ ucfirst($club->category) }}</span>
                    @endif
                    <span class="status-badge {{ $club->status }}">
                        <i class="bi bi-{{ $club->status === 'active' ? 'check-circle' : ($club->status === 'inactive' ? 'pause-circle' : ($club->status === 'pending' ? 'hourglass-split' : 'x-circle')) }}"></i>
                        {{ ucfirst($club->status) }}
                    </span>
                </div>
            </div>
            <div class="club-header-actions">
                @if($club->status === 'active')
                <form action="{{ route('admin.clubs.deactivate', $club) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-toggle-status-detail btn-deactivate" onclick="return confirm('Are you sure you want to deactivate this club?')">
                        <i class="bi bi-pause-circle me-2"></i>Deactivate
                    </button>
                </form>
                @elseif($club->status === 'inactive')
                <form action="{{ route('admin.clubs.activate', $club) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-toggle-status-detail btn-activate">
                        <i class="bi bi-play-circle me-2"></i>Activate
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.clubs.logs', $club) }}" class="btn-view-logs">
                    <i class="bi bi-clock-history me-2"></i>View Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Total Members</div>
                    <div class="admin-stat-value">{{ $stats['total_members'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Pending Requests</div>
                    <div class="admin-stat-value">{{ $stats['pending_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-info">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Total Announcements</div>
                    <div class="admin-stat-value">{{ $stats['total_announcements'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Published</div>
                    <div class="admin-stat-value">{{ $stats['published_announcements'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Club Details -->
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="admin-detail-card">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-info-circle me-2"></i>Basic Information
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <div class="detail-row">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $club->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Slug</div>
                        <div class="detail-value">{{ $club->slug ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Description</div>
                        <div class="detail-value">{{ $club->description ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Category</div>
                        <div class="detail-value">
                            @if($club->category)
                            <span class="category-badge">{{ ucfirst($club->category) }}</span>
                            @else
                            –
                            @endif
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $club->email ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $club->phone ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge {{ $club->status }}">
                                <i class="bi bi-{{ $club->status === 'active' ? 'check-circle' : ($club->status === 'inactive' ? 'pause-circle' : ($club->status === 'pending' ? 'hourglass-split' : 'x-circle')) }}"></i>
                                {{ ucfirst($club->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created At</div>
                        <div class="detail-value">{{ $club->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created By</div>
                        <div class="detail-value">{{ $club->creator->name ?? '–' }}</div>
                    </div>
                    @if($club->approved_at)
                    <div class="detail-row">
                        <div class="detail-label">Approved At</div>
                        <div class="detail-value">{{ $club->approved_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Approved By</div>
                        <div class="detail-value">{{ $club->approver->name ?? '–' }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Club Account & Images -->
        <div class="col-lg-4">
            <!-- Club Account -->
            <div class="admin-detail-card mb-4">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-person-badge me-2"></i>Club Account
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    @if($club->clubUser)
                    <div class="detail-row">
                        <div class="detail-label">Account Name</div>
                        <div class="detail-value">{{ $club->clubUser->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Account Email</div>
                        <div class="detail-value">{{ $club->clubUser->email }}</div>
                    </div>
                    @else
                    <div class="text-muted text-center py-3">
                        <i class="bi bi-exclamation-circle me-2"></i>No club account assigned
                    </div>
                    @endif
                </div>
            </div>

            <!-- Club Images -->
            <div class="admin-detail-card">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-image me-2"></i>Images
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <div class="mb-3">
                        <div class="detail-label mb-2">Logo</div>
                        @if($club->logo)
                        <img src="/storage/{{ $club->logo }}" alt="Club Logo" class="club-detail-image" onerror="this.src='{{ asset('images/default-club-avatar.png') }}'">
                        @else
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-image me-2"></i>No logo uploaded
                        </div>
                        @endif
                    </div>
                    <div>
                        <div class="detail-label mb-2">Background Image</div>
                        @if($club->background_image)
                        <img src="/storage/{{ $club->background_image }}" alt="Background" class="club-detail-image" onerror="this.style.display='none'">
                        @else
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-image me-2"></i>No background image
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-club-detail-page {
        max-width: 1400px;
        margin: 0 auto;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.9375rem;
    }

    .btn-back:hover {
        color: var(--primary);
        background: var(--bg-secondary);
    }

    .admin-club-header-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 2rem;
    }

    .club-header-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .club-header-logo {
        flex-shrink: 0;
    }

    .club-header-logo-img {
        width: 80px;
        height: 80px;
        border-radius: 1rem;
        object-fit: cover;
        border: 3px solid var(--border-color);
    }

    .club-header-info {
        flex: 1;
    }

    .club-header-name {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .club-header-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .club-header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-toggle-status-detail {
        padding: 0.5rem 1rem;
        border: 1px solid;
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-toggle-status-detail.btn-activate {
        background: var(--success-light);
        color: var(--success);
        border-color: var(--success);
    }

    .btn-toggle-status-detail.btn-activate:hover {
        background: var(--success);
        color: white;
    }

    .btn-toggle-status-detail.btn-deactivate {
        background: var(--error-light);
        color: var(--error);
        border-color: var(--error);
    }

    .btn-toggle-status-detail.btn-deactivate:hover {
        background: var(--error);
        color: white;
    }

    .btn-view-logs {
        padding: 0.5rem 1rem;
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        text-decoration: none;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-view-logs:hover {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
    }

    .admin-stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 1rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .admin-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
    }

    .admin-stat-content {
        flex: 1;
    }

    .admin-stat-label {
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        margin-bottom: 0.25rem;
    }

    .admin-stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .admin-detail-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .admin-detail-card-header {
        padding: 1.5rem 1.75rem;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-secondary);
    }

    .admin-detail-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
    }

    .admin-detail-card-body {
        padding: 1.5rem 1.75rem;
    }

    .detail-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        width: 150px;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-tertiary);
        flex-shrink: 0;
    }

    .detail-value {
        flex: 1;
        font-size: 0.9375rem;
        color: var(--text-primary);
    }

    .club-detail-image {
        width: 100%;
        max-width: 300px;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--primary-light);
        color: var(--primary);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .status-badge.active {
        background: var(--success-light);
        color: var(--success);
    }

    .status-badge.inactive {
        background: var(--warning-light);
        color: var(--warning);
    }

    .status-badge.pending {
        background: var(--info-light);
        color: var(--info);
    }

    .status-badge.rejected {
        background: var(--error-light);
        color: var(--error);
    }
</style>
@endpush

@endsection

