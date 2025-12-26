<!-- Author: Auto-generated -->
@if($clubs->count() > 0)
<table class="admin-table">
    <thead>
        <tr>
            <th>Club</th>
            <th>Category</th>
            <th>Status</th>
            <th>Members</th>
            <th>Created By</th>
            <th>Created At</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clubs as $club)
        <tr>
            <td>
                <div class="club-logo-cell">
                    <img
                        src="{{ $club->logo ? '/storage/' . $club->logo : asset('images/default-club-avatar.png') }}"
                        alt="{{ $club->name }}"
                        class="club-logo"
                        onerror="this.src='{{ asset('images/avatar/default-club-avatar.png') }}'"
                    >
                    <div class="club-info">
                        <div class="club-name">{{ $club->name }}</div>
                        <div class="club-category">{{ $club->email ?? '–' }}</div>
                    </div>
                </div>
            </td>
            <td>
                @if($club->category)
                <span class="category-badge">{{ ucfirst($club->category) }}</span>
                @else
                <span class="text-muted">–</span>
                @endif
            </td>
            <td>
                <span class="status-badge {{ $club->status }}">
                    <i class="bi bi-{{ $club->status === 'active' ? 'check-circle' : ($club->status === 'inactive' ? 'pause-circle' : ($club->status === 'pending' ? 'hourglass-split' : 'x-circle')) }}"></i>
                    {{ ucfirst($club->status) }}
                </span>
            </td>
            <td>
                <span class="text-muted">{{ $club->members()->wherePivot('status', 'active')->count() }}</span>
            </td>
            <td>
                <span class="text-muted">{{ $club->creator->name ?? '–' }}</span>
            </td>
            <td>
                <span class="text-muted">{{ $club->created_at ? $club->created_at->format('Y-m-d') : '–' }}</span>
            </td>
            <td>
                <div class="action-buttons justify-content-end">
                    <a
                        href="{{ route('admin.clubs.show', $club) }}"
                        class="btn-action"
                        title="View Details"
                    >
                        <i class="bi bi-eye"></i>
                    </a>
                    <a
                        href="{{ route('admin.clubs.logs', $club) }}"
                        class="btn-action"
                        title="View Logs"
                    >
                        <i class="bi bi-clock-history"></i>
                    </a>
                    @if($club->status === 'active')
                    <button
                        type="button"
                        class="btn-toggle-status btn-deactivate"
                        data-club-id="{{ $club->id }}"
                        data-status="{{ $club->status }}"
                        title="Deactivate"
                    >
                        <i class="bi bi-pause-circle me-1"></i>Deactivate
                    </button>
                    @elseif($club->status === 'inactive')
                    <button
                        type="button"
                        class="btn-toggle-status btn-activate"
                        data-club-id="{{ $club->id }}"
                        data-status="{{ $club->status }}"
                        title="Activate"
                    >
                        <i class="bi bi-play-circle me-1"></i>Activate
                    </button>
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
        <i class="bi bi-building"></i>
    </div>
    <div class="empty-state-title">No clubs found</div>
    <div class="empty-state-text">
        @if(request()->hasAny(['search', 'status', 'category']))
            Try adjusting your filters to see more results.
        @else
            Get started by creating your first club.
        @endif
    </div>
</div>
@endif

