@if($tags->count() > 0)
<table class="admin-table">
    <thead>
        <tr>
            <th>Tag</th>
            <th>Type</th>
            <th>Status</th>
            <th>Usage</th>
            <th>Created</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($tags as $tag)
        <tr>
            <td>
                <div class="user-info">
                    <div class="user-name">{{ $tag->name }}</div>
                    <div class="user-email text-muted">{{ $tag->slug }}</div>
                </div>

                {{-- 修改这里：data-id 改为 data-slug --}}
                <form class="tag-rename-form mt-2" data-slug="{{ $tag->slug }}">
                    <div class="d-flex gap-2">
                        <input class="admin-search-input" style="height: 42px;"
                               name="name" value="{{ $tag->name }}">
                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                    </div>
                </form>
            </td>

            <td>
                <span class="role-badge {{ $tag->type === 'official' ? 'club' : 'student' }}">
                    {{ $tag->type }}
                </span>
            </td>

            <td>
                <span class="status-badge {{ $tag->status }}">
                    {{ ucfirst($tag->status) }}
                </span>
            </td>

            <td class="text-muted">{{ $tag->usage_count }}</td>

            <td class="text-muted">
                {{ optional($tag->created_at)->diffForHumans() }}
            </td>

            <td class="text-end">
                <div class="action-buttons justify-content-end">
                    @if($tag->status === 'pending')
                        {{-- 修改这里：data-id 改为 data-slug --}}
                        <button type="button" class="btn btn-sm btn-outline-success btn-tag-approve" 
                                data-slug="{{ $tag->slug }}">Approve</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-tag-reject" 
                                data-slug="{{ $tag->slug }}">Reject</button>
                    @endif

                    @if($tag->status === 'active')
                        {{-- 修改这里：data-id 改为 data-slug --}}
                        <button type="button" class="btn btn-sm btn-outline-danger btn-tag-disable" 
                                data-slug="{{ $tag->slug }}">Disable</button>
                    @elseif($tag->status === 'inactive')
                        {{-- 修改这里：data-id 改为 data-slug --}}
                        <button type="button" class="btn btn-sm btn-outline-success btn-tag-enable" 
                                data-slug="{{ $tag->slug }}">Enable</button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
    <div class="empty-state-title">No Tags Found</div>
    <div class="empty-state-text">No tags match your current filters.</div>
</div>
@endif