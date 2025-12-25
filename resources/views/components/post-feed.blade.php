@props([
    'apiUrl',
    'initialPosts' => null,
    'showFilters' => true,
])

<div class="forum-index-layout">
    @if($showFilters)
        <aside class="forum-sidebar">
            <div class="sidebar-sticky">
                <form id="filterForm" class="filter-card" method="GET" action="{{ $apiUrl }}">
                    {{-- 现有的 Search & Filter DOM 直接复用 --}}
                    {{-- 保留：resultsSummary/trendingTags/postsGrid 的容器 id --}}
                    <div class="filter-card-header">
                        <h3 class="filter-title"><i class="bi bi-funnel me-2"></i> Search & Filter</h3>
                        <a href="#" class="reset-link" id="resetBtn">
                            <i class="bi bi-arrow-counterclockwise"></i><span class="reset-text">Reset</span>
                        </a>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="input-icon">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="search" class="form-control search-input" placeholder="Title, content, author, tag…" autocomplete="off">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select name="category_id" class="form-select filter-select">
                            <option value="">All categories</option>
                            {{-- club 页面你可以在 view 传 categories --}}
                            @foreach(($categories ?? collect()) as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Sort</label>
                        <select name="sort" class="form-select filter-select">
                            <option value="recent">Most recent</option>
                            <option value="popular">Most popular</option>
                        </select>
                    </div>

                    <div id="selectedTagsInputs"></div>

                    <div class="filter-group">
                        <div class="filter-label d-flex justify-content-between align-items-center">
                            <span>Trending</span>
                            <a class="small text-decoration-none" href="#" id="clearTrending">Clear</a>
                        </div>
                        <div id="trendingTags">
                            {!! $trendingHtml ?? '' !!}
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
    @endif

    <main class="forum-feed">
        <div class="results-summary" id="resultsSummary">
            {!! $summaryHtml ?? '' !!}
        </div>

        <div id="postsGrid" class="posts-grid">
            @if($initialPosts)
                @include('forums.partials.posts_page', ['posts' => $initialPosts])
            @endif
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
    </main>
</div>
