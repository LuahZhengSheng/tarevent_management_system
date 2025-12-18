@if($admins->count() > 0)
<table class="admin-table">
    <thead>
        <tr>
            <th>Administrator</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Permissions</th>
            <th>Last Login</th>
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
                    Super Admin
                </span>
                @else
                <span class="role-badge admin">
                    <i class="bi bi-shield"></i>
                    Administrator
                </span>
                @endif
            </td>
            <td>
                <span class="text-muted">{{ $admin->phone ?? '–' }}</span>
            </td>
            <td>
                <span class="status-badge {{ $admin->status }}">
                    <i class="bi bi-{{ $admin->status === 'active' ? 'check-circle' : ($admin->status === 'suspended' ? 'x-circle' : 'pause-circle') }}"></i>
                    {{ ucfirst($admin->status) }}
                </span>
            </td>
            <td>
                @if($admin->isSuperAdmin())
                    <span class="badge bg-warning text-dark">All Permissions</span>
                @elseif($admin->permissions === null)
                    <span class="badge bg-secondary">Profile Only</span>
                @else
                    <div class="permissions-list-small">
                        @foreach(array_slice($admin->permissions ?? [], 0, 2) as $permission)
                        <span class="permission-badge-small">{{ $permission }}</span>
                        @endforeach
                        @if(count($admin->permissions ?? []) > 2)
                        <span class="text-muted" style="font-size: 0.75rem;">+{{ count($admin->permissions) - 2 }} more</span>
                        @endif
                    </div>
                @endif
            </td>
            <td>
                <span class="text-muted">
                    {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}
                </span>
            </td>
            <td>
                <div class="action-buttons justify-content-end">
                    <a 
                        href="{{ route('admin.administrators.show', $admin) }}" 
                        class="btn-action"
                        title="View Details"
                    >
                        <i class="bi bi-eye"></i>
                    </a>
                    <a 
                        href="{{ route('admin.administrators.edit', $admin) }}" 
                        class="btn-action"
                        title="Edit"
                    >
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button 
                        type="button"
                        class="btn-toggle-status {{ $admin->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}"
                        data-admin-id="{{ $admin->id }}"
                        data-status="{{ $admin->status }}"
                        title="{{ $admin->status === 'active' ? 'Deactivate' : 'Activate' }}"
                        onclick="return false;"
                    >
                        {{ $admin->status === 'active' ? 'Deactivate' : 'Activate' }}
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
        <i class="bi bi-shield-check"></i>
    </div>
    <div class="empty-state-title">No Administrators Found</div>
    <div class="empty-state-text">
        No administrators match your current filters. Try adjusting your search criteria.
    </div>
</div>
@endif

