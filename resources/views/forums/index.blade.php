{{-- resources/views/forums/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Community Forum - TAREvent')

@push('styles')
<style>
/* ===================================
   Forum Index Styles
   使用 theme.css 的颜色变量
   =================================== */

.forum-index-page {
    min-height: 100vh;
    background: var(--bg-secondary);
}

/* Hero Section */
.forum-hero {
    background: linear-gradient(135deg, var(--forum-gradient-start), var(--forum-gradient-end));
    padding: 5rem 0 4rem;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.forum-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    max-width: 600px;
}

.hero-actions .btn {
    padding: 0.875rem 2rem;
    font-weight: 600;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.hero-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

/* Forum Container */
.forum-container {
    max-width: 1200px;
    padding: 0 1.5rem 4rem;
}

/* Filter Section */
.filter-section {
    background: var(--bg-primary);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.filter-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: center;
}

.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    font-size: 1.125rem;
    pointer-events: none;
    transition: color 0.3s ease;
}

.search-input {
    padding-left: 3rem;
    border-radius: 0.75rem;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.search-input:focus {
    border-color: var(--user-primary);
    box-shadow: 0 0 0 3px var(--user-primary-light);
    background: var(--bg-primary);
}

[data-theme="dark"] .search-input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.search-input:focus ~ .search-icon {
    color: var(--user-primary);
}

.filter-select {
    border-radius: 0.75rem;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
    padding: 0.625rem 1rem;
}

.filter-select:focus {
    border-color: var(--user-primary);
    box-shadow: 0 0 0 3px var(--user-primary-light);
}

[data-theme="dark"] .filter-select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.btn-clear {
    white-space: nowrap;
    border-radius: 0.75rem;
}

/* Results Summary */
.results-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.results-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.125rem;
    color: var(--text-secondary);
}

.results-info i {
    font-size: 1.5rem;
    color: var(--user-primary);
}

.results-count {
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--text-primary);
}

.active-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--user-primary-light);
    color: var(--user-primary);
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
}

[data-theme="dark"] .filter-tag {
    background: rgba(59, 130, 246, 0.2);
}

.filter-tag-close {
    color: var(--user-primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.filter-tag-close:hover {
    transform: scale(1.2);
}

/* Posts Grid */
.posts-grid {
    display: grid;
    gap: 1.5rem;
}

/* Post Card */
.post-card {
    background: var(--bg-primary);
    border-radius: 1.25rem;
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    position: relative;
}

.post-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--user-primary), var(--user-secondary));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.post-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: var(--user-primary);
}

.post-card:hover::before {
    transform: scaleX(1);
}

.post-card-inner {
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Post Header */
.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.post-author {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
    transition: border-color 0.3s ease;
}

.post-card:hover .author-avatar {
    border-color: var(--user-primary);
}

.author-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.author-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.post-time {
    font-size: 0.8125rem;
    color: var(--text-tertiary);
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.post-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.category-badge {
    background: linear-gradient(135deg, var(--user-primary), var(--user-primary-hover));
    color: white;
    font-size: 0.75rem;
    padding: 0.375rem 0.875rem;
    border-radius: 2rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.club-badge {
    background: var(--user-secondary);
    color: white;
    font-size: 0.75rem;
    padding: 0.375rem 0.875rem;
    border-radius: 2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

/* Post Content */
.post-content {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.post-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
}

.post-card:hover .post-title {
    color: var(--user-primary);
}

.post-excerpt {
    color: var(--text-secondary);
    line-height: 1.7;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Post Tags */
.post-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag-item {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    padding: 0.375rem 0.875rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.post-card:hover .tag-item {
    background: var(--user-primary-light);
    color: var(--user-primary);
}

[data-theme="dark"] .post-card:hover .tag-item {
    background: rgba(59, 130, 246, 0.2);
}

.tag-more {
    background: var(--user-primary-light);
    color: var(--user-primary);
    font-weight: 600;
}

/* Post Media Preview */
.post-media-preview {
    position: relative;
    border-radius: 0.875rem;
    overflow: hidden;
    height: 200px;
}

.media-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.post-card:hover .media-thumbnail {
    transform: scale(1.08);
}

.video-thumbnail {
    position: relative;
    width: 100%;
    height: 100%;
}

.video-play-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 4rem;
    color: white;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    transition: transform 0.3s ease;
}

.post-card:hover .video-play-icon {
    transform: translate(-50%, -50%) scale(1.1);
}

.media-count {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 2rem;
    font-size: 0.8125rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

/* Post Footer */
.post-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.post-stats {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.stat-item i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.post-card:hover .stat-item {
    color: var(--text-primary);
}

.post-card:hover .stat-item i {
    transform: scale(1.1);
}

.post-action .read-more {
    color: var(--user-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.9375rem;
}

.post-card:hover .read-more {
    gap: 0.875rem;
}

.post-card:hover .read-more i {
    transform: translateX(4px);
}

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 5rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: var(--text-tertiary);
    opacity: 0.5;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.875rem;
}

.empty-text {
    color: var(--text-secondary);
    font-size: 1.125rem;
    max-width: 500px;
    margin: 0 auto 2rem;
    line-height: 1.7;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
}

.pagination {
    gap: 0.5rem;
}

.page-link {
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.625rem 1rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
}

.page-link:hover {
    background: var(--user-primary);
    color: white;
    border-color: var(--user-primary);
    transform: translateY(-2px);
}

.page-item.active .page-link {
    background: var(--user-primary);
    border-color: var(--user-primary);
}

/* Responsive */
@media (max-width: 992px) {
    .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-search {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .forum-hero {
        padding: 3rem 0 2rem;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .hero-content {
        flex-direction: column;
        text-align: center;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .post-title {
        font-size: 1.25rem;
    }
    
    .post-media-preview {
        height: 150px;
    }
    
    .post-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="forum-index-page user-site">
    <!-- Hero Section -->
    <section class="forum-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">Community Forum</h1>
                    <p class="hero-subtitle">Join the conversation, share ideas, and connect with fellow students</p>
                </div>
                <div class="hero-actions">
                    <a href="{{ route('forums.posts.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Create Post
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container forum-container">
        <!-- Search and Filter Bar -->
        <div class="filter-section">
            <form action="{{ route('forums.index') }}" method="GET" id="filterForm" class="filter-form">
                <div class="filter-grid">
                    <!-- Search Input -->
                    <div class="filter-item filter-search">
                        <div class="search-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   class="form-control search-input" 
                                   name="search" 
                                   placeholder="Search discussions..." 
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-item">
                        <select class="form-select filter-select" name="category_id" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Filter -->
                    <div class="filter-item">
                        <select class="form-select filter-select" name="sort" onchange="this.form.submit()">
                            <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>
                                Most Recent
                            </option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>
                                Most Popular
                            </option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    @if(request()->hasAny(['search', 'category_id', 'sort', 'tag']))
                    <div class="filter-item">
                        <a href="{{ route('forums.index') }}" class="btn btn-outline-secondary btn-clear">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="results-summary">
            <div class="results-info">
                <i class="bi bi-chat-dots-fill"></i>
                <span class="results-count">{{ $posts->total() }}</span>
                <span class="results-text">{{ Str::plural('Discussion', $posts->total()) }}</span>
            </div>
            
            @if(request()->hasAny(['search', 'category_id', 'tag']))
            <div class="active-filters">
                @if(request()->has('search'))
                <span class="filter-tag">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('forums.index', request()->except('search')) }}" class="filter-tag-close">
                        <i class="bi bi-x"></i>
                    </a>
                </span>
                @endif
                
                @if(request()->has('category_id'))
                @php
                    $selectedCategory = $categories->firstWhere('id', request('category_id'));
                @endphp
                @if($selectedCategory)
                <span class="filter-tag">
                    {{ $selectedCategory->name }}
                    <a href="{{ route('forums.index', request()->except('category_id')) }}" class="filter-tag-close">
                        <i class="bi bi-x"></i>
                    </a>
                </span>
                @endif
                @endif

                @if(request()->has('tag'))
                <span class="filter-tag">
                    #{{ request('tag') }}
                    <a href="{{ route('forums.index', request()->except('tag')) }}" class="filter-tag-close">
                        <i class="bi bi-x"></i>
                    </a>
                </span>
                @endif
            </div>
            @endif
        </div>

        <!-- Posts Grid -->
        <div class="posts-grid">
            @forelse($posts as $post)
            <article class="post-card" onclick="window.location='{{ route('forums.posts.show', $post->slug) }}'">
                <div class="post-card-inner">
                    <!-- Post Header -->
                    <div class="post-header">
                        <div class="post-author">
                            <img src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $post->user->name ?? 'User' }}" 
                                 class="author-avatar">
                            <div class="author-info">
                                <span class="author-name">{{ $post->user->name ?? 'Anonymous' }}</span>
                                <span class="post-time">
                                    <i class="bi bi-clock"></i>
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="post-badges">
                            @if($post->category)
                            <span class="badge category-badge" style="background: {{ $post->category->color ?? 'var(--user-primary)' }}">
                                <i class="{{ $post->category->icon ?? 'bi-folder' }}"></i>
                                {{ $post->category->name }}
                            </span>
                            @endif
                            
                            @if($post->club)
                            <span class="badge club-badge">
                                <i class="bi bi-people-fill"></i>
                                {{ $post->club->name }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div class="post-content">
                        <h2 class="post-title">{{ $post->title }}</h2>
                        <p class="post-excerpt">
                            {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 180) }}
                        </p>

                        <!-- Post Tags -->
                        @if($post->tags && $post->tags->count() > 0)
                        <div class="post-tags">
                            @foreach($post->tags->take(4) as $tag)
                            <a href="{{ route('forums.index', ['tag' => $tag->slug]) }}" 
                               class="tag-item" 
                               onclick="event.stopPropagation()">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                            @if($post->tags->count() > 4)
                            <span class="tag-item tag-more">+{{ $post->tags->count() - 4 }}</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Post Media Preview -->
                    @if($post->hasMedia())
                    <div class="post-media-preview">
                        @php $firstMedia = $post->media->first(); @endphp
                        
                        @if($firstMedia->type === 'image')
                        <img src="{{ $firstMedia->url }}" 
                             alt="{{ $post->title }}" 
                             class="media-thumbnail">
                        @else
                        <div class="video-thumbnail">
                            <video src="{{ $firstMedia->url }}" class="media-thumbnail"></video>
                            <div class="video-play-icon">
                                <i class="bi bi-play-circle-fill"></i>
                            </div>
                        </div>
                        @endif

                        @if($post->media->count() > 1)
                        <div class="media-count">
                            <i class="bi bi-images"></i>
                            {{ $post->media->count() }}
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Post Footer -->
                    <div class="post-footer">
                        <div class="post-stats">
                            <span class="stat-item">
                                <i class="bi bi-eye-fill"></i>
                                <span>{{ number_format($post->views_count) }}</span>
                            </span>
                            <span class="stat-item">
                                <i class="bi bi-heart-fill"></i>
                                <span>{{ number_format($post->likes_count) }}</span>
                            </span>
                            <span class="stat-item">
                                <i class="bi bi-chat-fill"></i>
                                <span>{{ number_format($post->comments_count) }}</span>
                            </span>
                            <span class="stat-item">
                                <i class="bi bi-clock-fill"></i>
                                <span>{{ $post->read_time }}</span>
                            </span>
                        </div>
                        
                        <div class="post-action">
                            <span class="read-more">
                                Read More
                                <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-chat-left-text"></i>
                </div>
                <h3 class="empty-title">No Discussions Found</h3>
                <p class="empty-text">
                    @if(request()->hasAny(['search', 'category_id', 'tag']))
                        Try adjusting your filters or search terms to find what you're looking for.
                    @else
                        Be the first to start a meaningful discussion in our community!
                    @endif
                </p>
                <a href="{{ route('forums.posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create First Post
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="pagination-wrapper">
            {{ $posts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Dark Mode Toggle -->
<button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
    <i class="bi bi-moon-stars-fill" id="darkModeIcon"></i>
</button>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initForumFeatures();
});

function initForumFeatures() {
    initDarkMode();
    initSearchDebounce();
    initScrollToTop();
    initPostCardEffects();
    initFilterAnimations();
}

// Dark Mode
function initDarkMode() {
    const $toggle = document.getElementById('darkModeToggle');
    const $icon = document.getElementById('darkModeIcon');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateDarkModeIcon(currentTheme);
    
    $toggle.addEventListener('click', function() {
        const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateDarkModeIcon(newTheme);
    });
}

function updateDarkModeIcon(theme) {
    const $icon = document.getElementById('darkModeIcon');
    if (theme === 'dark') {
        $icon.classList.remove('bi-moon-stars-fill');
        $icon.classList.add('bi-sun-fill');
    } else {
        $icon.classList.remove('bi-sun-fill');
        $icon.classList.add('bi-moon-stars-fill');
    }
}

// Search Input Debounce
function initSearchDebounce() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    let debounceTimer;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        
        const searchIcon = document.querySelector('.search-icon');
        searchIcon.classList.add('searching');
        
        debounceTimer = setTimeout(() => {
            searchIcon.classList.remove('searching');
        }, 500);
    });
}

// Post Card Effects
function initPostCardEffects() {
    const postCards = document.querySelectorAll('.post-card');
    
    postCards.forEach(card => {
        // Prevent card click when clicking on links inside
        card.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button')) {
                e.stopPropagation();
                return;
            }
        });
    });
}

// Filter Animations
function initFilterAnimations() {
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.classList.add('loading');
            }
        });
    });
}

// Scroll to Top Button
function initScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    
    // Add styles
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--user-primary), var(--user-primary-hover));
        color: white;
        border: none;
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    `;
    
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.opacity = '1';
            scrollBtn.style.visibility = 'visible';
        } else {
            scrollBtn.style.opacity = '0';
            scrollBtn.style.visibility = 'hidden';
        }
    });

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    scrollBtn.addEventListener('mouseenter', () => {
        scrollBtn.style.transform = 'translateY(-4px) scale(1.05)';
        scrollBtn.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.3)';
    });

    scrollBtn.addEventListener('mouseleave', () => {
        scrollBtn.style.transform = '';
        scrollBtn.style.boxShadow = 'var(--shadow-xl)';
    });
}
</script>
@endpush
