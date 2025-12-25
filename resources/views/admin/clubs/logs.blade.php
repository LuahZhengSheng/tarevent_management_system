<!-- Author: Auto-generated -->
@extends('layouts.admin')

@section('title', 'Club Logs')

@section('content')
<div class="admin-club-logs-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.clubs.show', $club) }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Club Details
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
                <p class="club-header-subtitle">Activity Log</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filter-card mb-4">
        <div class="row g-3">
            <!-- Action Filter -->
            <div class="col-md-3">
                <select id="actionFilter" class="admin-filter-select">
                    <option value="">All Actions</option>
                    <option value="create_club" {{ request('action') === 'create_club' ? 'selected' : '' }}>Create Club</option>
                    <option value="approve_club" {{ request('action') === 'approve_club' ? 'selected' : '' }}>Approve Club</option>
                    <option value="activate_club" {{ request('action') === 'activate_club' ? 'selected' : '' }}>Activate Club</option>
                    <option value="deactivate_club" {{ request('action') === 'deactivate_club' ? 'selected' : '' }}>Deactivate Club</option>
                    <option value="add_member" {{ request('action') === 'add_member' ? 'selected' : '' }}>Add Member</option>
                    <option value="remove_member" {{ request('action') === 'remove_member' ? 'selected' : '' }}>Remove Member</option>
                    <option value="approve_join" {{ request('action') === 'approve_join' ? 'selected' : '' }}>Approve Join</option>
                    <option value="reject_join" {{ request('action') === 'reject_join' ? 'selected' : '' }}>Reject Join</option>
                    <option value="create_announcement" {{ request('action') === 'create_announcement' ? 'selected' : '' }}>Create Announcement</option>
                    <option value="publish_announcement" {{ request('action') === 'publish_announcement' ? 'selected' : '' }}>Publish Announcement</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-3">
                <input 
                    type="date" 
                    id="dateFrom" 
                    class="admin-filter-select"
                    value="{{ request('date_from') }}"
                    placeholder="Date From"
                >
            </div>

            <!-- Date To -->
            <div class="col-md-3">
                <input 
                    type="date" 
                    id="dateTo" 
                    class="admin-filter-select"
                    value="{{ request('date_to') }}"
                    placeholder="Date To"
                >
            </div>

            <!-- Actor Filter -->
            <div class="col-md-3">
                <select id="actorFilter" class="admin-filter-select">
                    <option value="">All Actors</option>
                    <!-- Populate with actors from logs -->
                </select>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="admin-table-card">
        <div id="logsTableContainer">
            @include('admin.clubs.partials.log-table', ['logs' => $logs])
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer">
        @include('admin.clubs.partials.log-pagination', ['logs' => $logs])
    </div>
</div>

@push('styles')
<style>
    .admin-club-logs-page {
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
        padding: 1.5rem 2rem;
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
        width: 60px;
        height: 60px;
        border-radius: 0.75rem;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }

    .club-header-info {
        flex: 1;
    }

    .club-header-name {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .club-header-subtitle {
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

    .admin-filter-select {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: all 0.2s ease;
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

    .action-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
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
</style>
@endpush

@push('scripts')
<script>
(function($) {
    'use strict';

    function fetchLogs() {
        const params = {
            action: $('#actionFilter').val(),
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            actor_id: $('#actorFilter').val(),
            page: $('.pagination .page-item.active .page-link').data('page') || 1
        };

        $.ajax({
            url: '{{ route("admin.clubs.logs", $club) }}',
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#logsTableContainer').html(response.html);
                $('#paginationContainer').html(response.pagination);
            },
            error: function(xhr) {
                console.error('Error fetching logs:', xhr);
            }
        });
    }

    // Filter change
    $('#actionFilter, #dateFrom, #dateTo, #actorFilter').on('change', function() {
        fetchLogs();
    });

    // Pagination click
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            $('.pagination .page-item').removeClass('active');
            $(this).closest('.page-item').addClass('active');
            fetchLogs();
        }
    });
})(jQuery);
</script>
@endpush

@endsection

