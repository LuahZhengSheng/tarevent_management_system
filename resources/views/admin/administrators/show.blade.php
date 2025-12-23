<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'Administrator Details')

@section('content')
<div class="admin-admin-detail-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.administrators.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Administrators
        </a>
    </div>

    <!-- Administrator Header -->
    <div class="admin-user-header-card mb-4">
        <div class="user-header-content">
            <div class="user-header-avatar">
                <img 
                    src="{{ $admin->profile_photo_url }}" 
                    alt="{{ $admin->name }}"
                    class="user-header-avatar-img"
                    onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                >
            </div>
            <div class="user-header-info">
                <h1 class="user-header-name">{{ $admin->name }}</h1>
                <div class="user-header-meta">
                    @if($admin->isSuperAdmin())
                    <span class="role-badge super-admin">
                        <i class="bi bi-shield-check"></i>
                        Super Administrator
                    </span>
                    @else
                    <span class="role-badge admin">
                        <i class="bi bi-shield"></i>
                        Administrator
                    </span>
                    @endif
                    <span class="status-badge {{ $admin->status }}">
                        <i class="bi bi-{{ $admin->status === 'active' ? 'check-circle' : ($admin->status === 'suspended' ? 'x-circle' : 'pause-circle') }}"></i>
                        {{ ucfirst($admin->status) }}
                    </span>
                </div>
            </div>
            <div class="user-header-actions">
                <a href="{{ route('admin.administrators.edit', $admin) }}" class="btn-edit">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
                <button 
                    type="button"
                    class="btn-toggle-status-detail {{ $admin->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}"
                    data-admin-id="{{ $admin->id }}"
                    data-status="{{ $admin->status }}"
                >
                    <i class="bi bi-{{ $admin->status === 'active' ? 'pause' : 'play' }}-circle me-2"></i>
                    {{ $admin->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Administrator Details -->
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
                        <div class="detail-value">{{ $admin->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">
                            {{ $admin->email }}
                            @if($admin->email_verified_at)
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
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $admin->phone ?? '–' }}</div>
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
                        <div class="detail-value">{{ $admin->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Last Login</div>
                        <div class="detail-value">
                            {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never' }}
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email Verified</div>
                        <div class="detail-value">
                            {{ $admin->email_verified_at ? $admin->email_verified_at->format('M d, Y H:i') : 'Not verified' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="col-lg-4">
            <div class="admin-detail-card">
                <div class="admin-detail-card-header">
                    <h2 class="admin-detail-card-title">
                        <i class="bi bi-shield-lock me-2"></i>Permissions
                    </h2>
                </div>
                <div class="admin-detail-card-body">
                    @if($admin->isSuperAdmin())
                    <div class="permission-info">
                        <div class="permission-badge-full">
                            <i class="bi bi-shield-check me-2"></i>
                            <span>Super Administrator</span>
                        </div>
                        <p class="permission-description">
                            Has full access to all system features and can manage all permissions.
                        </p>
                    </div>
                    @elseif($admin->permissions === null)
                    <div class="permission-info">
                        <div class="permission-badge-full profile-only">
                            <i class="bi bi-person me-2"></i>
                            <span>Profile Only</span>
                        </div>
                        <p class="permission-description">
                            This administrator can only view and edit their own profile. No additional permissions have been granted.
                        </p>
                    </div>
                    @else
                    <div class="permission-info">
                        <div class="permissions-list-full">
                            @php
                                $permissionLabels = [
                                    'manage_students' => 'Manage Students',
                                    'manage_administrators' => 'Manage Administrators',
                                    'manage_events' => 'Manage Events',
                                    'manage_clubs' => 'Manage Clubs',
                                    'view_reports' => 'View Reports',
                                    'manage_settings' => 'Manage System Settings',
                                ];
                            @endphp
                            @foreach($admin->permissions as $permission)
                            <div class="permission-item-full">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>{{ $permissionLabels[$permission] ?? $permission }}</span>
                            </div>
                            @endforeach
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                        <div class="permission-actions mt-3">
                            <a href="{{ route('admin.permissions.edit', $admin) }}" class="btn-edit-permissions">
                                <i class="bi bi-pencil me-2"></i>Edit Permissions
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-admin-detail-page {
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

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .role-badge.admin {
        background: var(--info-light);
        color: var(--info);
    }

    .role-badge.super-admin {
        background: var(--warning-light);
        color: var(--warning);
    }

    .permission-info {
        padding: 0.5rem 0;
    }

    .permission-badge-full {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 0.75rem;
        font-weight: 500;
        margin-bottom: 1rem;
        width: 100%;
        justify-content: center;
    }

    .permission-badge-full.profile-only {
        background: var(--bg-secondary);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .permission-description {
        font-size: 0.875rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    .permissions-list-full {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .permission-item-full {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        color: var(--text-primary);
    }

    .permission-actions {
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .btn-edit-permissions {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1rem;
        background: var(--primary);
        color: white;
        border-radius: 0.5rem;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        width: 100%;
        justify-content: center;
    }

    .btn-edit-permissions:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
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
        const adminId = $btn.data('admin-id');
        const currentStatus = $btn.data('status');

        $.ajax({
            url: `/admin/administrators/${adminId}/toggle-status`,
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
                alert('Failed to update administrator status. Please try again.');
            }
        });
    });

})(jQuery);
</script>
@endpush

@endsection

