@extends('layouts.club')

@section('title', 'Member Management - TAREvent')

@section('content')
<div class="club-members-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Member Management</span>
                    </div>
                    <h1 class="page-title">Member Management</h1>
                    <p class="page-description">Manage club members, roles, blacklist, and removed members</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="membersTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-members" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Active Members
                    <span class="badge bg-primary ms-2">{{ $activeMembers->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="removed-tab" data-bs-toggle="tab" data-bs-target="#removed-members" type="button" role="tab">
                    <i class="bi bi-person-x me-2"></i>Removed Members
                    <span class="badge bg-secondary ms-2">{{ $removedMembers->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="blacklist-tab" data-bs-toggle="tab" data-bs-target="#blacklisted-members" type="button" role="tab">
                    <i class="bi bi-ban me-2"></i>Blacklist
                    <span class="badge bg-danger ms-2">{{ $blacklistedUsers->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="membersTabContent">
            <!-- Active Members Tab -->
            <div class="tab-pane fade show active" id="active-members" role="tabpanel">
                <div class="members-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Active Members</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchActiveMembers" placeholder="Search members...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($activeMembers->count() > 0)
                        <div class="table-responsive">
                            <table class="table members-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="activeMembersTableBody">
                                    @foreach($activeMembers as $member)
                                    <tr data-member-id="{{ $member->id }}" data-member-name="{{ $member->name }}">
                                        <td>
                                            <div class="member-info">
                                                <img src="{{ $member->profile_photo_url }}" 
                                                     alt="{{ $member->name }}" 
                                                     class="member-avatar"
                                                     onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                                <div>
                                                    <div class="member-name">{{ $member->name }}</div>
                                                    <div class="member-email">{{ $member->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm role-select" 
                                                    data-member-id="{{ $member->id }}" 
                                                    data-current-role="{{ $member->pivot->role }}">
                                                @foreach(\App\Models\ClubMemberRole::all() as $role)
                                                <option value="{{ $role }}" {{ $member->pivot->role === $role ? 'selected' : '' }}>
                                                    {{ \App\Models\ClubMemberRole::displayName($role) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $member->pivot->created_at ? $member->pivot->created_at->format('M d, Y') : 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-danger btn-remove-member" 
                                                        data-member-id="{{ $member->id }}" 
                                                        data-member-name="{{ $member->name }}">
                                                    <i class="bi bi-person-x"></i> Remove
                                                </button>
                                                <button class="btn btn-sm btn-warning btn-blacklist-member" 
                                                        data-member-id="{{ $member->id }}" 
                                                        data-member-name="{{ $member->name }}">
                                                    <i class="bi bi-ban"></i> Blacklist
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-people empty-icon"></i>
                            <p class="empty-text">No active members yet.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Removed Members Tab -->
            <div class="tab-pane fade" id="removed-members" role="tabpanel">
                <div class="members-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Removed Members</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchRemovedMembers" placeholder="Search members...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($removedMembers->count() > 0)
                        <div class="table-responsive">
                            <table class="table members-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Role</th>
                                        <th>Removed</th>
                                    </tr>
                                </thead>
                                <tbody id="removedMembersTableBody">
                                    @foreach($removedMembers as $member)
                                    <tr data-member-id="{{ $member->id }}" data-member-name="{{ $member->name }}">
                                        <td>
                                            <div class="member-info">
                                                <img src="{{ $member->profile_photo_url }}" 
                                                     alt="{{ $member->name }}" 
                                                     class="member-avatar"
                                                     onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                                <div>
                                                    <div class="member-name">{{ $member->name }}</div>
                                                    <div class="member-email">{{ $member->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ \App\Models\ClubMemberRole::displayName($member->pivot->role) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $member->pivot->created_at ? $member->pivot->created_at->format('M d, Y') : 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-person-x empty-icon"></i>
                            <p class="empty-text">No removed members.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Blacklisted Members Tab -->
            <div class="tab-pane fade" id="blacklisted-members" role="tabpanel">
                <div class="members-card">
                    <div class="card-header-custom">
                        <h3 class="card-title">Blacklisted Members</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control form-control-sm" id="searchBlacklistedMembers" placeholder="Search members...">
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @if($blacklistedUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table members-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Reason</th>
                                        <th>Blacklisted</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="blacklistedMembersTableBody">
                                    @foreach($blacklistedUsers as $user)
                                    <tr data-member-id="{{ $user->id }}" data-member-name="{{ $user->name }}">
                                        <td>
                                            <div class="member-info">
                                                <img src="{{ $user->profile_photo_url }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="member-avatar"
                                                     onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'">
                                                <div>
                                                    <div class="member-name">{{ $user->name }}</div>
                                                    <div class="member-email">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->pivot->reason ?? 'No reason provided' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->pivot->created_at ? $user->pivot->created_at->format('M d, Y') : 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success btn-unblacklist-member" 
                                                    data-member-id="{{ $user->id }}" 
                                                    data-member-name="{{ $user->name }}">
                                                <i class="bi bi-check-circle"></i> Remove from Blacklist
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-ban empty-icon"></i>
                            <p class="empty-text">No blacklisted members.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Blacklist Modal -->
<div class="modal fade" id="blacklistModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Blacklist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="blacklistForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to blacklist <strong id="blacklistMemberName"></strong>?</p>
                    <div class="mb-3">
                        <label for="blacklistReason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="blacklistReason" name="reason" rows="3" placeholder="Enter reason for blacklisting..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add to Blacklist</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.club-members-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.members-card {
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

.members-table {
    margin: 0;
}

.member-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.member-name {
    font-weight: 500;
    color: var(--text-primary);
}

.member-email {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.role-select {
    min-width: 150px;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
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
    $('#searchActiveMembers').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#activeMembersTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    $('#searchRemovedMembers').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#removedMembersTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    $('#searchBlacklistedMembers').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#blacklistedMembersTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });

    // Update member role
    $('.role-select').on('change', function() {
        const memberId = $(this).data('member-id');
        const newRole = $(this).val();
        const currentRole = $(this).data('current-role');
        const select = $(this);

        if (newRole === currentRole) {
            return;
        }

        if (!confirm(`Are you sure you want to change this member's role to ${newRole}?`)) {
            $(this).val(currentRole);
            return;
        }

        $.ajax({
            url: `/club/members/${memberId}/role`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            data: {
                role: newRole,
            },
            success: function(response) {
                select.data('current-role', newRole);
                // Show success message
                alert('Member role updated successfully.');
            },
            error: function(xhr) {
                select.val(currentRole);
                const error = xhr.responseJSON?.message || 'Failed to update member role.';
                alert(error);
            }
        });
    });

    // Remove member
    $('.btn-remove-member').on('click', function() {
        const memberId = $(this).data('member-id');
        const memberName = $(this).data('member-name');

        if (!confirm(`Are you sure you want to remove ${memberName} from the club?`)) {
            return;
        }

        $.ajax({
            url: `/club/members/${memberId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to remove member.';
                alert(error);
            }
        });
    });

    // Blacklist member
    $('.btn-blacklist-member').on('click', function() {
        const memberId = $(this).data('member-id');
        const memberName = $(this).data('member-name');
        $('#blacklistMemberName').text(memberName);
        $('#blacklistForm').data('member-id', memberId);
        $('#blacklistReason').val('');
        $('#blacklistModal').modal('show');
    });

    $('#blacklistForm').on('submit', function(e) {
        e.preventDefault();
        const memberId = $(this).data('member-id');
        const reason = $('#blacklistReason').val();

        $.ajax({
            url: `/club/members/${memberId}/blacklist`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            data: {
                reason: reason,
            },
            success: function(response) {
                $('#blacklistModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to add member to blacklist.';
                alert(error);
            }
        });
    });

    // Unblacklist member
    $('.btn-unblacklist-member').on('click', function() {
        const memberId = $(this).data('member-id');
        const memberName = $(this).data('member-name');

        if (!confirm(`Are you sure you want to remove ${memberName} from the blacklist?`)) {
            return;
        }

        $.ajax({
            url: `/club/blacklist/${memberId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to remove member from blacklist.';
                alert(error);
            }
        });
    });
});
</script>
@endpush
@endsection

