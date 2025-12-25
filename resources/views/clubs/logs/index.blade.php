@extends('layouts.club')

@section('title', 'Activity Logs - TAREvent')

@section('content')
<div class="club-logs-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Activity Logs</span>
                    </div>
                    <h1 class="page-title">Activity Logs</h1>
                    <p class="page-description">View all activities and changes made to your club</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Filters -->
        <div class="logs-filters-card mb-4">
            <form method="GET" action="{{ route('club.logs.index') }}" id="logsFilterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="action" class="form-label">Action</label>
                        <select class="form-select" id="action" name="action">
                            <option value="">All Actions</option>
                            <option value="create_club" {{ request('action') === 'create_club' ? 'selected' : '' }}>Create Club</option>
                            <option value="update_club_profile" {{ request('action') === 'update_club_profile' ? 'selected' : '' }}>Update Profile</option>
                            <option value="add_member" {{ request('action') === 'add_member' ? 'selected' : '' }}>Add Member</option>
                            <option value="remove_member" {{ request('action') === 'remove_member' ? 'selected' : '' }}>Remove Member</option>
                            <option value="update_member_role" {{ request('action') === 'update_member_role' ? 'selected' : '' }}>Update Member Role</option>
                            <option value="approve_join" {{ request('action') === 'approve_join' ? 'selected' : '' }}>Approve Join</option>
                            <option value="reject_join" {{ request('action') === 'reject_join' ? 'selected' : '' }}>Reject Join</option>
                            <option value="add_to_blacklist" {{ request('action') === 'add_to_blacklist' ? 'selected' : '' }}>Add to Blacklist</option>
                            <option value="remove_from_blacklist" {{ request('action') === 'remove_from_blacklist' ? 'selected' : '' }}>Remove from Blacklist</option>
                            <option value="create_announcement" {{ request('action') === 'create_announcement' ? 'selected' : '' }}>Create Announcement</option>
                            <option value="update_announcement" {{ request('action') === 'update_announcement' ? 'selected' : '' }}>Update Announcement</option>
                            <option value="delete_announcement" {{ request('action') === 'delete_announcement' ? 'selected' : '' }}>Delete Announcement</option>
                            <option value="publish_announcement" {{ request('action') === 'publish_announcement' ? 'selected' : '' }}>Publish Announcement</option>
                            <option value="unpublish_announcement" {{ request('action') === 'unpublish_announcement' ? 'selected' : '' }}>Unpublish Announcement</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-funnel me-2"></i>Filter
                            </button>
                            <a href="{{ route('club.logs.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="logs-card">
            <div class="card-header-custom">
                <h3 class="card-title">Activity Logs</h3>
                <div class="card-meta">
                    <span class="text-muted">Total: {{ $logs->total() }} entries</span>
                </div>
            </div>
            <div class="card-body-custom">
                @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table logs-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Actor</th>
                                <th>Target</th>
                                <th>Request ID</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>
                                    <div class="log-timestamp">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </div>
                                    <div class="log-relative-time text-muted">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info action-badge">
                                        {{ str_replace('_', ' ', ucwords($log->action, '_')) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->actor)
                                    <div class="log-user">
                                        <img src="{{ $log->actor->profile_photo_url }}" 
                                             alt="{{ $log->actor->name }}" 
                                             class="log-user-avatar"
                                             onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                        <span>{{ $log->actor->name }}</span>
                                    </div>
                                    @else
                                    <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->targetUser)
                                    <div class="log-user">
                                        <img src="{{ $log->targetUser->profile_photo_url }}" 
                                             alt="{{ $log->targetUser->name }}" 
                                             class="log-user-avatar"
                                             onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                        <span>{{ $log->targetUser->name }}</span>
                                    </div>
                                    @else
                                    <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->request_id)
                                    <code class="request-id">{{ Str::limit($log->request_id, 20) }}</code>
                                    @else
                                    <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->metadata && count($log->metadata) > 0)
                                    <button class="btn btn-sm btn-outline-info" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#metadata-{{ $log->id }}">
                                        <i class="bi bi-info-circle me-1"></i>View
                                    </button>
                                    <div class="collapse mt-2" id="metadata-{{ $log->id }}">
                                        <div class="metadata-content">
                                            <pre>{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-muted">–</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper mt-4">
                    {{ $logs->links() }}
                </div>
                @else
                <div class="empty-state">
                    <i class="bi bi-clock-history empty-icon"></i>
                    <p class="empty-text">No activity logs found.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.club-logs-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.logs-filters-card {
    background: var(--bg-primary);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-sm);
    padding: 1.5rem;
}

.logs-card {
    background: var(--bg-primary);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header-custom {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.card-body-custom {
    padding: 1.5rem;
}

.logs-table {
    margin: 0;
}

.log-timestamp {
    font-weight: 500;
    color: var(--text-primary);
}

.log-relative-time {
    font-size: 0.75rem;
}

.log-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.log-user-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
}

.action-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.request-id {
    font-size: 0.75rem;
    background: var(--bg-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.metadata-content {
    background: var(--bg-secondary);
    padding: 0.75rem;
    border-radius: 0.25rem;
    border: 1px solid var(--border-color);
}

.metadata-content pre {
    margin: 0;
    font-size: 0.75rem;
    color: var(--text-secondary);
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

.form-control, .form-select {
    border: 1px solid var(--border-color);
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    background-color: var(--bg-primary);
    color: var(--text-primary);
}
</style>
@endpush
@endsection

