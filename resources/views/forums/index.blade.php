{{-- resources/views/forums/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Community Forum - TAREvent')

@push('styles')
@vite([
    'resources/css/forums/forum.css',
    'resources/css/forums/forum-media-gallery.css',
    'resources/css/forums/media-lightbox.css'
])
@endpush

@section('content')
<section class="forum-hero forum-hero--enhanced">
    <div class="container">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="hero-title">Community Forum</h1>
                <p class="hero-subtitle">Join the conversation, share ideas, and connect with fellow students</p>

                <div class="hero-quick-actions">
                    <a href="{{ route('forums.create') }}" class="btn btn-primary hero-btn">
                        <i class="bi bi-plus-circle me-2"></i> Create Post
                    </a>

                    <a href="{{ route('forums.my-posts') }}" class="btn btn-outline-light hero-btn">
                        <i class="bi bi-journal-text me-2"></i> My Posts
                    </a>
                </div>
            </div>

            <div class="hero-right">
                <div class="hero-stats-card">
                    <div class="stat">
                        <div class="stat-value">{{ $posts->total() }}</div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">{{ $popularTags->count() }}</div>
                        <div class="stat-label">Trending tags</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">{{ $categories->count() }}</div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container forum-index-container">
    <div class="forum-index-layout">
        {{-- Left sidebar --}}
        <aside class="forum-sidebar">
            {{-- sticky 包裹层（用来固定并允许内部滚动） --}}
            <div class="sidebar-sticky">
                <form id="filterForm" class="filter-card filter-card--compact" method="GET" action="{{ route('forums.index') }}">
                    <div class="filter-card-header">
                        <h3 class="filter-title">
                            <i class="bi bi-funnel me-2"></i> Search & Filter
                        </h3>

                        {{-- ✅ Reset 改成 link，不要“难看 button” --}}
                        <a href="{{ route('forums.index') }}" class="reset-link" id="resetBtn" aria-label="Reset filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span class="reset-text">Reset</span>
                        </a>
                    </div>

                    {{-- Search --}}
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="input-icon">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                name="search"
                                class="form-control search-input"
                                placeholder="Title, content, author, tag…"
                                value="{{ request('search', request('q')) }}"
                                autocomplete="off"
                            >
                        </div>
                        <div class="filter-hint">
                            Searches: title/content + author name + tags.
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select name="category_id" class="form-select filter-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string)request('category_id') === (string)$category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="filter-group">
                        <label class="filter-label">Sort</label>
                        <select name="sort" class="form-select filter-select">
                            <option value="recent" {{ request('sort','recent') === 'recent' ? 'selected' : '' }}>Most recent</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most popular</option>
                        </select>
                    </div>

                    {{-- tags[] hidden inputs (JS 会维护) --}}
                    <div id="selectedTagsInputs"></div>

                    {{-- Trending tags --}}
                    <div class="filter-group">
                        <div class="filter-label d-flex justify-content-between align-items-center">
                            <span>Trending</span>
                            <a class="small text-decoration-none" href="{{ route('forums.index') }}">Clear</a>
                        </div>

                        <div id="trendingTags">
                            {{-- ✅ 你的 partial 里 chips 请使用 .js-tag-chip 才能被 JS 识别 --}}
                            @include('forums.partials.trending_tags_ajax', [
                                'popularTags' => $popularTags,
                                'selectedTags' => request('tags', [])
                            ])
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary w-100" id="applyBtn">
                            <i class="bi bi-search me-2"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Right content --}}
        <main class="forum-feed">
            <div class="results-summary" id="resultsSummary">
                @include('forums.partials.results_summary_ajax', [
                    'posts' => $posts,
                    'categories' => $categories,
                    'selectedTags' => request('tags', []),
                    'searchText' => request('search', request('q', ''))
                ])
            </div>

            <div id="postsGrid" class="posts-grid">
                @include('forums.partials.posts_page', ['posts' => $posts])
            </div>

            <div class="feed-footer">
                <div id="infiniteSentinel"></div>

                <div id="feedLoading" class="feed-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="loading-text">Loading more posts…</div>
                </div>

                <div id="feedEnd" class="feed-end" style="display:none;">
                    <i class="bi bi-check2-circle me-2"></i> You’ve reached the end.
                </div>
            </div>

            <noscript>
                <div class="pagination-wrapper mt-4">
                    {{ $posts->withQueryString()->links() }}
                </div>
            </noscript>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.FORUM_INDEX = {
        ajaxUrl: "{{ route('forums.index') }}",
        initialPage: {{ (int)$posts->currentPage() }},
        lastPage: {{ (int)$posts->lastPage() }},
        query: @json(request()->query()),
    };
</script>

@vite([
    'resources/js/forum.js',
    'resources/js/media-lightbox.js'
])
@endpush
