{{-- Admin Forums - Posts --}}
@extends('layouts.admin')

@section('title', 'Forum Posts')

@section('content')
<div class="admin-users-page">
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="admin-page-title">Forum Posts</h1>
            <p class="admin-page-subtitle">View and filter all forum posts (read-only).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.forums.tags.index') }}" class="btn-add-user">
                <i class="bi bi-tags me-2"></i>Manage Tags
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="admin-filter-card mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="admin-search-wrapper">
                    <i class="bi bi-search admin-search-icon"></i>
                    <input type="text" id="searchInput" class="admin-search-input"
                           placeholder="Search title/content/author/tag..."
                           value="{{ request('search', '') }}">
                    <button type="button" class="admin-search-clear" id="clearSearch"
                            style="display: {{ request('search') ? 'flex' : 'none' }};">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-2">
                <select id="statusFilter" class="admin-filter-select">
                    <option value="">All Status</option>
                    <option value="published" @selected(request('status')==='published')>Published</option>
                    <option value="draft" @selected(request('status')==='draft')>Draft</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="visibilityFilter" class="admin-filter-select">
                    <option value="">All Visibility</option>
                    <option value="public" @selected(request('visibility')==='public')>Public</option>
                    <option value="club_only" @selected(request('visibility')==='club_only')>Club Only</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="categoryFilter" class="admin-filter-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->id)>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select id="clubFilter" class="admin-filter-select">
                    <option value="">All Clubs</option>
                    @foreach($clubs as $club)
                    <option value="{{ $club->id }}" @selected((string)request('club_id')===(string)$club->id)>
                        {{ $club->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tags (multi-select) --}}
            <div class="col-md-4">
                <select id="tagsFilter" class="admin-filter-multiselect" multiple>
                    @foreach($allTags as $t)
                    <option value="{{ $t->slug }}" @selected(in_array($t->slug, $selectedTags ?? [], true))>
                        {{ $t->name }} ({{ $t->status }})
                    </option>
                    @endforeach
                </select>
                <div class="text-muted mt-1" style="font-size: 0.8125rem;">
                    Hold Ctrl/Command to select multiple tags (AND logic).
                </div>
            </div>

            <div class="col-md-2">
                <input type="date" id="dateFrom" class="admin-date-input" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <input type="date" id="dateTo" class="admin-date-input" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2">
                <select id="hasMediaFilter" class="admin-filter-select">
                    <option value="">Has Media (All)</option>
                    <option value="yes" @selected(request('has_media')==='yes')>Yes</option>
                    <option value="no" @selected(request('has_media')==='no')>No</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="sortBy" class="admin-filter-select">
                    <option value="recent" @selected(request('sortby','recent')==='recent')>Recent</option>
                    <option value="popular" @selected(request('sortby')==='popular')>Popular</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table (reduced columns) --}}
    <div class="admin-table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Title</th>
                    <th style="width: 26%;">Author</th>
                    <th style="width: 14%;">Status</th>
                    <th style="width: 14%;">Visibility</th>
                    <th class="text-end th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-name">{{ $post->title }}</div>
                            <div class="user-email text-muted">#{{ $post->id }} • {{ $post->slug }}</div>
                        </div>
                    </td>

                    <td>
                        <div class="user-info">
                            <div class="user-name">{{ $post->user?->name ?? '-' }}</div>
                            <div class="user-email">{{ $post->user?->email ?? '' }}</div>
                        </div>
                    </td>

                    <td>
                        <span class="status-badge {{ $post->status }}">
                            {{ ucfirst($post->status) }}
                        </span>
                    </td>

                    <td>
                        <span class="role-badge {{ $post->visibility === 'public' ? 'student' : 'club' }}">
                            {{ $post->visibility }}
                        </span>
                    </td>

                    <td class="text-end td-actions">
                        <div class="action-buttons justify-content-end">
                            <button type="button" class="btn-action btn-view-post"
                                    title="View Details" data-post-id="{{ $post->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-inboxes"></i></div>
                            <div class="empty-state-title">No Posts Found</div>
                            <div class="empty-state-text">No posts match your current filters.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3" id="paginationContainer">
        @include('admin.users.partials.pagination', ['users' => $posts])
    </div>
</div>

{{-- View Modal (now contains “moved columns”: category/tags/media/stats/clubs/hasMedia) --}}
<div class="modal fade" id="postViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="postViewLoading" class="text-muted">Loading...</div>

                <div id="postViewContent" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Title:</strong> <span id="pvTitle"></span></div>
                        <div class="col-md-6"><strong>Author:</strong> <span id="pvAuthor"></span></div>

                        <div class="col-md-4"><strong>Category:</strong> <span id="pvCategory"></span></div>
                        <div class="col-md-4"><strong>Status:</strong> <span id="pvStatus"></span></div>
                        <div class="col-md-4"><strong>Visibility:</strong> <span id="pvVisibility"></span></div>

                        <div class="col-md-6"><strong>Clubs:</strong> <span id="pvClubs"></span></div>
                        <div class="col-md-6"><strong>Tags:</strong> <span id="pvTags"></span></div>

                        <div class="col-md-6"><strong>Has Media:</strong> <span id="pvHasMedia"></span></div>
                        <div class="col-md-6"><strong>Stats:</strong> <span id="pvStats"></span></div>
                    </div>

                    <hr>
                    <div class="mb-2"><strong>Content:</strong></div>
                    <div class="p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                        <div id="pvContent"></div>
                    </div>

                    <hr>
                    <div class="mb-2"><strong>Media:</strong></div>
                    <div id="pvMedia" class="text-muted"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* same look as users page */
    .admin-users-page {
        max-width: 1400px;
        margin: 0 auto;
    }
    .admin-page-header {
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
    .admin-page-title {
        font-size:1.75rem;
        font-weight:600;
        color:var(--text-primary);
        margin-bottom:0.25rem;
        letter-spacing:-0.02em;
    }
    .admin-page-subtitle {
        font-size:0.9375rem;
        color:var(--text-secondary);
        margin:0;
    }

    .admin-filter-card {
        background:var(--bg-primary);
        border-radius:1rem;
        border:1px solid var(--border-color);
        padding:1.5rem;
        box-shadow:var(--shadow-sm);
    }

    .admin-search-wrapper {
        position:relative;
        display:flex;
        align-items:center;
    }
    .admin-search-icon {
        position:absolute;
        left:1rem;
        color:var(--text-tertiary);
        font-size:1rem;
        pointer-events:none;
    }
    .admin-search-input {
        width:100%;
        padding:0.75rem 1rem 0.75rem 2.75rem;
        font-size:0.9375rem;
        border:1px solid var(--border-color);
        border-radius:0.75rem;
        background:var(--bg-primary);
        color:var(--text-primary);
        transition:all 0.2s ease;
    }
    .admin-search-input:focus {
        outline:none;
        border-color:var(--primary);
        box-shadow:0 0 0 3px var(--primary-light);
    }

    .admin-search-clear {
        position:absolute;
        right:0.75rem;
        background:none;
        border:none;
        color:var(--text-tertiary);
        cursor:pointer;
        padding:0.25rem;
        display:flex;
        align-items:center;
        justify-content:center;
        transition:color 0.2s ease;
    }
    .admin-search-clear:hover {
        color:var(--text-primary);
    }

    .admin-filter-select{
        width:100%;
        padding:0.75rem 1rem;
        font-size:0.9375rem;
        border:1px solid var(--border-color);
        border-radius:0.75rem;
        background:var(--bg-primary);
        color:var(--text-primary);
        cursor:pointer;
        transition:all 0.2s ease;
        appearance:none;
        background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat:no-repeat;
        background-position:right 0.75rem center;
        background-size:16px 12px;
        padding-right:2.5rem;
    }
    .admin-filter-select:focus {
        outline:none;
        border-color:var(--primary);
        box-shadow:0 0 0 3px var(--primary-light);
    }

    .admin-date-input{
        width:100%;
        padding:0.75rem 1rem;
        font-size:0.9375rem;
        border:1px solid var(--border-color);
        border-radius:0.75rem;
        background:var(--bg-primary);
        color:var(--text-primary);
        transition:all 0.2s ease;
    }
    .admin-date-input:focus {
        outline:none;
        border-color:var(--primary);
        box-shadow:0 0 0 3px var(--primary-light);
    }

    /* multi-select fix */
    .admin-filter-multiselect{
        width:100%;
        min-height:120px;
        padding:0.75rem 1rem;
        font-size:0.9375rem;
        border:1px solid var(--border-color);
        border-radius:0.75rem;
        background:var(--bg-primary);
        color:var(--text-primary);
        transition:all 0.2s ease;
        appearance:auto;
        background-image:none;
    }
    .admin-filter-multiselect:focus {
        outline:none;
        border-color:var(--primary);
        box-shadow:0 0 0 3px var(--primary-light);
    }

    .admin-table-card {
        background:var(--bg-primary);
        border-radius:1rem;
        border:1px solid var(--border-color);
        box-shadow:var(--shadow-sm);
        overflow:hidden;
    }
    .admin-table {
        width:100%;
        margin:0;
        table-layout: fixed;
    }
    .admin-table thead {
        background:var(--bg-secondary);
    }
    .admin-table thead th {
        padding:1rem 1.5rem;
        font-size:0.75rem;
        text-transform:uppercase;
        letter-spacing:0.08em;
        color:var(--text-tertiary);
        font-weight:600;
        border-bottom:1px solid var(--border-color);
    }
    .admin-table tbody td {
        padding:1.25rem 1.5rem;
        font-size:0.9375rem;
        color:var(--text-primary);
        border-bottom:1px solid var(--border-color);
        vertical-align:middle;
    }
    .admin-table tbody tr:last-child td {
        border-bottom:none;
    }
    .admin-table tbody tr:hover {
        background:var(--bg-secondary);
    }

    .user-info {
        display:flex;
        flex-direction:column;
        min-width:0;
    }
    .user-name {
        font-weight:600;
        color:var(--text-primary);
        margin-bottom:0.125rem;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .user-email {
        font-size:0.8125rem;
        color:var(--text-secondary);
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .role-badge {
        display:inline-flex;
        align-items:center;
        gap:0.375rem;
        padding:0.375rem 0.75rem;
        border-radius:0.5rem;
        font-size:0.8125rem;
        font-weight:500;
    }
    .role-badge.student {
        background:var(--info-light);
        color:var(--info);
    }
    .role-badge.club {
        background:var(--primary-light);
        color:var(--primary);
    }

    .status-badge {
        display:inline-flex;
        align-items:center;
        gap:0.375rem;
        padding:0.375rem 0.75rem;
        border-radius:0.5rem;
        font-size:0.8125rem;
        font-weight:500;
    }
    .status-badge.published {
        background:var(--success-light);
        color:var(--success);
    }
    .status-badge.draft {
        background:var(--warning-light);
        color:var(--warning);
    }

    .action-buttons {
        display:flex;
        align-items:center;
        gap:0.5rem;
    }
    .btn-action {
        padding:0.5rem;
        border:none;
        background:none;
        color:var(--text-secondary);
        cursor:pointer;
        border-radius:0.5rem;
        transition:all 0.2s ease;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .btn-action:hover {
        background:var(--bg-secondary);
        color:var(--primary);
    }

    .btn-add-user {
        display:inline-flex;
        align-items:center;
        padding:0.75rem 1.5rem;
        background:var(--primary);
        color:#fff;
        border-radius:0.75rem;
        text-decoration:none;
        font-weight:500;
        transition:all 0.2s ease;
        font-size:0.9375rem;
    }
    .btn-add-user:hover {
        background:var(--primary-hover);
        color:#fff;
        transform:translateY(-1px);
        box-shadow:var(--shadow-md);
    }

    .empty-state {
        text-align:center;
        padding:4rem 2rem;
        color:var(--text-secondary);
    }
    .empty-state-icon {
        font-size:3rem;
        color:var(--text-tertiary);
        margin-bottom:1rem;
    }
    .empty-state-title {
        font-size:1.125rem;
        font-weight:600;
        color:var(--text-primary);
        margin-bottom:0.5rem;
    }
    .empty-state-text {
        font-size:0.9375rem;
        color:var(--text-secondary);
    }

    /* Make Actions column always visible */
    .th-actions,
    .td-actions {
        width: 80px;            /* 固定宽度，避免被挤掉 */
        min-width: 80px;
        white-space: nowrap;
    }

    /* Title 不要撑爆：强制省略 */
    .admin-table td:first-child .user-name,
    .admin-table td:first-child .user-email {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

</style>
@endpush

@push('scripts')
<script>
    (function () {
        'use strict';

        const applyFiltersToUrl = () => {
            const url = new URL(window.location.href);
            url.search = '';

            const search = ($('#searchInput').val() || '').trim();
            if (search)
                url.searchParams.set('search', search);

            const status = $('#statusFilter').val();
            if (status)
                url.searchParams.set('status', status);

            const visibility = $('#visibilityFilter').val();
            if (visibility)
                url.searchParams.set('visibility', visibility);

            const category_id = $('#categoryFilter').val();
            if (category_id)
                url.searchParams.set('category_id', category_id);

            const club_id = $('#clubFilter').val();
            if (club_id)
                url.searchParams.set('club_id', club_id);

            const date_from = $('#dateFrom').val();
            if (date_from)
                url.searchParams.set('date_from', date_from);

            const date_to = $('#dateTo').val();
            if (date_to)
                url.searchParams.set('date_to', date_to);

            const has_media = $('#hasMediaFilter').val();
            if (has_media)
                url.searchParams.set('has_media', has_media);

            const sortby = $('#sortBy').val();
            if (sortby)
                url.searchParams.set('sortby', sortby);

            const tags = $('#tagsFilter').val() || [];
            tags.forEach(slug => url.searchParams.append('tags[]', slug));

            window.location.href = url.toString();
        };

        let searchTimeout = null;
        $('#searchInput').on('input', function () {
            clearTimeout(searchTimeout);
            const v = $(this).val();
            $('#clearSearch').toggle(!!v);
            searchTimeout = setTimeout(applyFiltersToUrl, 500);
        });

        $('#clearSearch').on('click', function () {
            $('#searchInput').val('');
            $(this).hide();
            applyFiltersToUrl();
        });

        $('#statusFilter, #visibilityFilter, #categoryFilter, #clubFilter, #dateFrom, #dateTo, #hasMediaFilter, #sortBy').on('change', applyFiltersToUrl);
        $('#tagsFilter').on('change', applyFiltersToUrl);

        const postViewModal = new bootstrap.Modal(document.getElementById('postViewModal'));

        $(document).on('click', '.btn-view-post', function () {
            const postId = $(this).data('post-id');

            $('#postViewLoading').show().text('Loading...');
            $('#postViewContent').hide();
            postViewModal.show();

            $.ajax({
                url: "{{ route('admin.forums.posts.show', ['post' => '__ID__']) }}".replace('__ID__', postId),
                type: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function (resp) {
                    if (!resp || !resp.success) {
                        $('#postViewLoading').text('Failed to load post.');
                        return;
                    }

                    const p = resp.post;

                    $('#pvTitle').text(p.title || '');
                    $('#pvAuthor').text(p.author ? (p.author.name + ' (' + p.author.email + ')') : '-');
                    $('#pvCategory').text(p.category ? p.category.name : '-');
                    $('#pvStatus').text(p.status);
                    $('#pvVisibility').text(p.visibility);
                    $('#pvClubs').text((p.clubs && p.clubs.length) ? p.clubs.map(c => c.name).join(', ') : '-');
                    $('#pvTags').text((p.tags && p.tags.length) ? p.tags.map(t => t.name + ' (' + t.status + ')').join(', ') : '-');
                    $('#pvHasMedia').text(p.has_media ? 'Yes' : 'No');
                    $('#pvStats').text('Views: ' + p.views_count + ', Likes: ' + p.likes_count + ', Comments: ' + p.comments_count);

                    $('#pvContent').text(p.content || '');

                    if (!p.media || !p.media.length) {
                        $('#pvMedia').text('No media.');
                    } else {
                        const items = p.media.map(m => {
                            const link = m.url
                                    ? `<a href="${m.url}" target="_blank" rel="noopener">open</a>`
                                    : `<span class="text-muted">private</span>`;
                            return `<div class="mb-1">[${m.type}] ${m.original_name || m.path} (${m.disk}) - ${link}</div>`;
                        }).join('');
                        $('#pvMedia').html(items);
                    }

                    $('#postViewLoading').hide();
                    $('#postViewContent').show();
                },
                error: function () {
                    $('#postViewLoading').text('Failed to load post.');
                }
            });
        });

    })();
</script>
@endpush
@endsection
