@extends('layouts.club')

@section('title', 'Join Requests - TAREvent')

@section('content')
<div class="club-join-requests-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Join Requests</span>
                    </div>
                    <h1 class="page-title">Join Requests</h1>
                    <p class="page-description">Review and manage student join requests for your club</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="joinRequestsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-requests" type="button" role="tab">
                    <i class="bi bi-hourglass-split me-2"></i>Pending
                    <span class="badge bg-warning ms-2">{{ $pendingRequests->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-requests" type="button" role="tab">
                    <i class="bi bi-check-circle me-2"></i>Approved
                    <span class="badge bg-success ms-2">{{ $approvedRequests->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-requests" type="button" role="tab">
                    <i class="bi bi-x-circle me-2"></i>Rejected
                    <span class="badge bg-danger ms-2">{{ $rejectedRequests->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="joinRequestsTabContent">
            <!-- Pending Requests Tab -->
            <div class="tab-pane fade show active" id="pending-requests" role="tabpanel">
                <div class="requests-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Pending Join Requests</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchPendingRequests" placeholder="Search requests...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($pendingRequests->count() > 0)
                        <div class="requests-list">
                            @foreach($pendingRequests as $request)
                            <div class="request-item" data-request-id="{{ $request->id }}" data-user-id="{{ $request->user_id }}">
                                <div class="request-user-info">
                                    <img src="{{ $request->user->profile_photo_url }}" 
                                         alt="{{ $request->user->name }}" 
                                         class="request-avatar"
                                         onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                    <div class="request-user-details">
                                        <div class="request-user-name">{{ $request->user->name }}</div>
                                        <div class="request-user-email">{{ $request->user->email }}</div>
                                        @if($request->reason)
                                        <div class="request-reason">
                                            <i class="bi bi-chat-left-text me-1"></i>
                                            <span>{{ $request->reason }}</span>
                                        </div>
                                        @endif
                                        <div class="request-date">
                                            <i class="bi bi-clock me-1"></i>
                                            <span>Requested {{ $request->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn btn-sm btn-success btn-approve-request" 
                                            data-request-id="{{ $request->id }}" 
                                            data-user-id="{{ $request->user_id }}"
                                            data-user-name="{{ $request->user->name }}">
                                        <i class="bi bi-check-circle me-1"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-reject-request" 
                                            data-request-id="{{ $request->id }}" 
                                            data-user-id="{{ $request->user_id }}"
                                            data-user-name="{{ $request->user->name }}">
                                        <i class="bi bi-x-circle me-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-hourglass-split empty-icon"></i>
                            <p class="empty-text">No pending join requests.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approved Requests Tab -->
            <div class="tab-pane fade" id="approved-requests" role="tabpanel">
                <div class="requests-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Approved Join Requests</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchApprovedRequests" placeholder="Search requests...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($approvedRequests->count() > 0)
                        <div class="requests-list">
                            @foreach($approvedRequests as $request)
                            <div class="request-item request-item-approved">
                                <div class="request-user-info">
                                    <img src="{{ $request->user->profile_photo_url }}" 
                                         alt="{{ $request->user->name }}" 
                                         class="request-avatar"
                                         onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                    <div class="request-user-details">
                                        <div class="request-user-name">{{ $request->user->name }}</div>
                                        <div class="request-user-email">{{ $request->user->email }}</div>
                                        <div class="request-date">
                                            <i class="bi bi-check-circle me-1 text-success"></i>
                                            <span>Approved {{ $request->updated_at ? $request->updated_at->diffForHumans() : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-check-circle empty-icon"></i>
                            <p class="empty-text">No approved requests yet.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rejected Requests Tab -->
            <div class="tab-pane fade" id="rejected-requests" role="tabpanel">
                <div class="requests-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Rejected Join Requests</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchRejectedRequests" placeholder="Search requests...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($rejectedRequests->count() > 0)
                        <div class="requests-list">
                            @foreach($rejectedRequests as $request)
                            <div class="request-item request-item-rejected">
                                <div class="request-user-info">
                                    <img src="{{ $request->user->profile_photo_url }}" 
                                         alt="{{ $request->user->name }}" 
                                         class="request-avatar"
                                         onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                    <div class="request-user-details">
                                        <div class="request-user-name">{{ $request->user->name }}</div>
                                        <div class="request-user-email">{{ $request->user->email }}</div>
                                        @if($request->rejection_reason)
                                        <div class="request-reason text-danger">
                                            <i class="bi bi-x-circle me-1"></i>
                                            <span>{{ $request->rejection_reason }}</span>
                                        </div>
                                        @endif
                                        <div class="request-date">
                                            <i class="bi bi-x-circle me-1 text-danger"></i>
                                            <span>Rejected {{ $request->updated_at ? $request->updated_at->diffForHumans() : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-x-circle empty-icon"></i>
                            <p class="empty-text">No rejected requests yet.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Join Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reject <strong id="rejectUserName"></strong>'s join request?</p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.club-join-requests-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.requests-card {
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

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.card-body-custom {
    padding: 1.5rem;
}

.requests-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.request-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.request-item:hover {
    box-shadow: var(--shadow-sm);
}

.request-item-approved {
    border-left: 4px solid var(--success);
}

.request-item-rejected {
    border-left: 4px solid var(--danger);
}

.request-user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.request-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.request-user-details {
    flex: 1;
}

.request-user-name {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.request-user-email {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.request-reason {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
    font-style: italic;
}

.request-date {
    font-size: 0.875rem;
    color: var(--text-tertiary);
}

.request-actions {
    display: flex;
    gap: 0.5rem;
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Search functionality
    $('#searchPendingRequests').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#pending-requests .request-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    $('#searchApprovedRequests').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#approved-requests .request-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    $('#searchRejectedRequests').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#rejected-requests .request-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    // Approve request
    $('.btn-approve-request').on('click', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');

        if (!confirm(`Are you sure you want to approve ${userName}'s join request?`)) {
            return;
        }

        $.ajax({
            url: `/club/join-requests/${userId}/approve`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to approve join request.';
                alert(error);
            }
        });
    });

    // Reject request
    $('.btn-reject-request').on('click', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        $('#rejectUserName').text(userName);
        $('#rejectForm').data('user-id', userId);
        $('#rejectReason').val('');
        $('#rejectModal').modal('show');
    });

    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const reason = $('#rejectReason').val();

        $.ajax({
            url: `/club/join-requests/${userId}/reject`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            data: {
                reason: reason,
            },
            success: function(response) {
                $('#rejectModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to reject join request.';
                alert(error);
            }
        });
    });
});
</script>
@endpush
@endsection

