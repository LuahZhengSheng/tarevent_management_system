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
                        <div class="detail-value">{{ $user->program ?? '–' }}</div>
                    </div>
                    @if($user->role === 'club' && $user->club)
                    <div class="detail-row">
                        <div class="detail-label">Club</div>
                        <div class="detail-value">{{ $user->club->name }}</div>
                    </div>
                    @endif
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
            type: 'POST',
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

})(jQuery);
</script>
@endpush

@endsection

