{{-- resources/views/forums/my-posts.blade.php - Enhanced Version --}}
@extends('layouts.app')

@section('title', 'My Posts')

@push('styles')
@vite([
    'resources/css/forums/my-posts.css',
    'resources/css/forums/forum-media-gallery.css',
    'resources/css/forums/media-lightbox.css'
])
@endpush

@section('content')
@php
    $tab = request('tab', 'posts');

    // Stats
    $totalPosts = $stats['total'] ?? ($postsTotal ?? (is_countable($posts ?? null) ? count($posts) : 0));
    $publishedPosts = $stats['published'] ?? ($publishedTotal ?? null);
    $draftPosts = $stats['draft'] ?? ($draftTotal ?? null);

    // Profile
    $displayName = $profile['name'] ?? (auth()->user()->name ?? 'User');
    $displayHandle = $profile['handle'] ?? (auth()->user()->username ?? ('@' . (auth()->user()->id ?? 'me')));
    $bio = $profile['bio'] ?? 'Manage your posts, drafts and activity in one place.';
    $coverUrl = $profile['cover_url'] ?? null;
    $avatarUrl = $profile['avatar_url'] ?? null;

    // Additional stats
    $followers = $profile['followers'] ?? null;
    $following = $profile['following'] ?? null;
    $likesTotal = $profile['likes_total'] ?? null;
    $commentsTotal = $profile['comments_total'] ?? null;
@endphp

<div class="mp-page">
    {{-- ===== Hero Cover Section ===== --}}
    <section class="mp-cover">
        @if($coverUrl)
            <div class="mp-cover__bg" style="background-image:url('{{ $coverUrl }}');" role="img" aria-label="Cover image"></div>
        @else
            <div class="mp-cover__bg" role="img" aria-label="Default cover"></div>
        @endif
        <div class="mp-cover__overlay" aria-hidden="true"></div>

        <div class="mp-container">
            <div class="mp-cover__inner">
                {{-- Left: Avatar + Identity --}}
                <div class="mp-cover__left">
                    <div class="mp-avatar" role="img" aria-label="Profile avatar">
                        @if($avatarUrl)
                            <img class="mp-avatar__img" src="{{ $avatarUrl }}" alt="{{ $displayName }}'s avatar">
                        @else
                            <div class="mp-avatar__fallback" aria-label="Avatar placeholder">
                                {{ strtoupper(mb_substr($displayName, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="mp-identity">
                        <h1 class="mp-identity__name">{{ $displayName }}</h1>
                        <div class="mp-identity__handle">{{ $displayHandle }}</div>
                        <p class="mp-identity__bio">{{ $bio }}</p>

                        <div class="mp-kpis" role="list">
                            <div class="mp-kpi" role="listitem">
                                <span class="mp-kpi__label">Posts</span>
                                <span class="mp-kpi__value">{{ number_format($totalPosts) }}</span>
                            </div>
                            @if(!is_null($likesTotal))
                                <div class="mp-kpi" role="listitem">
                                    <span class="mp-kpi__label">Likes</span>
                                    <span class="mp-kpi__value">{{ number_format($likesTotal) }}</span>
                                </div>
                            @endif
                            @if(!is_null($commentsTotal))
                                <div class="mp-kpi" role="listitem">
                                    <span class="mp-kpi__label">Comments</span>
                                    <span class="mp-kpi__value">{{ number_format($commentsTotal) }}</span>
                                </div>
                            @endif
                            @if(!is_null($followers))
                                <div class="mp-kpi" role="listitem">
                                    <span class="mp-kpi__label">Followers</span>
                                    <span class="mp-kpi__value">{{ number_format($followers) }}</span>
                                </div>
                            @endif
                            @if(!is_null($following))
                                <div class="mp-kpi" role="listitem">
                                    <span class="mp-kpi__label">Following</span>
                                    <span class="mp-kpi__value">{{ number_format($following) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right: Actions + Mini Stats --}}
                <div class="mp-cover__right">
                    <nav class="mp-actions" aria-label="Quick actions">
                        <a href="{{ route('forums.create') }}" class="btn btn-light mp-btn-pill">
                            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                            <span>Create Post</span>
                        </a>
                        <a href="{{ route('forums.index') }}" class="btn btn-outline-light mp-btn-pill mp-btn-glass">
                            <i class="bi bi-grid me-1" aria-hidden="true"></i>
                            <span>Browse Forum</span>
                        </a>
                    </nav>

                    <div class="mp-mini" role="complementary" aria-label="Post statistics">
                        <div class="mp-mini__row">
                            <span class="mp-mini__label">Published</span>
                            <span class="mp-mini__value">{{ $publishedPosts ?? '—' }}</span>
                        </div>
                        <div class="mp-mini__row">
                            <span class="mp-mini__label">Drafts</span>
                            <span class="mp-mini__value">{{ $draftPosts ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Main Content ===== --}}
    <main class="mp-main">
        <div class="mp-container">
            <div class="mp-layout">
                {{-- ===== Left Sidebar: Filters ===== --}}
                <aside class="mp-left" role="complementary" aria-label="Filters and navigation">
                    <div class="mp-card mp-card--soft">
                        <div class="mp-card__title">
                            <i class="bi bi-sliders" aria-hidden="true"></i>
                            <span>Quick Filters</span>
                        </div>

                        <form class="mp-quickform" role="search" aria-label="Filter posts">
                            <div class="mp-field">
                                <label for="mpSearchInput" class="mp-label">Search</label>
                                <div class="mp-input mp-input--icon mp-input--mini">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                    <input id="mpSearchInput" 
                                           type="text" 
                                           class="form-control" 
                                           placeholder="Search title / content..."
                                           aria-label="Search posts">
                                </div>
                            </div>

                            <div class="mp-field">
                                <label for="mpSortSelect" class="mp-label">Sort By</label>
                                <select id="mpSortSelect" class="form-select" aria-label="Sort posts">
                                    <option value="latest" selected>Latest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="most_viewed">Most Viewed</option>
                                    <option value="most_liked">Most Liked</option>
                                </select>
                            </div>

                            <div class="mp-actions-col">
                                <button class="btn btn-primary mp-btn-pill" 
                                        id="mpApplyClientFilter" 
                                        type="button"
                                        aria-label="Apply filters">
                                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                                    <span>Apply Filters</span>
                                </button>
                                <button class="btn btn-outline-secondary mp-btn-pill" 
                                        id="mpResetClientFilter" 
                                        type="button"
                                        aria-label="Reset filters">
                                    <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                                    <span>Reset</span>
                                </button>
                            </div>
                        </form>

                        <nav class="mp-links" aria-label="Quick navigation">
                            <a class="mp-link" href="{{ route('forums.create') }}">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                <span>Create new post</span>
                            </a>
                            <a class="mp-link" href="{{ route('forums.index') }}">
                                <i class="bi bi-grid" aria-hidden="true"></i>
                                <span>Browse forum</span>
                            </a>
                        </nav>
                    </div>
                </aside>

                {{-- ===== Right Content: Posts/Drafts/Liked/Comments ===== --}}
                <section class="mp-content" role="main" aria-label="Posts content">
                    {{-- Tabs Navigation --}}
                    <nav class="mp-tabs" role="tablist" aria-label="Content tabs">
                        <a class="mp-tab {{ $tab === 'posts' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'posts']) }}"
                           role="tab"
                           aria-selected="{{ $tab === 'posts' ? 'true' : 'false' }}"
                           aria-label="My posts">
                            <i class="bi bi-file-text" aria-hidden="true"></i>
                            <span>Posts</span>
                        </a>
                        <a class="mp-tab {{ $tab === 'drafts' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'drafts']) }}"
                           role="tab"
                           aria-selected="{{ $tab === 'drafts' ? 'true' : 'false' }}"
                           aria-label="Draft posts">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            <span>Drafts</span>
                        </a>
                        <a class="mp-tab {{ $tab === 'liked' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'liked']) }}"
                           role="tab"
                           aria-selected="{{ $tab === 'liked' ? 'true' : 'false' }}"
                           aria-label="Liked posts">
                            <i class="bi bi-heart" aria-hidden="true"></i>
                            <span>Liked</span>
                        </a>
                        <a class="mp-tab {{ $tab === 'comments' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'comments']) }}"
                           role="tab"
                           aria-selected="{{ $tab === 'comments' ? 'true' : 'false' }}"
                           aria-label="My comments">
                            <i class="bi bi-chat-dots" aria-hidden="true"></i>
                            <span>Comments</span>
                        </a>
                    </nav>

                    {{-- Content Header --}}
                    <header class="mp-strip">
                        <div class="mp-strip__left">
                            <h2 class="mp-strip__title">
                                @if($tab === 'drafts')
                                    Draft Posts
                                @elseif($tab === 'liked')
                                    Liked Posts
                                @elseif($tab === 'comments')
                                    My Comments
                                @else
                                    My Posts
                                @endif
                            </h2>
                            <p class="mp-strip__sub">
                                @if($tab === 'comments')
                                    All comments you've made on posts
                                @else
                                    One row per post (desktop-friendly list layout)
                                @endif
                            </p>
                        </div>
                    </header>

                    {{-- Posts / Drafts / Liked Content --}}
                    @if(in_array($tab, ['posts', 'drafts', 'liked']))
                        <div class="mp-list-posts" id="mpPostGrid" role="list" aria-label="Post list">
                            @forelse(($posts ?? []) as $post)
                                @php
                                    $mediaPaths = $post->media_paths ?? $post->mediapaths ?? [];
                                    $mediaCount = is_array($mediaPaths) ? count($mediaPaths) : 0;
                                    
                                    $isDraft = ($post->status ?? null) === 'draft';
                                    $isPinned = (bool)($post->is_pinned ?? false);
                                    $isClubOnly = ($post->visibility ?? null) === 'club_only';
                                    
                                    $excerpt = $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 180);
                                    $views = $post->views_count ?? 0;
                                    $likes = $post->likes_count ?? 0;
                                    $comments = $post->comments_count ?? 0;
                                    $createdAt = $post->created_at ?? null;
                                @endphp

                                <article class="mp-rowpost"
                                         role="listitem"
                                         data-post-title="{{ strtolower($post->title ?? '') }}"
                                         data-post-excerpt="{{ strtolower($excerpt ?? '') }}"
                                         data-post-views="{{ (int)$views }}"
                                         data-post-likes="{{ (int)$likes }}"
                                         data-post-created="{{ $createdAt ? $createdAt->timestamp : 0 }}"
                                         aria-label="Post: {{ $post->title }}">

                                    <div class="mp-rowpost__main">
                                        {{-- Media Gallery (if exists) --}}
                                        @if($mediaCount > 0)
                                            <div class="mp-rowpost__media-top">
                                                <div class="media-gallery-facebook layout-{{ min($mediaCount, 5) }}"
                                                     role="img"
                                                     aria-label="Post media gallery with {{ $mediaCount }} {{ Str::plural('item', $mediaCount) }}">
                                                    @foreach($post->media_paths as $index => $media)
                                                        @php
                                                            $mediaPath = is_array($media) ? ($media['path'] ?? '') : $media;
                                                            $mediaType = is_array($media) ? ($media['type'] ?? 'image') : 'image';
                                                            $mimeType = is_array($media) ? ($media['mime_type'] ?? 'image/jpeg') : 'image/jpeg';
                                                            $isVideo = $mediaType === 'video' || str_starts_with($mimeType, 'video/');
                                                            
                                                            if (empty($mediaPath)) continue;
                                                            $isVisible = $index < 5;
                                                        @endphp

                                                        <div class="fb-media-item media-item item-{{ $index + 1 }} {{ !$isVisible ? 'd-none' : '' }}"
                                                             data-index="{{ $index }}"
                                                             data-lightbox="true"
                                                             data-group="post-{{ $post->id }}"
                                                             data-type="{{ $isVideo ? 'video' : 'image' }}"
                                                             data-src="{{ Storage::url($mediaPath) }}"
                                                             tabindex="0"
                                                             role="button"
                                                             aria-label="View {{ $isVideo ? 'video' : 'image' }} {{ $index + 1 }}">
                                                            
                                                            @if($isVideo)
                                                                <video class="fb-media-content" preload="metadata" aria-label="Video preview">
                                                                    <source src="{{ Storage::url($mediaPath) }}" type="{{ $mimeType }}">
                                                                </video>
                                                                <div class="fb-media-badge video-badge" aria-label="Video">
                                                                    <i class="bi bi-play-circle-fill" aria-hidden="true"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ Storage::url($mediaPath) }}"
                                                                     alt="Media {{ $index + 1 }}"
                                                                     class="fb-media-content"
                                                                     loading="lazy">
                                                            @endif

                                                            @if($index == 4 && $mediaCount > 5)
                                                                <div class="fb-overlay-more" aria-label="{{ $mediaCount - 5 }} more items">
                                                                    <span class="overlay-number">+{{ $mediaCount - 5 }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Post Header --}}
                                        <div class="mp-rowpost__top">
                                            <div class="mp-rowpost__titlewrap">
                                                <h3 class="mp-rowpost__title">
                                                    <a href="{{ route('forums.posts.show', $post->slug) }}">
                                                        {{ $post->title }}
                                                    </a>
                                                </h3>

                                                <div class="mp-rowpost__badges" role="list">
                                                    @if($isPinned)
                                                        <span class="badge text-bg-warning" role="listitem">
                                                            <i class="bi bi-pin-angle-fill me-1" aria-hidden="true"></i>Pinned
                                                        </span>
                                                    @endif
                                                    @if($isDraft)
                                                        <span class="badge text-bg-secondary" role="listitem">
                                                            <i class="bi bi-pencil-fill me-1" aria-hidden="true"></i>Draft
                                                        </span>
                                                    @endif
                                                    @if($isClubOnly)
                                                        <span class="badge text-bg-dark" role="listitem">
                                                            <i class="bi bi-lock-fill me-1" aria-hidden="true"></i>Club Only
                                                        </span>
                                                    @endif
                                                    @if(isset($post->category) && $post->category)
                                                        <span class="badge text-bg-primary" role="listitem">
                                                            {{ $post->category->name ?? 'Category' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mp-rowpost__meta" role="list">
                                                <span class="mp-stat" role="listitem">
                                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                                    <time datetime="{{ $createdAt ? $createdAt->toIso8601String() : '' }}">
                                                        {{ $createdAt ? $createdAt->diffForHumans() : '—' }}
                                                    </time>
                                                </span>
                                                <span class="mp-stat" role="listitem">
                                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                                    <span>{{ number_format((int)$views) }}</span>
                                                </span>
                                                <span class="mp-stat" role="listitem">
                                                    <i class="bi bi-heart" aria-hidden="true"></i>
                                                    <span>{{ number_format((int)$likes) }}</span>
                                                </span>
                                                <span class="mp-stat" role="listitem">
                                                    <i class="bi bi-chat" aria-hidden="true"></i>
                                                    <span>{{ number_format((int)$comments) }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Excerpt --}}
                                        <p class="mp-rowpost__excerpt">{{ $excerpt }}</p>

                                        {{-- Tags --}}
                                        @if(!empty($post->tags) && $post->tags->count() > 0)
                                            <div class="mp-tags mp-rowpost__tags" role="list">
                                                @foreach($post->tags->take(6) as $tag)
                                                    <span class="mp-tag" role="listitem">#{{ $tag->name }}</span>
                                                @endforeach
                                                @if($post->tags->count() > 6)
                                                    <span class="mp-tag mp-tag--more" role="listitem">
                                                        +{{ $post->tags->count() - 6 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Actions --}}
                                        <div class="mp-rowpost__actions">
                                            <a href="{{ route('forums.posts.show', $post->slug) }}" 
                                               class="btn btn-outline-primary mp-btn-pill"
                                               aria-label="View post">
                                                <i class="bi bi-eye me-1" aria-hidden="true"></i>
                                                <span>View</span>
                                            </a>
                                            <a href="{{ route('forums.posts.edit', $post->slug) }}" 
                                               class="btn btn-outline-secondary mp-btn-pill"
                                               aria-label="Edit post">
                                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>
                                                <span>Edit</span>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-danger mp-btn-pill js-delete-post"
                                                    data-delete-url="{{ route('forums.posts.destroy', $post->slug) }}"
                                                    data-post-title="{{ $post->title }}"
                                                    aria-label="Delete post">
                                                <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="mp-empty" role="status">
                                    <div class="mp-empty__icon">
                                        <i class="bi bi-inboxes" aria-hidden="true"></i>
                                    </div>
                                    <h3 class="mp-empty__title">No posts found</h3>
                                    <p class="mp-empty__text">
                                        Create a new post, or adjust your filters to see results.
                                    </p>
                                    <a href="{{ route('forums.posts.create') }}" 
                                       class="btn btn-primary mp-btn-pill"
                                       aria-label="Create your first post">
                                        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                                        <span>Create Post</span>
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        @if(method_exists($posts ?? null, 'links'))
                            <nav class="mp-pagination" aria-label="Posts pagination">
                                {{ $posts->links() }}
                            </nav>
                        @endif
                    @endif

                    {{-- Comments Tab Content --}}
                    @if($tab === 'comments')
                        <div class="mp-list" role="list" aria-label="Comments list">
                            @forelse(($comments ?? []) as $comment)
                                <article class="mp-item" role="listitem">
                                    <header class="mp-item__head">
                                        <a class="mp-item__title" 
                                           href="{{ route('forums.posts.show', $comment->post->slug ?? '') }}">
                                            {{ \Illuminate\Support\Str::limit($comment->post->title ?? 'Post', 80) }}
                                        </a>
                                        <span class="mp-badge">
                                            <i class="bi bi-clock" aria-hidden="true"></i>
                                            <time datetime="{{ $comment->created_at ? $comment->created_at->toIso8601String() : '' }}">
                                                {{ $comment->created_at ? $comment->created_at->diffForHumans() : '—' }}
                                            </time>
                                        </span>
                                    </header>
                                    <div class="mp-item__body">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($comment->content ?? ''), 220) }}
                                    </div>
                                </article>
                            @empty
                                <div class="mp-empty" role="status">
                                    <div class="mp-empty__icon">
                                        <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                                    </div>
                                    <h3 class="mp-empty__title">No comments yet</h3>
                                    <p class="mp-empty__text">
                                        Comments you leave on posts will appear here.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>

    {{-- Toast Notifications Container --}}
    <div id="myToastHost" 
         class="mp-toast-host" 
         role="region" 
         aria-live="polite" 
         aria-atomic="true"
         aria-label="Notifications"></div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" 
         id="deleteConfirmModal" 
         tabindex="-1" 
         aria-labelledby="deleteConfirmModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content mp-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2" aria-hidden="true"></i>
                        <span>Confirm Delete</span>
                    </h5>
                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mp-warn" role="alert">
                        <div class="mp-warn__icon" aria-hidden="true">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <div>
                            <div class="mp-warn__title">Delete this post?</div>
                            <div class="text-muted mt-1" id="deleteConfirmText">
                                This action cannot be undone.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-outline-secondary mp-btn-pill" 
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="button" 
                            class="btn btn-danger mp-btn-pill" 
                            id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1" aria-hidden="true"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite([
    'resources/js/my-posts.js',
    'resources/js/media-lightbox.js'
])
@endpush