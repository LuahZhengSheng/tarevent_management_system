<!-- Author: Tang Lit Xuan -->
@if($users->count() > 0)
<table class="admin-table">
    <thead>
        <tr>
            <th>User</th>
            <th>Role</th>
            <th>Student ID</th>
            <th>Status</th>
            <th>Last Login</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>
                <div class="user-avatar-cell">
                    <img 
                        src="{{ $user->profile_photo_url }}" 
                        alt="{{ $user->name }}"
                        class="user-avatar"
                        onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                    >
                    <div class="user-info">
                        <div class="user-name">{{ $user->name }}</div>
                        <div class="user-email">{{ $user->email }}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="role-badge {{ $user->role === 'club' ? 'club' : 'student' }}">
                    <i class="bi bi-{{ $user->role === 'club' ? 'building' : 'person' }}"></i>
                    {{ $user->role === 'club' ? 'Club Organizer' : 'Student' }}
                </span>
            </td>
            <td>
                <span class="text-muted">{{ $user->student_id ?? '–' }}</span>
            </td>
            <td>
                <span class="status-badge {{ $user->status }}">
                    <i class="bi bi-{{ $user->status === 'active' ? 'check-circle' : ($user->status === 'suspended' ? 'x-circle' : 'pause-circle') }}"></i>
                    {{ ucfirst($user->status) }}
                </span>
            </td>
            <td>
                <span class="text-muted">
                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                </span>
            </td>
            <td>
                <div class="action-buttons justify-content-end">
                    <a 
                        href="{{ route('admin.users.show', $user) }}" 
                        class="btn-action"
                        title="View Details"
                    >
                        <i class="bi bi-eye"></i>
                    </a>
                    <a 
                        href="{{ route('admin.users.edit', $user) }}" 
                        class="btn-action"
                        title="Edit"
                    >
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button 
                        type="button"
                        class="btn-toggle-status {{ $user->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}"
                        data-user-id="{{ $user->id }}"
                        data-status="{{ $user->status }}"
                        title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}"
                    >
                        {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <div class="empty-state-icon">
        <i class="bi bi-people"></i>
    </div>
    <div class="empty-state-title">No Users Found</div>
    <div class="empty-state-text">
        No users match your current filters. Try adjusting your search criteria.
    </div>
</div>
@endif

