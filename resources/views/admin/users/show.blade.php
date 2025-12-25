<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="admin-user-detail-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>
    </div>


    <!-- User Header -->
    <div class="admin-user-header-card mb-4">
        <div class="user-header-content">
            <div class="user-header-avatar">
                <img 
                    src="{{ $user->profile_photo_url }}" 
                    alt="{{ $user->name }}"
                    class="user-header-avatar-img"
                    onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                >
            </div>
            <div class="user-header-info">
                <h1 class="user-header-name">{{ $user->name }}</h1>
                <div class="user-header-meta">
                    <span class="role-badge {{ $user->role === 'club' ? 'club' : 'student' }}">
                        <i class="bi bi-{{ $user->role === 'club' ? 'building' : 'person' }}"></i>
                        {{ $user->role === 'club' ? 'Club Organizer' : 'Student' }}
                    </span>
                    <span class="status-badge {{ $user->status }}">
                        <i class="bi bi-{{ $user->status === 'active' ? 'check-circle' : ($user->status === 'suspended' ? 'x-circle' : 'pause-circle') }}"></i>
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>
            <div class="user-header-actions">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
                <button 
                    type="button"
                    class="btn-toggle-status-detail {{ $user->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}"
                    data-user-id="{{ $user->id }}"
                    data-status="{{ $user->status }}"
                >
                    <i class="bi bi-{{ $user->status === 'active' ? 'pause' : 'play' }}-circle me-2"></i>
                    {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- User Details -->
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="admin-detail-card">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-person me-2"></i>Basic Information
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <div class="detail-row">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $user->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">
                            {{ $user->email }}
                            @if($user->email_verified_at)
                            <span class="badge bg-success-subtle text-success ms-2">
                                <i class="bi bi-check-circle"></i> Verified
                            </span>
                            @else
                            <span class="badge bg-warning-subtle text-warning ms-2">
                                <i class="bi bi-exclamation-circle"></i> Unverified
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Student ID</div>
                        <div class="detail-value">{{ $user->student_id ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $user->phone ?? '–' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Program</div>
                        <div class="detail-value">
                            @if($user->program && isset($programOptions[$user->program]))
                                {{ $programOptions[$user->program] }}
                            @elseif($user->program)
                                {{ $user->program }}
                            @else
                                –
                            @endif
                        </div>
                    </div>
                    @if($user->role === 'club' && $user->club)
                    <div class="detail-row">
                        <div class="detail-label">Club</div>
                        <div class="detail-value">{{ $user->club->name }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Club Information -->
            <div class="admin-detail-card mt-4">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-building me-2"></i>Club Information
                        <span id="clubCountBadge" class="ms-2 badge bg-primary-subtle text-primary" style="display: none;"></span>
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <!-- Loading State -->
                    <div id="clubLoadingState" class="club-loading-state">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Loading clubs...</span>
                    </div>

                    <!-- Error State -->
                    <div id="clubErrorState" class="club-error-state" style="display: none;">
                        <div class="club-error-content">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            <span id="clubErrorMessage">Failed to load club information.</span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="clubEmptyState" class="club-empty-state" style="display: none;">
                        <div class="club-empty-content">
                            <i class="bi bi-building text-muted"></i>
                            <p class="club-empty-text">This user has not joined any clubs.</p>
                        </div>
                    </div>

                    <!-- Clubs List Container -->
                    <div id="clubInformationContainer" class="club-list-container" style="display: none;">
                        <!-- Visible clubs (first 5) -->
                        <div id="clubVisibleList" class="club-visible-list"></div>
                        <!-- Hidden clubs (rest) -->
                        <div id="clubHiddenList" class="club-hidden-list" style="display: none;"></div>
                        <!-- Show More/Less Button -->
                        <div id="clubToggleContainer" class="club-toggle-container" style="display: none;">
                            <button type="button" id="clubToggleBtn" class="btn-show-more-clubs">
                                <span class="btn-text">Show all clubs</span>
                                <i class="bi bi-chevron-down ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Activity -->
            <div class="admin-detail-card mt-4">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-clock-history me-2"></i>Account Activity
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <div class="detail-row">
                        <div class="detail-label">Account Created</div>
                        <div class="detail-value">{{ $user->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Last Login</div>
                        <div class="detail-value">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email Verified</div>
                        <div class="detail-value">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i') : 'Not verified' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-lg-4">
            <div class="admin-detail-card">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-graph-up me-2"></i>Statistics
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    <div class="stat-item">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $user->eventRegistrations->count() }}</div>
                            <div class="stat-label">Event Registrations</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon bg-success">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $user->posts->count() }}</div>
                            <div class="stat-label">Forum Posts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-user-detail-page {
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
    }

    .btn-back:hover {
        color: var(--primary);
        background: var(--bg-secondary);
    }

    .admin-user-header-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .user-header-content {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .user-header-avatar {
        flex-shrink: 0;
    }

    .user-header-avatar-img {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--border-color);
    }

    .user-header-info {
        flex: 1;
    }

    .user-header-name {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .user-header-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-edit, .btn-toggle-status-detail {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    .btn-edit {
        background: var(--primary);
        color: white;
        text-decoration: none;
    }

    .btn-edit:hover {
        background: var(--primary-hover);
        color: white;
    }

    .btn-toggle-status-detail {
        background: var(--bg-primary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-toggle-status-detail.btn-deactivate {
        background: var(--error-light);
        color: var(--error);
        border-color: var(--error);
    }

    .btn-toggle-status-detail.btn-deactivate:hover {
        background: var(--error);
        color: white;
        border-color: var(--error);
    }

    .btn-toggle-status-detail.btn-activate:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary);
    }

    .admin-detail-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .admin-detail-card-header {
        padding: 1.5rem;
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
        padding: 1.5rem;
    }

    .detail-row {
        display: flex;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        width: 180px;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-secondary);
        flex-shrink: 0;
    }

    .detail-value {
        flex: 1;
        font-size: 0.9375rem;
        color: var(--text-primary);
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    /* Club Information Styles */
    .club-loading-state,
    .club-error-state,
    .club-empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        text-align: center;
    }

    .club-loading-state {
        color: var(--text-secondary);
        font-size: 0.9375rem;
    }

    .club-error-content {
        display: flex;
        align-items: center;
        color: var(--error);
        font-size: 0.9375rem;
    }

    .club-empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .club-empty-content i {
        font-size: 3rem;
        opacity: 0.5;
    }

    .club-empty-text {
        color: var(--text-secondary);
        font-size: 0.9375rem;
        margin: 0;
    }

    .club-list-container {
        display: flex;
        flex-direction: column;
    }

    .club-visible-list,
    .club-hidden-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .club-item-compact {
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--bg-secondary);
        transition: all 0.2s ease;
    }

    .club-item-compact:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
        transform: translateY(-1px);
    }

    .club-item-compact-main {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .club-logo-compact,
    .club-logo-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 1px solid var(--border-color);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-primary);
    }

    .club-logo-placeholder {
        color: var(--text-tertiary);
        font-size: 1.5rem;
    }

    .club-item-compact-info {
        flex: 1;
        min-width: 0;
    }

    .club-item-compact-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
    }

    .club-name-compact {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        flex: 1;
        min-width: 0;
    }

    .club-badges-compact {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .club-badges-compact .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .club-info-items {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 0.5rem;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }

    .club-info-items i {
        margin-right: 0.25rem;
        opacity: 0.7;
    }

    .club-joined-date {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .club-joined-date i {
        opacity: 0.7;
    }

    .club-toggle-container {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        text-align: center;
    }

    .btn-show-more-clubs {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-show-more-clubs:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-show-more-clubs i {
        transition: transform 0.2s ease;
    }

    #clubCountBadge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
(function($) {
    'use strict';

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('.btn-toggle-status-detail').on('click', function() {
        const $btn = $(this);
        const userId = $btn.data('user-id');
        const currentStatus = $btn.data('status');

        $.ajax({
            url: `/admin/users/${userId}/toggle-status`,
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error('Error toggling status:', xhr);
                alert('Failed to update user status. Please try again.');
            }
        });
    });

    // Load club information
    function loadClubInformation() {
        const userId = {{ $user->id }};
        const $loadingState = $('#clubLoadingState');
        const $errorState = $('#clubErrorState');
        const $emptyState = $('#clubEmptyState');
        const $container = $('#clubInformationContainer');
        const $countBadge = $('#clubCountBadge');

        // Show loading state
        $loadingState.show();
        $errorState.hide();
        $emptyState.hide();
        $container.hide();
        $countBadge.hide();

        $.ajax({
            url: `/api/users/${userId}/clubs`,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                $loadingState.hide();

                if (response.success && response.data) {
                    const clubs = response.data.clubs || [];
                    const totalClubs = response.data.total_clubs || 0;

                    if (totalClubs === 0 || clubs.length === 0) {
                        // Show empty state
                        $emptyState.show();
                        $countBadge.hide();
                    } else {
                        // Render clubs list
                        renderClubsList(clubs);
                        $container.show();
                        $countBadge.text(totalClubs).show();
                    }
                } else {
                    // Show error state
                    $('#clubErrorMessage').text(response.message || 'Failed to load club information.');
                    $errorState.show();
                }
            },
            error: function(xhr) {
                $loadingState.hide();
                let errorMessage = 'Failed to load club information.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'Authentication required. Please refresh the page.';
                } else if (xhr.status === 404) {
                    errorMessage = 'User not found.';
                }

                $('#clubErrorMessage').text(errorMessage);
                $errorState.show();
                console.error('Error loading clubs:', xhr);
            }
        });
    }

    // Render clubs list
    function renderClubsList(clubs) {
        const $visibleList = $('#clubVisibleList');
        const $hiddenList = $('#clubHiddenList');
        const $toggleContainer = $('#clubToggleContainer');
        const $toggleBtn = $('#clubToggleBtn');
        
        $visibleList.empty();
        $hiddenList.empty();

        if (!clubs || clubs.length === 0) {
            return;
        }

        const INITIAL_DISPLAY_COUNT = 5;
        const hasMore = clubs.length > INITIAL_DISPLAY_COUNT;

        clubs.forEach(function(club, index) {
            const clubItem = createClubItem(club);
            
            if (index < INITIAL_DISPLAY_COUNT) {
                $visibleList.append(clubItem);
            } else {
                $hiddenList.append(clubItem);
            }
        });

        // Show toggle button if there are more clubs
        if (hasMore) {
            const remainingCount = clubs.length - INITIAL_DISPLAY_COUNT;
            $toggleBtn.find('.btn-text').text(`Show ${remainingCount} more club${remainingCount > 1 ? 's' : ''}`);
            $toggleContainer.show();
        } else {
            $toggleContainer.hide();
        }
    }

    // Create club item HTML (compact version)
    function createClubItem(club) {
        const joinedDate = club.joined_at ? formatDate(club.joined_at) : 'N/A';
        const statusBadgeClass = club.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
        const statusIcon = club.status === 'active' ? 'check-circle' : 'pause-circle';
        const memberRole = club.member_role ? capitalizeFirst(club.member_role) : 'Member';

        let logoHtml = '';
        if (club.logo) {
            const logoUrl = club.logo.startsWith('http') ? club.logo : `/storage/${club.logo}`;
            logoHtml = `<img src="${logoUrl}" alt="${club.name}" class="club-logo-compact" onerror="this.style.display='none'">`;
        } else {
            logoHtml = `<div class="club-logo-placeholder"><i class="bi bi-building"></i></div>`;
        }

        // Compact info display
        let infoItems = [];
        if (club.email) {
            infoItems.push(`<i class="bi bi-envelope"></i> ${escapeHtml(club.email)}`);
        }
        if (club.phone) {
            infoItems.push(`<i class="bi bi-telephone"></i> ${escapeHtml(club.phone)}`);
        }
        if (club.creator) {
            infoItems.push(`<i class="bi bi-person"></i> ${escapeHtml(club.creator.name)}`);
        }

        return $(`
            <div class="club-item-compact">
                <div class="club-item-compact-main">
                    ${logoHtml}
                    <div class="club-item-compact-info">
                        <div class="club-item-compact-header">
                            <h4 class="club-name-compact">${escapeHtml(club.name)}</h4>
                            <div class="club-badges-compact">
                                <span class="badge ${statusBadgeClass}">
                                    <i class="bi bi-${statusIcon}"></i> ${capitalizeFirst(club.status)}
                                </span>
                                <span class="badge bg-info-subtle text-info">
                                    <i class="bi bi-person-badge"></i> ${memberRole}
                                </span>
                            </div>
                        </div>
                        ${infoItems.length > 0 ? `
                            <div class="club-info-items">
                                ${infoItems.join('')}
                            </div>
                        ` : ''}
                        <div class="club-joined-date">
                            <i class="bi bi-calendar3"></i> Joined: ${joinedDate}
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Toggle show more/less clubs
    $('#clubToggleBtn').on('click', function() {
        const $btn = $(this);
        const $hiddenList = $('#clubHiddenList');
        const $icon = $btn.find('i');
        const isExpanded = $hiddenList.is(':visible');

        if (isExpanded) {
            // Collapse
            $hiddenList.slideUp(300);
            $icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
            const totalClubs = $('#clubVisibleList .club-item-compact').length + $('#clubHiddenList .club-item-compact').length;
            const visibleCount = $('#clubVisibleList .club-item-compact').length;
            const remainingCount = totalClubs - visibleCount;
            $btn.find('.btn-text').text(`Show ${remainingCount} more club${remainingCount > 1 ? 's' : ''}`);
        } else {
            // Expand
            $hiddenList.slideDown(300);
            $icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            $btn.find('.btn-text').text('Show less');
        }
    });

    // Helper functions
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        
        return `${month} ${day}, ${year} ${hours}:${minutes}`;
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Load club information on page load
    $(document).ready(function() {
        loadClubInformation();
    });

})(jQuery);
</script>
@endpush

@endsection

