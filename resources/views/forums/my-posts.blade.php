{{-- resources/views/forums/my-posts.blade.php --}}
@extends('layouts.app')

@section('title', 'My Posts - Forum')

@push('styles')
@vite('resources/css/forums/my-posts.css')
@vite('resources/css/forums/forum-media-gallery.css')
@vite('resources/css/forums/media-lightbox.css')
@endpush

@section('content')
<div class="my-posts-page">

    {{-- Hero Section with User Info --}}
    <section class="user-hero">
        <div class="user-hero-bg"></div>
        <div class="user-hero-overlay"></div>

        <div class="container">
            <div class="user-hero-content">
                {{-- Left: Avatar & Info --}}
                <div class="user-profile">
                    <div class="user-avatar-wrapper">
                        <img
                            src="{{ $user->profile_photo ? Storage::url($user->profile_photo) : asset('images/avatar/default-student-avatar.png') }}"
                            alt="{{ $user->name }}"
                            class="user-avatar-img"
                            >
                        <div class="user-status-indicator"></div>
                    </div>

                    <div class="user-info">
                        <h1 class="user-display-name">{{ $user->name }}</h1>
                        <p class="user-handle">
                            {{ '@' . ($user->student_id ?: Str::slug($user->name)) }}
                        </p>

                        @if($user->bio ?? false)
                        <p class="user-bio">{{ $user->bio }}</p>
                        @endif

                        {{-- Stats Grid --}}
                        <div class="user-stats-grid">
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['total_posts'] ?? 0 }}</div>
                                <div class="stat-label">Posts</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['total_drafts'] ?? 0 }}</div>
                                <div class="stat-label">Drafts</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['total_likes_received'] ?? 0 }}</div>
                                <div class="stat-label">Likes</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['total_saves_received'] ?? 0 }}</div>
                                <div class="stat-label">Saves</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Actions --}}
                <div class="user-actions">
                    <a href="{{ route('forums.create') }}" class="action-btn action-btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        <span>Create Post</span>
                    </a>

                    <a href="#" class="action-btn action-btn-secondary">
                        <i class="bi bi-chat-dots"></i>
                        <span>Messages</span>
                    </a>

                    <a href="{{ route('notifications.index') }}" class="action-btn action-btn-icon">
                        <i class="bi bi-bell"></i>
                        @if(($stats['unread_notifications'] ?? 0) > 0)
                        <span class="notification-dot">{{ $stats['unread_notifications'] }}</span>
                        @endif
                    </a>

                    <a href="{{ route('profile.edit') }}" class="action-btn action-btn-icon">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <div class="container main-container">
        <div class="content-layout">
            {{-- Sidebar --}}
            <aside class="content-sidebar">
                {{-- Quick Search --}}
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="bi bi-search"></i>
                        Search & Filter
                    </h3>
                    <form id="filterForm" class="filter-form">
                        <input type="hidden" name="tab" id="currentTab" value="{{ $activeTab }}">

                        <div class="filter-group">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input
                                    type="text"
                                    name="q"
                                    id="searchInput"
                                    class="filter-input search-input"
                                    placeholder="Search your posts..."
                                    value="{{ $search['q'] ?? '' }}"
                                    >
                                <button type="button" id="clearSearch" class="clear-btn" style="display: none;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="bi bi-flag"></i>
                                Status
                            </label>
                            <div class="select-wrapper">
                                <select name="status" id="statusFilter" class="filter-select">
                                    <option value="">All</option>
                                    <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>
                                        Published
                                    </option>
                                    <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>
                                        Draft
                                    </option>
                                </select>
                                <i class="bi bi-chevron-down select-icon"></i>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="bi bi-eye"></i>
                                Visibility
                            </label>
                            <div class="select-wrapper">
                                <select name="visibility" id="visibilityFilter" class="filter-select">
                                    <option value="">All</option>
                                    <option value="public" {{ ($filters['visibility'] ?? '') === 'public' ? 'selected' : '' }}>
                                        Public
                                    </option>
                                    <option value="club_only" {{ ($filters['visibility'] ?? '') === 'club_only' ? 'selected' : '' }}>
                                        Club only
                                    </option>
                                </select>
                                <i class="bi bi-chevron-down select-icon"></i>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="bi bi-sort-down"></i>
                                Sort by
                            </label>
                            <div class="select-wrapper">
                                <select name="sort" id="sortFilter" class="filter-select">
                                    <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                                    <option value="oldest" {{ ($sort ?? 'latest') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                                    <option value="mostliked" {{ ($sort ?? 'latest') === 'mostliked' ? 'selected' : '' }}>Most liked</option>
                                    <option value="mostcommented" {{ ($sort ?? 'latest') === 'mostcommented' ? 'selected' : '' }}>Most commented</option>
                                </select>
                                <i class="bi bi-chevron-down select-icon"></i>
                            </div>
                        </div>

                        <button type="button" id="resetFilters" class="filter-reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset Filters
                        </button>
                    </form>
                </div>

                {{-- Quick Links --}}
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="bi bi-link-45deg"></i>
                        Quick Links
                    </h3>
                    <div class="quick-links">
                        <a href="{{ route('forums.index') }}" class="quick-link">
                            <i class="bi bi-grid"></i>
                            <span>All Posts</span>
                        </a>
                        <a href="{{ route('forums.create') }}" class="quick-link">
                            <i class="bi bi-pencil-square"></i>
                            <span>Create New</span>
                        </a>
                        <a href="{{-- route('profile.show') --}}" class="quick-link">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <main class="content-main">
                {{-- Tabs Navigation --}}
                <nav class="tabs-nav">
                    @foreach($tabs as $tabKey => $tab)
                    <button
                        class="tab-item {{ $activeTab === $tabKey ? 'active' : '' }}"
                        data-tab="{{ $tabKey }}"
                        >
                        <span class="tab-label">{{ $tab['label'] }}</span>
                        @if(isset($tab['count']))
                        <span class="tab-count">{{ $tab['count'] }}</span>
                        @endif
                    </button>
                    @endforeach
                </nav>

                {{-- Posts Content Container --}}
                <div class="posts-container" id="postsContainer">
                    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Loading...</p>
                        </div>
                    </div>

                    {{-- 首次加载当前 tab 内容 --}}
                    <div class="tab-content active" id="tabContent">
                        @include('forums.partials.my-posts-tab', ['activeTab'=>$activeTab,'tabs'=>$tabs])
                    </div>

                </div>
            </main>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmText">Are you sure you want to delete this post?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="myToastHost" class="toast-container"></div>

@endsection

@push('scripts')
<script>
  window.AJAX_CONFIG = {!! json_encode([
    'baseUrl' => route('forums.my-posts'),
    'csrfToken' => csrf_token(),
    'quickDeleteUrl' => route('forums.my-posts.quick-delete'),
  ]) !!};

  window.currentState = {!! json_encode([
    'tab' => $activeTab,
    'search' => is_array($search) ? ($search['q'] ?? '') : ($search ?? ''),
    'status' => $filters['status'] ?? '',
    'visibility' => $filters['visibility'] ?? '',
    'sort' => $sort ?? 'latest',
  ]) !!};
</script>


@vite('resources/js/my-posts.js')
@vite('resources/js/media-lightbox.js')
@endpush
