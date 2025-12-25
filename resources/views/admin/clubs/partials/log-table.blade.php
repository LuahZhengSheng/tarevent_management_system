<!-- Author: Auto-generated -->
@if($logs->count() > 0)
<table class="admin-table">
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>Action</th>
            <th>Actor</th>
            <th>Target User</th>
            <th>Request ID</th>
            <th>Metadata</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>
                <span class="text-muted">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
            </td>
            <td>
                <span class="action-badge">{{ str_replace('_', ' ', $log->action) }}</span>
            </td>
            <td>
                @if($log->actor)
                <div class="d-flex align-items-center gap-2">
                    <span>{{ $log->actor->name }}</span>
                    <small class="text-muted">({{ $log->actor->email }})</small>
                </div>
                @else
                <span class="text-muted">–</span>
                @endif
            </td>
            <td>
                @if($log->targetUser)
                <div class="d-flex align-items-center gap-2">
                    <span>{{ $log->targetUser->name }}</span>
                    <small class="text-muted">({{ $log->targetUser->email }})</small>
                </div>
                @else
                <span class="text-muted">–</span>
                @endif
            </td>
            <td>
                @if($log->request_id)
                <code class="text-muted" style="font-size: 0.75rem;">{{ \Illuminate\Support\Str::limit($log->request_id, 20) }}</code>
                @else
                <span class="text-muted">–</span>
                @endif
            </td>
            <td>
                @if($log->metadata && count($log->metadata) > 0)
                <details>
                    <summary class="text-primary" style="cursor: pointer; font-size: 0.875rem;">View</summary>
                    <pre style="font-size: 0.75rem; margin-top: 0.5rem; padding: 0.5rem; background: var(--bg-secondary); border-radius: 0.5rem; overflow-x: auto;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                </details>
                @else
                <span class="text-muted">–</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <div class="empty-state-icon">
        <i class="bi bi-clock-history"></i>
    </div>
    <div class="empty-state-title">No logs found</div>
    <div class="empty-state-text">
        @if(request()->hasAny(['action', 'date_from', 'date_to', 'actor_id']))
            Try adjusting your filters to see more results.
        @else
            No activity logs for this club yet.
        @endif
    </div>
</div>
@endif

