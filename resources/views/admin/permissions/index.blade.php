@extends('layouts.admin')

@section('title', 'Permission Management')

@section('content')
<div class="admin-permissions-page">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="admin-page-title">Permission Management</h1>
            <p class="admin-page-subtitle">Manage administrator permissions</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Administrators Table -->
    <div class="admin-table-card">
        @if($admins->count() > 0)
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Administrator</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="user-avatar-cell">
                            <img 
                                src="{{ $admin->profile_photo_url }}" 
                                alt="{{ $admin->name }}"
                                class="user-avatar"
                                onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                            >
                            <div class="user-info">
                                <div class="user-name">{{ $admin->name }}</div>
                                <div class="user-email">{{ $admin->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
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
                    </td>
                    <td>
                        @if($admin->isSuperAdmin())
                        <span class="badge bg-warning text-dark">All Permissions</span>
                        @elseif($admin->permissions === null)
                        <span class="badge bg-secondary">No Permissions (Profile Only)</span>
                        @else
                        <div class="permissions-list">
                            @foreach($admin->permissions as $permission)
                            <span class="permission-badge">{{ $permissions[$permission] ?? $permission }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons justify-content-end">
                            @if(!$admin->isSuperAdmin())
                            <a 
                                href="{{ route('admin.permissions.edit', $admin) }}" 
                                class="btn-action"
                                title="Edit Permissions"
                            >
                                <i class="bi bi-pencil"></i>
                            </a>
                            @else
                            <span class="text-muted" style="font-size: 0.875rem;">N/A</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <div class="empty-state-title">No Administrators Found</div>
            <div class="empty-state-text">
                No administrators are available for permission management.
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .admin-permissions-page {
        max-width: 1400px;
        margin: 0 auto;
    }

    .admin-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .admin-page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        letter-spacing: -0.02em;
    }

    .admin-page-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .admin-table-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .admin-table {
        width: 100%;
        margin: 0;
    }

    .admin-table thead {
        background: var(--bg-secondary);
    }

    .admin-table thead th {
        padding: 1rem 1.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-tertiary);
        font-weight: 600;
        border-bottom: 1px solid var(--border-color);
    }

    .admin-table tbody td {
        padding: 1.25rem 1.5rem;
        font-size: 0.9375rem;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    .admin-table tbody tr:hover {
        background: var(--bg-secondary);
    }

    .user-avatar-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.125rem;
    }

    .user-email {
        font-size: 0.8125rem;
        color: var(--text-secondary);
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

    .role-badge.super-admin {
        background: var(--warning-light);
        color: var(--warning);
    }

    .role-badge.admin {
        background: var(--info-light);
        color: var(--info);
    }

    .permissions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .permission-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem;
        border: none;
        background: none;
        color: var(--text-secondary);
        cursor: pointer;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .btn-action:hover {
        background: var(--bg-secondary);
        color: var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }

    .empty-state-icon {
        font-size: 3rem;
        color: var(--text-tertiary);
        margin-bottom: 1rem;
    }

    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        font-size: 0.9375rem;
        color: var(--text-secondary);
    }
</style>
@endpush

@endsection

