@extends('layouts.club')

@section('title', 'Announcements - TAREvent')

@section('content')
<div class="club-announcements-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Announcements</span>
                    </div>
                    <h1 class="page-title">Announcements</h1>
                    <p class="page-description">Manage and publish announcements for your club members</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('club.announcements.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Announcement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="announcementsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="published-tab" data-bs-toggle="tab" data-bs-target="#published-announcements" type="button" role="tab">
                    <i class="bi bi-megaphone me-2"></i>Published
                    <span class="badge bg-success ms-2">{{ $publishedAnnouncements->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-announcements" type="button" role="tab">
                    <i class="bi bi-file-earmark me-2"></i>Drafts
                    <span class="badge bg-secondary ms-2">{{ $draftAnnouncements->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="announcementsTabContent">
            <!-- Published Announcements Tab -->
            <div class="tab-pane fade show active" id="published-announcements" role="tabpanel">
                <div class="announcements-card">
                    @if($publishedAnnouncements->count() > 0)
                    <div class="announcements-grid">
                        @foreach($publishedAnnouncements as $announcement)
                        <div class="announcement-card">
                            @if($announcement->image && $announcement->image_url)
                            <div class="announcement-image">
                                <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}">
                            </div>
                            @endif
                            <div class="announcement-content">
                                <div class="announcement-header">
                                    <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                    <span class="badge bg-success">Published</span>
                                </div>
                                <div class="announcement-body">
                                    <p class="announcement-text">{{ Str::limit($announcement->content, 150) }}</p>
                                </div>
                                <div class="announcement-footer">
                                    <div class="announcement-meta">
                                        <span class="announcement-date">
                                            <i class="bi bi-calendar me-1"></i>
                                            Published {{ $announcement->published_at->format('M d, Y') }}
                                        </span>
                                        <span class="announcement-author">
                                            <i class="bi bi-person me-1"></i>
                                            {{ $announcement->creator->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <div class="announcement-actions">
                                        <a href="{{ route('club.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <button class="btn btn-sm btn-outline-warning btn-unpublish-announcement" 
                                                data-announcement-id="{{ $announcement->id }}">
                                            <i class="bi bi-eye-slash me-1"></i>Unpublish
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-announcement" 
                                                data-announcement-id="{{ $announcement->id }}"
                                                data-announcement-title="{{ $announcement->title }}">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="bi bi-megaphone empty-icon"></i>
                        <p class="empty-text">No published announcements yet.</p>
                        <a href="{{ route('club.announcements.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-2"></i>Create Your First Announcement
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Draft Announcements Tab -->
            <div class="tab-pane fade" id="draft-announcements" role="tabpanel">
                <div class="announcements-card">
                    @if($draftAnnouncements->count() > 0)
                    <div class="announcements-grid">
                        @foreach($draftAnnouncements as $announcement)
                        <div class="announcement-card">
                            @if($announcement->image && $announcement->image_url)
                            <div class="announcement-image">
                                <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}">
                            </div>
                            @endif
                            <div class="announcement-content">
                                <div class="announcement-header">
                                    <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                    <span class="badge bg-secondary">Draft</span>
                                </div>
                                <div class="announcement-body">
                                    <p class="announcement-text">{{ Str::limit($announcement->content, 150) }}</p>
                                </div>
                                <div class="announcement-footer">
                                    <div class="announcement-meta">
                                        <span class="announcement-date">
                                            <i class="bi bi-calendar me-1"></i>
                                            Created {{ $announcement->created_at->format('M d, Y') }}
                                        </span>
                                        <span class="announcement-author">
                                            <i class="bi bi-person me-1"></i>
                                            {{ $announcement->creator->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <div class="announcement-actions">
                                        <a href="{{ route('club.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <button class="btn btn-sm btn-outline-success btn-publish-announcement" 
                                                data-announcement-id="{{ $announcement->id }}">
                                            <i class="bi bi-eye me-1"></i>Publish
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-announcement" 
                                                data-announcement-id="{{ $announcement->id }}"
                                                data-announcement-title="{{ $announcement->title }}">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark empty-icon"></i>
                        <p class="empty-text">No draft announcements.</p>
                        <a href="{{ route('club.announcements.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-2"></i>Create Announcement
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.club-announcements-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.announcements-card {
    background: var(--bg-primary);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-sm);
    padding: 1.5rem;
}

.announcements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.announcement-card {
    background: var(--bg-secondary);
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.announcement-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.announcement-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: var(--bg-tertiary);
}

.announcement-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.announcement-content {
    padding: 1.25rem;
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.announcement-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    flex: 1;
}

.announcement-body {
    margin-bottom: 1rem;
}

.announcement-text {
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.6;
    margin: 0;
}

.announcement-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.announcement-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: var(--text-tertiary);
}

.announcement-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-text {
    margin: 0;
    font-size: 1rem;
}

.nav-tabs {
    border-bottom: 2px solid var(--border-color);
}

.nav-tabs .nav-link {
    border: none;
    color: var(--text-secondary);
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: var(--primary);
}

.nav-tabs .nav-link.active {
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
    background: transparent;
}

.header-right {
    display: flex;
    align-items: center;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const apiToken = localStorage.getItem('api_token') || '';
    const clubId = {{ $club->id }};

    // Generate timestamp for IFA standard
    function generateTimestamp() {
        const now = new Date();
        return now.getFullYear() + '-' + 
            String(now.getMonth() + 1).padStart(2, '0') + '-' + 
            String(now.getDate()).padStart(2, '0') + ' ' + 
            String(now.getHours()).padStart(2, '0') + ':' + 
            String(now.getMinutes()).padStart(2, '0') + ':' + 
            String(now.getSeconds()).padStart(2, '0');
    }

    // Publish announcement
    $('.btn-publish-announcement').on('click', function() {
        const announcementId = $(this).data('announcement-id');
        
        if (!confirm('Are you sure you want to publish this announcement?')) {
            return;
        }

        $.ajax({
            url: `/api/clubs/${clubId}/announcements/${announcementId}/publish`,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': `Bearer ${apiToken}`
            },
            data: JSON.stringify({
                timestamp: generateTimestamp()
            }),
            success: function(response) {
                if (response.status === 'S' || response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to publish announcement.');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to publish announcement.';
                alert(error);
            }
        });
    });

    // Unpublish announcement
    $('.btn-unpublish-announcement').on('click', function() {
        const announcementId = $(this).data('announcement-id');
        
        if (!confirm('Are you sure you want to unpublish this announcement?')) {
            return;
        }

        $.ajax({
            url: `/api/clubs/${clubId}/announcements/${announcementId}/unpublish`,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': `Bearer ${apiToken}`
            },
            data: JSON.stringify({
                timestamp: generateTimestamp()
            }),
            success: function(response) {
                if (response.status === 'S' || response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to unpublish announcement.');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to unpublish announcement.';
                alert(error);
            }
        });
    });

    // Delete announcement
    $('.btn-delete-announcement').on('click', function() {
        const announcementId = $(this).data('announcement-id');
        const announcementTitle = $(this).data('announcement-title');
        
        if (!confirm(`Are you sure you want to delete "${announcementTitle}"? This action cannot be undone.`)) {
            return;
        }

        $.ajax({
            url: `/api/clubs/${clubId}/announcements/${announcementId}`,
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': `Bearer ${apiToken}`
            },
            data: JSON.stringify({
                timestamp: generateTimestamp()
            }),
            success: function(response) {
                if (response.status === 'S' || response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to delete announcement.');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to delete announcement.';
                alert(error);
            }
        });
    });
});
</script>
@endpush
@endsection

