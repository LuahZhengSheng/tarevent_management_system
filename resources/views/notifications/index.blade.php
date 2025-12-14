@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
<style>
    .notifications-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .notifications-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        background: var(--bg-secondary);
        padding: 0.5rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-tab:hover {
        background: var(--primary-light);
        color: var(--primary);
    }

    .filter-tab.active {
        background: var(--primary);
        color: white;
        font-weight: 600;
    }

    .filter-tab .badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.125rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
    }

    .filter-tab.active .badge {
        background: rgba(255, 255, 255, 0.3);
    }

    .batch-actions {
        display: none;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--primary-light);
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        animation: slideDown 0.3s ease;
    }

    .batch-actions.show {
        display: flex;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notifications-list {
        background: var(--bg-primary);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .notification-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: var(--bg-secondary);
    }

    .notification-item.unread {
        background: var(--primary-light);
    }

    .notification-item.unread:hover {
        background: rgba(37, 99, 235, 0.15);
    }

    .notification-checkbox {
        margin-top: 0.25rem;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .notification-icon.event_updated {
        background: var(--info-light);
        color: var(--info);
    }

    .notification-icon.event_cancelled {
        background: var(--error-light);
        color: var(--error);
    }

    .notification-icon.event_time_changed,
    .notification-icon.event_venue_changed {
        background: var(--warning-light);
        color: var(--warning);
    }

    .notification-icon.registration_confirmed,
    .notification-icon.payment_confirmed {
        background: var(--success-light);
        color: var(--success);
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-title .unread-dot {
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
        flex-shrink: 0;
    }

    .notification-message {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 0.5rem;
    }

    .notification-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.8rem;
        color: var(--text-tertiary);
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .notification-action-btn {
        padding: 0.5rem;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .notification-action-btn:hover {
        background: var(--bg-secondary);
        color: var(--primary);
        border-color: var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: var(--text-secondary);
    }

    .btn-action {
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .btn-primary-action {
        background: var(--primary);
        color: white;
    }

    .btn-primary-action:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary-action {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary-action:hover {
        background: var(--bg-secondary);
        border-color: var(--border-hover);
    }

    .btn-danger-action {
        background: var(--error);
        color: white;
    }

    .btn-danger-action:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .notifications-header {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-tabs {
            flex-direction: column;
        }

        .filter-tab {
            justify-content: space-between;
        }

        .batch-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .notification-item {
            flex-direction: column;
        }

        .notification-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endpush

@section('content')
<div class="notifications-container">
    <!-- Header -->
    <div class="notifications-header">
        <h1 class="notifications-title">
            <i class="bi bi-bell"></i> Notifications
        </h1>
        <div class="header-actions">
            <button class="btn-action btn-primary-action" id="markAllReadBtn">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
            <button class="btn-action btn-secondary-action" id="selectAllBtn">
                <i class="bi bi-check-square"></i> Select All
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            <i class="bi bi-inbox"></i>
            All
            <span class="badge">{{ $stats['all'] }}</span>
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
           class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">
            <i class="bi bi-envelope"></i>
            Unread
            <span class="badge">{{ $stats['unread'] }}</span>
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
           class="filter-tab {{ $filter === 'read' ? 'active' : '' }}">
            <i class="bi bi-envelope-open"></i>
            Read
            <span class="badge">{{ $stats['read'] }}</span>
        </a>
    </div>

    <!-- Batch Actions -->
    <div class="batch-actions" id="batchActions">
        <div style="flex: 1;">
            <span id="selectedCount">0</span> selected
        </div>
        <button class="btn-action btn-danger-action" id="batchDeleteBtn">
            <i class="bi bi-trash"></i> Delete Selected
        </button>
        <button class="btn-action btn-secondary-action" id="cancelSelectionBtn">
            <i class="bi bi-x"></i> Cancel
        </button>
    </div>

    <!-- Notifications List -->
    @if($notifications->isEmpty())
        <div class="notifications-list">
            <div class="empty-state">
                <div class="empty-state-icon">
                    @if($filter === 'unread')
                        🎉
                    @elseif($filter === 'read')
                        📭
                    @else
                        🔔
                    @endif
                </div>
                <div class="empty-state-title">
                    @if($filter === 'unread')
                        All caught up!
                    @elseif($filter === 'read')
                        No read notifications
                    @else
                        No notifications yet
                    @endif
                </div>
                <p class="empty-state-text">
                    @if($filter === 'unread')
                        You have no unread notifications.
                    @elseif($filter === 'read')
                        You haven't read any notifications yet.
                    @else
                        When you receive notifications, they'll appear here.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="notifications-list">
            @foreach($notifications as $notification)
            <div class="notification-item {{ $notification->is_unread ? 'unread' : '' }}" 
                 data-notification-id="{{ $notification->id }}">
                <input type="checkbox" class="notification-checkbox" value="{{ $notification->id }}">
                
                <div class="notification-icon {{ $notification->type }}">
                    <i class="bi {{ $notification->icon }}"></i>
                </div>

                <div class="notification-content" onclick="window.location.href='{{ route('notifications.show', $notification) }}'">
                    <div class="notification-title">
                        @if($notification->is_unread)
                        <span class="unread-dot"></span>
                        @endif
                        {{ $notification->title }}
                    </div>
                    <div class="notification-message">
                        {{ $notification->message }}
                    </div>
                    <div class="notification-meta">
                        <span><i class="bi bi-clock"></i> {{ $notification->time_ago }}</span>
                        @if($notification->priority !== 'normal')
                        <span class="{{ $notification->color_class }}">
                            <i class="bi bi-exclamation-circle"></i> {{ ucfirst($notification->priority) }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="notification-actions">
                    @if($notification->is_unread)
                    <button class="notification-action-btn mark-read-btn" 
                            data-id="{{ $notification->id }}"
                            title="Mark as read">
                        <i class="bi bi-check"></i>
                    </button>
                    @else
                    <button class="notification-action-btn mark-unread-btn" 
                            data-id="{{ $notification->id }}"
                            title="Mark as unread">
                        <i class="bi bi-envelope"></i>
                    </button>
                    @endif
                    <button class="notification-action-btn delete-btn" 
                            data-id="{{ $notification->id }}"
                            title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $notifications->appends(['filter' => $filter])->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="deleteCount"></span> notification(s)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedNotifications = new Set();
    let pendingDeleteIds = [];

    // Checkbox handling
    $('.notification-checkbox').on('change', function() {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            selectedNotifications.add(id);
        } else {
            selectedNotifications.delete(id);
        }
        updateBatchActions();
    });

    // Select all button
    $('#selectAllBtn').on('click', function() {
        $('.notification-checkbox').prop('checked', true).trigger('change');
    });

    // Cancel selection
    $('#cancelSelectionBtn').on('click', function() {
        $('.notification-checkbox').prop('checked', false);
        selectedNotifications.clear();
        updateBatchActions();
    });

    function updateBatchActions() {
        const count = selectedNotifications.size;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#batchActions').addClass('show');
        } else {
            $('#batchActions').removeClass('show');
        }
    }

    // Mark as read
    $('.mark-read-btn').on('click', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        markAsRead(id);
    });

    // Mark as unread
    $('.mark-unread-btn').on('click', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        markAsUnread(id);
    });

    // Mark all as read
    $('#markAllReadBtn').on('click', function() {
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                window.location.reload();
            },
            error: function() {
                alert('Failed to mark all as read. Please try again.');
            }
        });
    });

    function markAsRead(id) {
        $.ajax({
            url: `/notifications/${id}/mark-as-read`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                window.location.reload();
            }
        });
    }

    function markAsUnread(id) {
        $.ajax({
            url: `/notifications/${id}/mark-as-unread`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                window.location.reload();
            }
        });
    }

    // Delete single notification
    $('.delete-btn').on('click', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        pendingDeleteIds = [id];
        $('#deleteCount').text('1');
        $('#deleteModal').modal('show');
    });

    // Batch delete
    $('#batchDeleteBtn').on('click', function() {
        pendingDeleteIds = Array.from(selectedNotifications);
        $('#deleteCount').text(pendingDeleteIds.length);
        $('#deleteModal').modal('show');
    });

    // Confirm delete
    $('#confirmDeleteBtn').on('click', function() {
        $.ajax({
            url: '{{ route("notifications.batch-delete") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                notification_ids: pendingDeleteIds
            },
            success: function() {
                $('#deleteModal').modal('hide');
                window.location.reload();
            },
            error: function() {
                alert('Failed to delete notifications. Please try again.');
            }
        });
    });
});
</script>
@endpush