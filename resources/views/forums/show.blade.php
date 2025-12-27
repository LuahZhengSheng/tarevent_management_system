{{-- resources/views/forums/show.blade.php --}}
@extends('layouts.app')

@section('title', $post->title . ' - Forum')

@push('styles')
@vite(['resources/css/forums/forum-show.css', 'resources/css/forums/forum-media-gallery.css', 'resources/css/forums/media-lightbox.css'])
@endpush

@section('content')
<div class="forum-show-page user-site">
    <div class="container py-4">

        {{-- Back Navigation --}}
        <div class="mb-4">
            <a href="{{ route('forums.index') }}" class="btn-back-clean">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Forum</span>
            </a>
        </div>

        <div class="row g-4">
            {{-- Main Content --}}
            <div class="col-lg-8">
                {{-- Post Card --}}
                <article class="post-container-clean">

                    {{-- Post Header --}}
                    <header class="post-header-clean">
                        <div class="post-meta-row">
                            <div class="post-badges">
                                <span class="badge-clean badge-category">
                                    <i class="bi bi-folder"></i>
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                </span>

                                @if($post->visibility === 'clubonly')
                                <span class="badge-clean badge-club">
                                    <i class="bi bi-lock"></i> Club Only
                                </span>
                                @endif

                                @if($post->status === 'draft')
                                <span class="badge-clean badge-draft">
                                    <i class="bi bi-pencil"></i> Draft
                                </span>
                                @endif
                            </div>

                            @auth
                            <div class="dropdown">
                                <button class="post-menu-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($post->canBeEditedBy(auth()->user()))
                                    <li>
                                        <a class="dropdown-item" href="{{ route('forums.posts.edit', $post) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit Post
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" id="deletePostBtn">
                                            <i class="bi bi-trash me-2"></i>Delete Post
                                        </button>
                                    </li>
                                    @else
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" id="reportPostBtn"
                                                data-post-id="{{ $post->id }}" data-requires-auth="true">
                                            <i class="bi bi-flag me-2"></i>Report Post
                                        </button>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            @endauth
                        </div>

                        <h1 class="post-title-clean">{{ $post->title }}</h1>

                        {{-- Author Info Inline --}}
                        <div class="author-info-inline">
                            <img
                                src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                alt="{{ $post->user->name }}"
                                class="author-avatar-inline"
                                >
                            <div class="author-details-inline">
                                <div class="author-name-inline">
                                    <span>{{ $post->user->name }}</span>
                                    @if($post->user->hasRole('admin'))
                                    <span class="role-badge-inline admin-badge-inline">Admin</span>
                                    @elseif($post->user->hasRole('club'))
                                    <span class="role-badge-inline club-badge-inline">Club</span>
                                    @endif
                                </div>

                                <div class="post-meta-inline">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>

                                    @if($post->created_at != $post->updated_at)
                                    <span class="meta-separator">•</span>
                                    <span>Edited {{ $post->updated_at->diffForHumans() }}</span>
                                    @endif

                                    @if($post->club)
                                    <span class="meta-separator">•</span>
                                    <span><i class="bi bi-people"></i> {{ $post->club->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div class="stats-row-clean">
                            <div class="stat-item-clean views">
                                <i class="bi bi-eye"></i>
                                <span class="stat-value" id="viewsCount">{{ number_format($post->views_count) }}</span>
                            </div>
                            <div class="stat-item-clean likes">
                                <i class="bi bi-heart"></i>
                                <span class="stat-value" id="likesCount">{{ number_format($post->likes_count) }}</span>
                            </div>
                            <div class="stat-item-clean comments">
                                <i class="bi bi-chat"></i>
                                <span class="stat-value" id="commentsCount">{{ number_format($post->comments_count) }}</span>
                            </div>
                            <div class="stat-item-clean time">
                                <i class="bi bi-clock"></i>
                                <span class="stat-value">{{ $post->read_time }}</span>
                            </div>
                        </div>
                    </header>

                    {{-- Post Media Gallery --}}
                    @if($post->media_paths && count($post->media_paths) > 0)
                    @php
                    $mediaCount = count($post->media_paths);
                    $layoutClass = 'layout-' . min($mediaCount, 5);
                    @endphp

                    <div class="media-gallery-facebook {{ $layoutClass }}" id="mediaGrid" data-count="{{ $mediaCount }}">
                        @foreach($post->media_paths as $index => $media)
                        @php
                        $mediaPath = is_array($media) ? ($media['path'] ?? '') : $media;
                        $mediaType = is_array($media) ? ($media['type'] ?? 'image') : 'image';
                        $mimeType  = is_array($media) ? ($media['mime_type'] ?? 'image/jpeg') : 'image/jpeg';
                        $mediaDisk = is_array($media) ? ($media['disk'] ?? 'public') : 'public';

                        $isVideo = $mediaType === 'video' || str_starts_with($mimeType, 'video/');
                        if (empty($mediaPath)) continue;

                        $mediaUrl = $mediaDisk === 'public'
                        ? Storage::disk('public')->url($mediaPath)
                        : route('forums.posts.media.show', ['post' => $post->slug, 'index' => $index]);

                        $isVisible = $index < 5;
                        @endphp

                        <div class="fb-media-item media-item item-{{ $index + 1 }} {{ !$isVisible ? 'd-none' : '' }}" data-index="{{ $index }}">
                            @if($isVideo)
                            <video class="fb-media-content" preload="metadata">
                                <source src="{{ $mediaUrl }}" type="{{ $mimeType }}">
                            </video>
                            <div class="fb-media-badge video-badge">
                                <i class="bi bi-play-circle-fill"></i>
                            </div>
                            @else
                            <img src="{{ $mediaUrl }}" alt="Media {{ $index + 1 }}" class="fb-media-content" loading="lazy">
                            @endif


                            @if($index == 4 && $mediaCount > 5)
                            <div class="fb-overlay-more">
                                <span class="overlay-number">+{{ $mediaCount - 5 }}</span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Post Content --}}
                    <div class="post-content-clean">
                        {!! $post->content !!}
                    </div>

                    {{-- Tags --}}
                    @if($post->tags && count($post->tags) > 0)
                    <div class="tags-section-clean">
                        <div class="tags-label-clean">Tags</div>
                        <div class="tags-list-clean">
                            @foreach($post->tags as $tag)
                            <a href="{{ route('forums.index', ['tag' => $tag->slug]) }}" class="tag-item-clean">
                                {{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions Footer --}}
                    <footer class="actions-row-clean">
                        <div class="actions-group">
                            <button type="button"
                                    class="action-btn-clean like-btn {{ ($hasLiked ?? false) ? 'active' : '' }}"
                                    id="likeBtn"
                                    data-post-slug="{{ $post->slug }}"
                                    data-requires-auth="true">
                                <i class="bi {{ ($hasLiked ?? false) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                <span>{{ ($hasLiked ?? false) ? 'Liked' : 'Like' }}</span>
                            </button>

                            <button type="button" class="action-btn-clean comment-btn"
                                    onclick="document.getElementById('commentInput')?.focus()">
                                <i class="bi bi-chat"></i>
                                <span>Comment</span>
                            </button>

                            <button type="button" class="action-btn-clean save-btn"
                                    id="saveBtn" data-post-id="{{ $post->id }}" data-requires-auth="true">
                                <i class="bi bi-bookmark"></i>
                                <span>Save</span>
                            </button>

                            <button type="button" class="action-btn-clean share-btn" id="shareBtn">
                                <i class="bi bi-share"></i>
                                <span>Share</span>
                            </button>
                        </div>

                        <div class="post-date-clean">
                            <i class="bi bi-calendar3"></i>
                            <span>{{ $post->created_at->format('M d, Y') }}</span>
                        </div>
                    </footer>
                </article>

                {{-- Comments Section (Keep Original) --}}
                <section class="comments-section-modern" id="commentsSection">
                    <div class="comments-header-modern">
                        <h3 class="comments-title-modern">
                            Comments <span id="totalComments">{{ number_format($post->comments_count) }}
                        </h3>

                        <div class="comments-sort-modern" id="commentsSortTrigger">
                            <span class="comments-sort-label">Sort</span>
                            <button type="button" class="comments-sort-current" data-current-sort="recent">
                                <span>Most recent</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>

                            <div class="comments-sort-menu" id="commentsSortMenu">
                                <button type="button"
                                        class="comments-sort-option is-active"
                                        data-sort="recent">
                                    <span class="comments-sort-check">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <span>Most recent</span>
                                </button>

                                <button type="button"
                                        class="comments-sort-option"
                                        data-sort="popular">
                                    <span class="comments-sort-check">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <span>Most popular</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Composer shell (top) --}}
                    <div class="comment-composer-shell" id="commentComposerShell">
                        <div id="commentComposerSentinel" style="height:1px;"></div>

                        <div class="comment-form-modern comment-form--flat" id="commentFormTop">
                            <img
                                src="{{ auth()->check() ? (auth()->user()->profilePhotoUrl ?? asset('images/default-avatar.png')) : asset('images/default-avatar.png') }}"
                                class="comment-avatar-modern"
                                alt="You"
                                >

                            <div class="comment-input-wrapper-modern">
                                <textarea
                                    class="comment-input-modern"
                                    id="commentInput"
                                    rows="1"
                                    placeholder="Write a comment..."
                                    @guest disabled @endguest
                                    ></textarea>

                                <div class="comment-composer-actions">
                                    <button
                                        type="button"
                                        class="comment-tool-btn btn-emoji-modern"
                                        data-emoji-target="commentInput"
                                        data-requires-auth="true"
                                        title="Emoji"
                                        >
                                        <i class="bi bi-emoji-smile"></i>
                                    </button>

                                    <input
                                        type="file"
                                        id="commentMedia"
                                        multiple
                                        accept="image/*,video/*"
                                        hidden
                                        @guest disabled @endguest
                                        />

<!--                                    <button
                                        type="button"
                                        class="comment-tool-btn"
                                        id="commentCameraBtn"
                                        data-requires-auth="true"
                                        title="Photo/Video"
                                        >
                                        <i class="bi bi-camera"></i>
                                    </button>-->

                                    <button
                                        type="button"
                                        class="comment-send-btn"
                                        id="submitCommentBtn"
                                        data-requires-auth="true"
                                        title="Post"
                                        disabled
                                        >
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </div>

                                <div class="comment-preview" id="commentMediaPreview"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Comments list --}}
                    <div class="comments-list-modern comments-list--flat" id="commentsList">
                        @forelse($post->comments->whereNull('parent_id') as $comment)
                        @include('forums.partials.comment_item', ['comment' => $comment, 'isReply' => false])
                        @empty
                        <div class="no-comments-modern" id="noCommentsMessage">
                            <i class="bi bi-chat-left-text"></i>
                            <p>No comments yet. Be the first to comment!</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Sticky container --}}
                    <div class="comment-form-sticky-wrap" id="commentFormStickyWrap" aria-hidden="true">
                        <div class="comment-form-sticky-inner" id="commentFormStickyInner"></div>
                    </div>
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="col-lg-4">
                {{-- Author Card --}}
                <div class="author-sidebar-card">
                    <div class="author-card-header">
                        <img
                            src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $post->user->name }}"
                            class="author-card-avatar"
                            >
                        <h3 class="author-card-name">{{ $post->user->name }}</h3>
                        <p class="author-card-role">
                            @if($post->user->hasRole('admin'))
                            <i class="bi bi-shield-check"></i> Administrator
                            @elseif($post->user->hasRole('club'))
                            <i class="bi bi-people"></i> Club Admin
                            @else
                            <i class="bi bi-person"></i> Member
                            @endif
                        </p>
                    </div>

                    <div class="author-stats-grid">
                        <div class="author-stat-item">
                            <span class="author-stat-value">{{ $post->user->posts_count ?? 42 }}</span>
                            <span class="author-stat-label">Posts</span>
                        </div>
                        <div class="author-stat-item">
                            <span class="author-stat-value">{{ $post->user->total_likes ?? 1248 }}</span>
                            <span class="author-stat-label">Likes</span>
                        </div>
                        <div class="author-stat-item">
                            <span class="author-stat-value">{{ $post->user->saves_count ?? 356 }}</span>
                            <span class="author-stat-label">Saves</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</div>

{{-- Dark Mode Toggle --}}
<button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle dark mode">
    <i class="bi bi-moon-stars"></i>
</button>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning"></i> Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this post?</p>
                <p class="text-danger"><i class="bi bi-info-circle"></i> This action cannot be undone. All comments will also be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Delete Post
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// Dark Mode Toggle
    document.addEventListener('DOMContentLoaded', function () {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        const icon = darkModeToggle.querySelector('i');

        // Check saved preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateIcon(savedTheme);

        darkModeToggle.addEventListener('click', function () {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.classList.remove('bi-moon-stars');
                icon.classList.add('bi-sun');
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon-stars');
            }
        }
    });
</script>
@endsection

@push('scripts')
@vite(['resources/js/forum-show.js', 'resources/js/media-lightbox.js'])
@endpush
