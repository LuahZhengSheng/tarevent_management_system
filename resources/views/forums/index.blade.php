{{-- resources/views/forums/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Community Forum - TAREvent')

@push('styles')
    @vite(['resources/css/forums/forum.css', 'resources/css/forums/forum-media-gallery.css', 'resources/css/forums/media-lightbox.css'])
@endpush

@section('content')
    <div class="forum-index-page user-site">
        {{-- Hero Section --}}
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
            {{-- Search and Filter Bar --}}
            <div class="filter-section">
                <form action="{{ route('forums.index') }}" method="GET" id="filterForm" class="filter-form">
                    <div class="filter-grid">
                        {{-- Search Input --}}
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

                        {{-- Category Filter --}}
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

                        {{-- Sort Filter --}}
                        <div class="filter-item">
                            <select class="form-select filter-select" name="sort" onchange="this.form.submit()">
                                <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Most Recent</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </div>

                        {{-- Clear Filters --}}
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

            {{-- Results Summary --}}
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
                                Search: {{ request('search') }}
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

            {{-- Posts Grid --}}
            <div class="posts-grid">
                @forelse($posts as $post)
                    <article class="post-card" onclick="window.location='{{ route('forums.posts.show', $post->slug) }}'">
                        <div class="post-card-inner">
                            {{-- Post Header --}}
                            <div class="post-header">
                                <div class="post-author">
                                    <img src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                         alt="{{ $post->user->name ?? 'User' }}"
                                         class="author-avatar">
                                    <div class="author-info">
                                        <span class="author-name">{{ $post->user->name ?? 'Anonymous' }}</span>
                                        <span class="post-time">
                                            <i class="bi bi-clock"></i>{{ $post->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="post-badges">
                                    @if($post->category)
                                        <span class="badge category-badge" style="background: {{ $post->category->color ?? 'var(--user-primary)' }}">
                                            <i class="{{ $post->category->icon ?? 'bi bi-folder' }}"></i>
                                            {{ $post->category->name }}
                                        </span>
                                    @endif
                                    @if($post->club)
                                        <span class="badge club-badge">
                                            <i class="bi bi-people-fill"></i>{{ $post->club->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Post Content --}}
                            <div class="post-content">
                                <h2 class="post-title">{{ $post->title }}</h2>
                                <p class="post-excerpt">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 180) }}</p>

                                {{-- Post Tags --}}
                                @if($post->tags && $post->tags->count() > 0)
                                    <div class="post-tags">
                                        @foreach($post->tags->take(4) as $tag)
                                            <a href="{{ route('forums.index', ['tag' => $tag->slug]) }}"
                                               class="tag-item"
                                               onclick="event.stopPropagation()">
                                                {{ $tag->name }}
                                            </a>
                                        @endforeach
                                        @if($post->tags->count() > 4)
                                            <span class="tag-item tag-more">+{{ $post->tags->count() - 4 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Post Media Preview (改成 show.blade.php 同款布局 + lightbox) --}}
                            @if($post->media_paths && count($post->media_paths) > 0)
                                @php
                                    $mediaCount = count($post->media_paths);
                                    $layoutClass = 'layout-' . min($mediaCount, 5);
                                @endphp

                                <div class="media-gallery-facebook {{ $layoutClass }}"
                                     id="mediaGrid"
                                     data-count="{{ $mediaCount }}"
                                     onclick="event.stopPropagation()">
                                    @foreach($post->media_paths as $index => $media)
                                        @php
                                            $mediaPath = is_array($media) ? ($media['path'] ?? '') : $media;
                                            $mediaType = is_array($media) ? ($media['type'] ?? 'image') : 'image';
                                            $mimeType  = is_array($media) ? ($media['mime_type'] ?? 'image/jpeg') : 'image/jpeg';

                                            $isVideo = $mediaType === 'video' || str_starts_with($mimeType, 'video/');
                                            if (empty($mediaPath)) continue;

                                            // 只在前5个显示，其他隐藏但仍然存在于 DOM 中
                                            $isVisible = $index < 5;
                                        @endphp

                                        <div class="fb-media-item media-item item-{{ $index + 1 }} {{ !$isVisible ? 'd-none' : '' }}"
                                             data-index="{{ $index }}"
                                             onclick="event.stopPropagation(); openLightbox({{ $index }});">
                                            @if($isVideo)
                                                <video class="fb-media-content" preload="metadata">
                                                    <source src="{{ Storage::url($mediaPath) }}" type="{{ $mimeType }}">
                                                </video>
                                                <div class="fb-media-badge video-badge">
                                                    <i class="bi bi-play-circle-fill"></i>
                                                </div>
                                            @else
                                                <img src="{{ Storage::url($mediaPath) }}"
                                                     alt="Media {{ $index + 1 }}"
                                                     class="fb-media-content"
                                                     loading="lazy">
                                            @endif

                                            {{-- Overlay for 5th item if more media --}}
                                            @if($index == 4 && $mediaCount > 5)
                                                <div class="fb-overlay-more">
                                                    <span class="overlay-number">+{{ $mediaCount - 5 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Post Footer --}}
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
                                        Read More <i class="bi bi-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    {{-- Empty State --}}
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

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="pagination-wrapper">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Dark Mode Toggle --}}
        <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
            <i class="bi bi-moon-stars-fill" id="darkModeIcon"></i>
        </button>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/forum.js', 'resources/js/media-lightbox.js'])
@endpush
