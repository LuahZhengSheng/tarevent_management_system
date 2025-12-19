<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'Edit Permissions')

@section('content')
<div class="admin-permission-edit-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.permissions.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Permissions
        </a>
    </div>

    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="admin-page-title">Edit Permissions</h1>
            <p class="admin-page-subtitle">Manage permissions for {{ $admin->name }}</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="admin-edit-card">
        <form method="POST" action="{{ route('admin.permissions.update', $admin) }}">
            @csrf
            @method('PATCH')

            <!-- Administrator Info -->
            <div class="form-section mb-4">
                <h3 class="form-section-title">Administrator Information</h3>
                <div class="admin-info-card">
                    <div class="admin-info-avatar">
                        <img 
                            src="{{ $admin->profile_photo_url }}" 
                            alt="{{ $admin->name }}"
                            class="admin-info-avatar-img"
                            onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                        >
                    </div>
                    <div class="admin-info-details">
                        <div class="admin-info-name">{{ $admin->name }}</div>
                        <div class="admin-info-email">{{ $admin->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="form-section">
                <h3 class="form-section-title">Permissions</h3>
                <p class="form-section-hint">
                    Select the permissions to grant to this administrator. If no permissions are selected, the administrator will only be able to view and edit their own profile.
                </p>

                <div class="permissions-grid">
                    @foreach($permissions as $key => $label)
                    <div class="permission-item">
                        <label class="permission-checkbox">
                            <input 
                                type="checkbox" 
                                name="permissions[]" 
                                value="{{ $key }}"
                                {{ in_array($key, $admin->permissions ?? []) ? 'checked' : '' }}
                            >
                            <span class="permission-label">{{ $label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.permissions.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Permissions</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .admin-permission-edit-page {
        max-width: 1200px;
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
        font-weight: 500;
    }

    .btn-back:hover {
        color: var(--primary);
        background: var(--bg-secondary);
    }

    .admin-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .admin-page-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        margin: 0.5rem 0 0 0;
    }

    .admin-edit-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-section-hint {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .admin-info-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }

    .admin-info-avatar {
        flex-shrink: 0;
    }

    .admin-info-avatar-img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }

    .admin-info-details {
        flex: 1;
    }

    .admin-info-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .admin-info-email {
        font-size: 0.9375rem;
        color: var(--text-secondary);
    }

    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }

    .permission-item {
        padding: 0;
    }

    .permission-checkbox {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bg-primary);
    }

    .permission-checkbox:hover {
        border-color: var(--primary);
        background: var(--bg-secondary);
    }

    .permission-checkbox input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .permission-checkbox input[type="checkbox"]:checked + .permission-label {
        color: var(--primary);
        font-weight: 500;
    }

    .permission-label {
        font-size: 0.9375rem;
        color: var(--text-primary);
        cursor: pointer;
        user-select: none;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .btn-cancel {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-cancel:hover {
        background: var(--bg-secondary);
        border-color: var(--border-hover);
    }

    .btn-save {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: var(--primary);
        color: white;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-save:hover {
        background: var(--primary-hover);
    }
</style>
@endpush

@endsection

