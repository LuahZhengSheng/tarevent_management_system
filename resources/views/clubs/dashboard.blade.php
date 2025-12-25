@extends('layouts.club')

@section('title', 'Club Dashboard - TAREvent')

@section('content')
<div class="club-dashboard-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <span>Club Dashboard</span>
                    </div>
                    <h1 class="page-title">Welcome Back, {{ auth()->user()->name }}</h1>
                    <p class="page-description">Manage your club activities, members, and events from one place</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card" data-stat="members">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="totalMembers">{{ $stats['total_members'] ?? 0 }}</div>
                        <div class="stat-label">Total Members</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="pending">
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="pendingRequests">{{ $stats['pending_requests'] ?? 0 }}</div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="announcements">
                    <div class="stat-icon bg-info">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="totalAnnouncements">{{ $stats['total_announcements'] ?? 0 }}</div>
                        <div class="stat-label">Announcements</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="events">
                    <div class="stat-icon bg-success">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="totalEvents">{{ $stats['total_events'] ?? 0 }}</div>
                        <div class="stat-label">Events</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <h2 class="section-title">
                <i class="bi bi-lightning-charge me-2"></i>
                Quick Actions
            </h2>
            <div class="actions-grid">
                <a href="{{ route('club.members.index') }}" class="action-card">
                    <div class="action-icon bg-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Manage Members</h3>
                        <p class="action-description">View, add, remove members and assign roles</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>

                <a href="{{ route('club.announcements.create') }}" class="action-card">
                    <div class="action-icon bg-info">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Create Announcement</h3>
                        <p class="action-description">Post announcements to your club members</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>

                <a href="{{ route('club.join-requests.index') }}" class="action-card">
                    <div class="action-icon bg-warning">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Review Join Requests</h3>
                        <p class="action-description">Approve or reject membership requests</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>

                <a href="{{ route('club.members.index') }}" class="action-card">
                    <div class="action-icon bg-danger">
                        <i class="bi bi-ban"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Manage Blacklist</h3>
                        <p class="action-description">View and manage blacklisted members</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>

                <a href="{{ route('club.events.index') }}" class="action-card">
                    <div class="action-icon bg-success">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Manage Events</h3>
                        <p class="action-description">Create and manage club events</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon bg-secondary">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Forum Posts</h3>
                        <p class="action-description">Manage forum posts and discussions</p>
                    </div>
                    <i class="bi bi-arrow-right action-arrow"></i>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity-section">
            <h2 class="section-title">
                <i class="bi bi-clock-history me-2"></i>
                Recent Activity
            </h2>
            <div class="activity-list">
                @forelse($recentLogs ?? [] as $log)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-{{ $log->action === 'create' ? 'plus-circle' : ($log->action === 'update' ? 'pencil' : 'info-circle') }}"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text">
                            <strong>{{ $log->actor->name ?? 'System' }}</strong>
                            {{ $log->action }}
                            @if($log->targetUser)
                                <strong>{{ $log->targetUser->name }}</strong>
                            @endif
                        </p>
                        <span class="activity-time">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="bi bi-inbox empty-icon"></i>
                    <p class="empty-text">No recent activity</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

