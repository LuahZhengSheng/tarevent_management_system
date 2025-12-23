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
        <div class="back-navigation mb-4">
            <a href="{{ route('forums.index') }}" class="btn-back-modern">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Forum</span>
            </a>
        </div>

        <div class="row g-4">
            {{-- Main Content --}}
            <div class="col-lg-8">
                {{-- Post Card --}}
                <article class="post-detail-card">

                    {{-- Post Header --}}
                    <header class="post-header">
                        <div class="post-meta-top">
                            <div class="badges-group">
                                <span class="badge category-badge-modern">
                                    <i class="bi bi-folder-fill"></i>
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                </span>

                                @if($post->visibility === 'clubonly')
                                <span class="badge visibility-badge-modern">
                                    <i class="bi bi-lock-fill"></i> Club Only
                                </span>
                                @endif

                                @if($post->status === 'draft')
                                <span class="badge draft-badge-modern">
                                    <i class="bi bi-pencil-fill"></i> Draft
                                </span>
                                @endif
                            </div>

                            @auth
                            <div class="dropdown">
                                <button class="btn-dropdown-modern" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
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

                        <h1 class="post-title-modern post-title">{{ $post->title }}</h1>

                        {{-- Author Info --}}
                        <div class="author-section-modern">
                            <img
                                src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                alt="{{ $post->user->name }}"
                                class="author-avatar-modern"
                                >
                            <div class="author-details-modern">
                                <div class="author-name-row">
                                    <span class="author-name-modern">{{ $post->user->name }}</span>
                                    @if($post->user->hasRole('admin'))
                                    <span class="badge role-badge-modern admin-badge-modern">Admin</span>
                                    @elseif($post->user->hasRole('club'))
                                    <span class="badge role-badge-modern club-badge-modern">Club Admin</span>
                                    @endif
                                </div>

                                <div class="post-meta-info-modern">
                                    <span class="meta-item-modern">
                                        <i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}
                                    </span>

                                    @if($post->created_at != $post->updated_at)
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item-modern">
                                        <i class="bi bi-pencil"></i> Edited {{ $post->updated_at->diffForHumans() }}
                                    </span>
                                    @endif

                                    @if($post->club)
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item-modern">
                                        <i class="bi bi-people-fill"></i> {{ $post->club->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Stats Bar --}}
                        <div class="stats-bar-modern">
                            <div class="stat-item-modern views">
                                <i class="bi bi-eye-fill"></i>
                                <span>{{ number_format($post->views_count) }}</span>
                            </div>
                            <div class="stat-item-modern likes">
                                <i class="bi bi-heart-fill"></i>
                                <span id="likesCount">{{ number_format($post->likes_count) }}</span>
                            </div>
                            <div class="stat-item-modern comments">
                                <i class="bi bi-chat-fill"></i>
                                <span id="commentsCount">{{ number_format($post->comments_count) }}</span>
                            </div>
                            <div class="stat-item-modern read-time">
                                <i class="bi bi-book-fill"></i>
                                <span>{{ $post->read_time }}</span>
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
                        $isVideo   = $mediaType === 'video' || str_starts_with($mimeType, 'video/');
                        if (empty($mediaPath)) continue;
                        $isVisible = $index < 5;
                        @endphp

                        <div class="fb-media-item media-item item-{{ $index + 1 }} {{ !$isVisible ? 'd-none' : '' }}" data-index="{{ $index }}">
                            @if($isVideo)
                            <video class="fb-media-content" preload="metadata">
                                <source src="{{ Storage::url($mediaPath) }}" type="{{ $mimeType }}">
                            </video>
                            <div class="fb-media-badge video-badge">
                                <i class="bi bi-play-circle-fill"></i>
                            </div>
                            @else
                            <img src="{{ Storage::url($mediaPath) }}" alt="Media {{ $index + 1 }}" class="fb-media-content" loading="lazy">
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
                    <div class="post-content-modern">
                        {!! $post->content !!}
                    </div>

                    {{-- Tags --}}
                    @if($post->tags && count($post->tags) > 0)
                    <div class="tags-section-modern">
                        <div class="tags-label-modern">
                            <i class="bi bi-tags-fill"></i>
                            <span>Tags</span>
                        </div>
                        <div class="tags-list-modern">
                            @foreach($post->tags as $tag)
                            <a href="{{ route('forums.index', ['tag' => $tag->slug]) }}" class="tag-badge-modern">
                                <i class="bi bi-hash"></i> {{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions Footer --}}
                    <footer class="post-actions-modern">
                        <div class="actions-left">
                            <button type="button"
                                    class="action-btn-modern btn-like-modern {{ ($hasLiked ?? false) ? 'active' : '' }}"
                                    id="likeBtn"
                                    data-post-slug="{{ $post->slug }}"
                                    data-requires-auth="true">
                                <i class="bi {{ ($hasLiked ?? false) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                <span>{{ ($hasLiked ?? false) ? 'Liked' : 'Like' }}</span>
                            </button>

                            <button type="button" class="action-btn-modern btn-comment-modern"
                                    onclick="document.getElementById('commentInput')?.focus()">
                                <i class="bi bi-chat-dots"></i>
                                <span>Comment</span>
                            </button>

                            <button type="button" class="action-btn-modern btn-save-modern"
                                    id="saveBtn" data-post-id="{{ $post->id }}" data-requires-auth="true">
                                <i class="bi bi-bookmark"></i>
                                <span>Save</span>
                            </button>

                            <button type="button" class="action-btn-modern btn-share-modern" id="shareBtn">
                                <i class="bi bi-share"></i>
                                <span>Share</span>
                            </button>
                        </div>

                        <div class="actions-right">
                            <span class="post-date-modern">
                                <i class="bi bi-calendar3"></i> {{ $post->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </footer>
                </article>

                {{-- Comments Section --}}
                <section class="comments-section-modern" id="commentsSection">
                    <div class="comments-header-modern">
                        <h3 class="comments-title-modern">
                            Comments ({{ $post->comments_count }})
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
                        {{-- Sentinel: NEVER moved, used by IntersectionObserver --}}
                        <div id="commentComposerSentinel" style="height:1px;"></div>

                        {{-- Top composer: this node will be MOVED into stickyInner when needed --}}
                        <div class="comment-form-modern comment-form--flat" id="commentFormTop">
                            <img
                                src="{{ auth()->check() ? (auth()->user()->profilePhotoUrl ?? asset('images/default-avatar.png')) : asset('images/default-avatar.png') }}"
                                class="comment-avatar-modern"
                                alt="You"
                                />

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

                                    <button
                                        type="button"
                                        class="comment-tool-btn"
                                        id="commentCameraBtn"
                                        data-requires-auth="true"
                                        title="Photo/Video"
                                        >
                                        <i class="bi bi-camera"></i>
                                    </button>

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

                    {{-- Sticky container (BOTTOM of comments section) --}}
                    <div class="comment-form-sticky-wrap" id="commentFormStickyWrap" aria-hidden="true">
                        <div class="comment-form-sticky-inner" id="commentFormStickyInner"></div>
                    </div>
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="col-lg-4">
                {{-- 你原本 sidebar 保持不动（此处略，继续用你原 show.blade.php 的 sidebar 内容即可） --}}
                {{-- 为避免你本地 sidebar 与这里不同，请把你原本 sidebar 代码粘回这里 --}}
            </aside>
        </div>

    </div>
</div>

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
@endsection

@push('scripts')
@vite(['resources/js/forum-show.js', 'resources/js/media-lightbox.js'])
@endpush
