@extends('layouts.admin')

@section('title', 'Forum Tags')

@section('content')
<div class="admin-users-page">
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="admin-page-title">Forum Tags</h1>
            <p class="admin-page-subtitle">Manage all tags: approve/reject pending, edit name, enable/disable.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.forums.posts.index') }}" class="btn-add-user">
                <i class="bi bi-chat-left-text me-2"></i>Back to Posts
            </a>
        </div>
    </div>

    <div class="admin-filter-card mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="admin-search-wrapper">
                    <i class="bi bi-search admin-search-icon"></i>
                    <input type="text" id="searchInput" class="admin-search-input" placeholder="Search tag name/slug...">
                    <button type="button" class="admin-search-clear" id="clearSearch" style="display:none;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-2">
                <select id="statusFilter" class="admin-filter-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="merged">Merged</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="typeFilter" class="admin-filter-select">
                    <option value="all">All Types</option>
                    <option value="official">Official</option>
                    <option value="community">Community</option>
                </select>
            </div>

            <div class="col-md-2">
                <select id="sortBy" class="admin-filter-select">
                    <option value="usage">Most Used</option>
                    <option value="newest">Newest</option>
                    <option value="name">Name A-Z</option>
                </select>
            </div>
        </div>
    </div>

    <div class="admin-table-card">
        <div id="tagsTableContainer"></div>
    </div>

    <div id="paginationContainer"></div>
</div>

@push('styles')
<style>
/* Same style pack as users index for consistency */
.admin-users-page { max-width: 1400px; margin: 0 auto; }
.admin-page-header { display:flex; align-items:center; justify-content:space-between; }
.admin-page-title { font-size:1.75rem; font-weight:600; color:var(--text-primary); margin-bottom:0.25rem; letter-spacing:-0.02em; }
.admin-page-subtitle { font-size:0.9375rem; color:var(--text-secondary); margin:0; }
.admin-filter-card { background:var(--bg-primary); border-radius:1rem; border:1px solid var(--border-color); padding:1.5rem; box-shadow:var(--shadow-sm); }
.admin-search-wrapper { position:relative; display:flex; align-items:center; }
.admin-search-icon { position:absolute; left:1rem; color:var(--text-tertiary); font-size:1rem; pointer-events:none; }
.admin-search-input { width:100%; padding:0.75rem 1rem 0.75rem 2.75rem; font-size:0.9375rem; border:1px solid var(--border-color); border-radius:0.75rem; background:var(--bg-primary); color:var(--text-primary); transition:all 0.2s ease; }
.admin-search-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-light); }
.admin-search-clear { position:absolute; right:0.75rem; background:none; border:none; color:var(--text-tertiary); cursor:pointer; padding:0.25rem; display:flex; align-items:center; justify-content:center; transition:color 0.2s ease; }
.admin-search-clear:hover { color:var(--text-primary); }
.admin-filter-select { width:100%; padding:0.75rem 1rem; font-size:0.9375rem; border:1px solid var(--border-color); border-radius:0.75rem; background:var(--bg-primary); color:var(--text-primary); cursor:pointer; transition:all 0.2s ease; appearance:none;
    background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat:no-repeat; background-position:right 0.75rem center; background-size:16px 12px; padding-right:2.5rem;
}
.admin-filter-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-light); }
.admin-table-card { background:var(--bg-primary); border-radius:1rem; border:1px solid var(--border-color); box-shadow:var(--shadow-sm); overflow:hidden; }
.admin-table { width:100%; margin:0; }
.admin-table thead { background:var(--bg-secondary); }
.admin-table thead th { padding:1rem 1.5rem; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-tertiary); font-weight:600; border-bottom:1px solid var(--border-color); }
.admin-table tbody td { padding:1.25rem 1.5rem; font-size:0.9375rem; color:var(--text-primary); border-bottom:1px solid var(--border-color); vertical-align:middle; }
.admin-table tbody tr:last-child td { border-bottom:none; }
.admin-table tbody tr:hover { background:var(--bg-secondary); }
.user-info { display:flex; flex-direction:column; }
.user-name { font-weight:600; color:var(--text-primary); margin-bottom:0.125rem; }
.user-email { font-size:0.8125rem; color:var(--text-secondary); }
.status-badge { display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; }
.status-badge.active { background:var(--success-light); color:var(--success); }
.status-badge.pending { background:var(--warning-light); color:var(--warning); }
.status-badge.inactive { background:var(--error-light); color:var(--error); }
.role-badge { display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; }
.role-badge.student { background:var(--info-light); color:var(--info); }
.role-badge.club { background:var(--primary-light); color:var(--primary); }
.action-buttons { display:flex; align-items:center; gap:0.5rem; }
.btn-add-user { display:inline-flex; align-items:center; padding:0.75rem 1.5rem; background:var(--primary); color:white; border-radius:0.75rem; text-decoration:none; font-weight:500; transition:all 0.2s ease; font-size:0.9375rem; }
.btn-add-user:hover { background:var(--primary-hover); color:white; transform:translateY(-1px); box-shadow:var(--shadow-md); }
.empty-state { text-align:center; padding:4rem 2rem; color:var(--text-secondary); }
.empty-state-icon { font-size:3rem; color:var(--text-tertiary); margin-bottom:1rem; }
.empty-state-title { font-size:1.125rem; font-weight:600; color:var(--text-primary); margin-bottom:0.5rem; }
.empty-state-text { font-size:0.9375rem; color:var(--text-secondary); }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const fetchTags = (page = 1) => {
        const search = $('#searchInput').val() || '';
        const status = $('#statusFilter').val();
        const type = $('#typeFilter').val();
        const sortby = $('#sortBy').val();

        $.ajax({
            url: "{{ route('admin.forums.tags.table') }}",
            type: 'GET',
            data: { search, status, type, sortby, page },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                $('#tagsTableContainer').html(resp.html);
                $('#paginationContainer').html(resp.pagination);
            },
            error: function () {
                alert('Failed to load tags.');
            }
        });
    };

    fetchTags(1);

    let t = null;
    $('#searchInput').on('input', function () {
        clearTimeout(t);
        const val = $(this).val();
        $('#clearSearch').toggle(!!val);
        t = setTimeout(() => fetchTags(1), 400);
    });

    $('#clearSearch').on('click', function () {
        $('#searchInput').val('');
        $(this).hide();
        fetchTags(1);
    });

    $('#statusFilter, #typeFilter, #sortBy').on('change', function () {
        fetchTags(1);
    });

    // pagination click (pagination partial uses data-page) [file:22]
    $(document).on('click', '#paginationContainer .pagination .page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) fetchTags(page);
    });

    const refreshCurrent = () => {
        const current = $('#paginationContainer .page-item.active .page-link').data('page') || 1;
        fetchTags(current);
    };

    $(document).on('click', '.btn-tag-approve', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.forums.tags.approve', ['tag' => '__ID__']) }}".replace('__ID__', id),
            type: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success: refreshCurrent,
            error: xhr => alert(xhr.responseJSON?.message || 'Approve failed')
        });
    });

    $(document).on('click', '.btn-tag-reject', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.forums.tags.reject', ['tag' => '__ID__']) }}".replace('__ID__', id),
            type: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success: refreshCurrent,
            error: xhr => alert(xhr.responseJSON?.message || 'Reject failed')
        });
    });

    $(document).on('click', '.btn-tag-disable', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.forums.tags.update', ['tag' => '__ID__']) }}".replace('__ID__', id),
            type: 'PATCH',
            data: { status: 'inactive' },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success: refreshCurrent,
            error: xhr => alert(xhr.responseJSON?.message || 'Disable failed')
        });
    });

    $(document).on('click', '.btn-tag-enable', function () {
        const id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.forums.tags.update', ['tag' => '__ID__']) }}".replace('__ID__', id),
            type: 'PATCH',
            data: { status: 'active' },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success: refreshCurrent,
            error: xhr => alert(xhr.responseJSON?.message || 'Enable failed')
        });
    });

    $(document).on('submit', '.tag-rename-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const id = form.data('id');
        const name = form.find('input[name="name"]').val();

        $.ajax({
            url: "{{ route('admin.forums.tags.update', ['tag' => '__ID__']) }}".replace('__ID__', id),
            type: 'PATCH',
            data: { name },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success: refreshCurrent,
            error: xhr => alert(xhr.responseJSON?.message || 'Rename failed')
        });
    });

})();
</script>
@endpush
@endsection
