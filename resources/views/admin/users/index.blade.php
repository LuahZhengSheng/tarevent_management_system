<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="admin-users-page">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="admin-page-title">User Management</h1>
            <p class="admin-page-subtitle">Manage students and club organizers</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn-add-user">
                <i class="bi bi-person-plus me-2"></i>Add User
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="admin-filter-card mb-4">
        <div class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <div class="admin-search-wrapper">
                    <i class="bi bi-search admin-search-icon"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="admin-search-input" 
                        placeholder="Search by name, email, student ID..."
                        value="{{ request('search') }}"
                    >
                    @if(request('search'))
                    <button type="button" class="admin-search-clear" id="clearSearch">
                        <i class="bi bi-x"></i>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Role Filter -->
            <div class="col-md-2">
                <select id="roleFilter" class="admin-filter-select">
                    <option value="">All Roles</option>
                    <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Students</option>
                    <option value="club" {{ request('role') === 'club' ? 'selected' : '' }}>Club Organizers</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <select id="statusFilter" class="admin-filter-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-2">
                <select id="sortBy" class="admin-filter-select">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Newest First</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="last_login_at" {{ request('sort_by') === 'last_login_at' ? 'selected' : '' }}>Last Login</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div class="col-md-2">
                <select id="sortOrder" class="admin-filter-select">
                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="admin-table-card">
        <div id="usersTableContainer">
            @include('admin.users.partials.user-table', ['users' => $users])
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer">
        @include('admin.users.partials.pagination', ['users' => $users])
    </div>
</div>

@push('styles')
<style>
    .admin-users-page {
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

    .admin-filter-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .admin-search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .admin-search-icon {
        position: absolute;
        left: 1rem;
        color: var(--text-tertiary);
        font-size: 1rem;
        pointer-events: none;
    }

    .admin-search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        font-size: 0.9375rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .admin-search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .admin-search-clear {
        position: absolute;
        right: 0.75rem;
        background: none;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .admin-search-clear:hover {
        color: var(--text-primary);
    }

    .admin-filter-select {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
    }

    .admin-filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
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

    .role-badge.student {
        background: var(--info-light);
        color: var(--info);
    }

    .role-badge.club {
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

    .status-badge.suspended {
        background: var(--error-light);
        color: var(--error);
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
    }

    .btn-action:hover {
        background: var(--bg-secondary);
        color: var(--primary);
    }

    .btn-toggle-status {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--text-primary);
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-toggle-status.btn-deactivate {
        background: var(--error-light);
        color: var(--error);
        border-color: var(--error);
    }

    .btn-toggle-status.btn-deactivate:hover {
        background: var(--error);
        color: white;
        border-color: var(--error);
    }

    .btn-toggle-status.btn-activate:hover {
        border-color: var(--primary);
        background: var(--primary-light);
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

    .btn-add-user {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border-radius: 0.75rem;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        font-size: 0.9375rem;
    }

    .btn-add-user:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
</style>
@endpush

@push('scripts')
<script>
(function($) {
    'use strict';

    let searchTimeout = null;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function fetchUsers() {
        const params = {
            search: $('#searchInput').val(),
            role: $('#roleFilter').val(),
            status: $('#statusFilter').val(),
            sort_by: $('#sortBy').val(),
            sort_order: $('#sortOrder').val(),
            page: $('.pagination .page-item.active .page-link').data('page') || 1
        };

        $.ajax({
            url: '{{ route("admin.users.index") }}',
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#usersTableContainer').html(response.html);
                $('#paginationContainer').html(response.pagination);
            },
            error: function(xhr) {
                console.error('Error fetching users:', xhr);
            }
        });
    }

    // Search with debounce
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetchUsers();
        }, 500);
    });

    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        fetchUsers();
    });

    // Filters
    $('#roleFilter, #statusFilter, #sortBy, #sortOrder').on('change', function() {
        fetchUsers();
    });

    // Pagination
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            fetchUsers();
        }
    });

    // Toggle status
    $(document).on('click', '.btn-toggle-status', function(e) {
        e.preventDefault();
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
                    $btn.data('status', response.status);
                    $btn.text(response.status === 'active' ? 'Deactivate' : 'Activate');
                    // Update button classes
                    $btn.removeClass('btn-deactivate btn-activate');
                    if (response.status === 'active') {
                        $btn.addClass('btn-deactivate');
                    } else {
                        $btn.addClass('btn-activate');
                    }
                    // Update status badge
                    const $row = $btn.closest('tr');
                    const $badge = $row.find('.status-badge');
                    $badge.removeClass('active inactive suspended').addClass(response.status);
                    $badge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
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

