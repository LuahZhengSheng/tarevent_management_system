{{-- resources/views/forums/show.blade.php --}}
@extends('layouts.app')

@section('title', $post->title . ' - Forum')

@push('styles')
@vite(['resources/css/forum-show.css', 'resources/css/media-lightbox.css'])
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
                        {{-- Badges Row --}}
                        <div class="post-meta-top">
                            <div class="badges-group">
                                <span class="badge category-badge-modern">
                                    <i class="bi bi-folder-fill"></i>
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                </span>
                                @if($post->visibility === 'club_only')
                                <span class="badge visibility-badge-modern">
                                    <i class="bi bi-lock-fill"></i>
                                    Club Only
                                </span>
                                @endif
                                @if($post->status === 'draft')
                                <span class="badge draft-badge-modern">
                                    <i class="bi bi-pencil-fill"></i>
                                    Draft
                                </span>
                                @endif
                            </div>

                            @auth
                            @if($post->canBeEditedBy(auth()->user()))
                            <div class="dropdown">
                                <button class="btn-dropdown-modern" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
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
                                </ul>
                            </div>
                            @endif
                            @endauth
                        </div>

                        {{-- Title --}}
                        <h1 class="post-title-modern">{{ $post->title }}</h1>

                        {{-- Author Info --}}
                        <div class="author-section-modern">
                            <img src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $post->user->name }}" 
                                 class="author-avatar-modern">
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
                                        <i class="bi bi-clock"></i>
                                        {{ $post->created_at->diffForHumans() }}
                                    </span>
                                    @if($post->created_at != $post->updated_at)
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item-modern">
                                        <i class="bi bi-pencil"></i>
                                        Edited {{ $post->updated_at->diffForHumans() }}
                                    </span>
                                    @endif
                                    @if($post->club)
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item-modern">
                                        <i class="bi bi-people-fill"></i>
                                        {{ $post->club->name }}
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
                        $mimeType = is_array($media) ? ($media['mime_type'] ?? 'image/jpeg') : 'image/jpeg';
                        $isVideo = $mediaType === 'video' || str_starts_with($mimeType, 'video/');
                        if (empty($mediaPath)) continue;

                        // 只在前5个显示，其他隐藏但仍然存在于 DOM 中
                        $isVisible = $index < 5;
                        @endphp

                        {{-- 添加 media-item 类，超过 5 个的隐藏 --}}
                        <div class="fb-media-item media-item item-{{ $index + 1 }} {{ !$isVisible ? 'd-none' : '' }}" 
                             data-index="{{ $index }}">
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

                    {{-- Post Content --}}
                    <div class="post-content-modern">
                        {!! $post->content !!}
                    </div>

                    {{-- Tags Section --}}
                    @if($post->tags && count($post->tags) > 0)
                    <div class="tags-section-modern">
                        <div class="tags-label-modern">
                            <i class="bi bi-tags-fill"></i>
                            <span>Tags</span>
                        </div>
                        <div class="tags-list-modern">
                            @foreach($post->tags as $tag)
                            <a href="{{ route('forums.index', ['tag' => $tag->slug]) }}" class="tag-badge-modern">
                                <i class="bi bi-hash"></i>{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions Footer --}}
                    <footer class="post-actions-modern">
                        <div class="actions-left">
                            {{-- Like Button --}}
                            <button type="button" 
                                    class="action-btn-modern btn-like-modern {{ $hasLiked ?? false ? 'active' : '' }}" 
                                    id="likeBtn"
                                    data-post-id="{{ $post->id }}"
                                    data-requires-auth="true">
                                <i class="bi bi-heart{{ $hasLiked ?? false ? '-fill' : '' }}"></i>
                                <span>{{ $hasLiked ?? false ? 'Liked' : 'Like' }}</span>
                            </button>

                            {{-- Comment Button --}}
                            <button type="button" 
                                    class="action-btn-modern btn-comment-modern" 
                                    onclick="document.getElementById('commentInput').focus()">
                                <i class="bi bi-chat-dots"></i>
                                <span>Comment</span>
                            </button>

                            {{-- Share Button --}}
                            <button type="button" class="action-btn-modern btn-share-modern" id="shareBtn">
                                <i class="bi bi-share"></i>
                                <span>Share</span>
                            </button>
                        </div>
                        <div class="actions-right">
                            <span class="post-date-modern">
                                <i class="bi bi-calendar3"></i>
                                {{ $post->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </footer>
                </article>

                {{-- Comments Section --}}
                <section class="comments-section-modern">
                    <div class="comments-header-modern">
                        <h3 class="comments-title-modern">
                            <i class="bi bi-chat-dots-fill"></i>
                            <span>Comments</span>
                            <span class="comments-count-modern" id="totalComments">({{ $post->comments_count }})</span>
                        </h3>
                    </div>

                    {{-- Comment Input --}}
                    <div class="comment-form-modern">
                        <img src="{{ auth()->check() ? auth()->user()->profile_photo_url : asset('images/default-avatar.png') }}" 
                             alt="Your avatar" 
                             class="comment-avatar-modern">
                        <div class="comment-input-wrapper-modern">
                            <textarea class="comment-input-modern" 
                                      id="commentInput" 
                                      rows="3" 
                                      placeholder="Share your thoughts..."
                                      @guest disabled @endguest></textarea>
                            <div class="comment-form-actions-modern">
                                <button type="button" 
                                        class="btn-submit-comment-modern" 
                                        id="submitCommentBtn">
                                    <i class="bi bi-send-fill"></i>
                                    <span>Post Comment</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Comments List --}}
                    <div class="comments-list-modern" id="commentsList">
                        @forelse($post->comments()->whereNull('parent_id')->latest()->get() as $comment)
                        <div class="comment-item-modern" data-comment-id="{{ $comment->id }}">
                            <img src="{{ $comment->user->profile_photo_url }}" 
                                 alt="{{ $comment->user->name }}" 
                                 class="comment-avatar-modern">
                            <div class="comment-body-modern">
                                <div class="comment-header-modern">
                                    <div class="comment-author-info-modern">
                                        <span class="comment-author-modern">{{ $comment->user->name }}</span>
                                        @if($comment->user->hasRole('admin'))
                                        <span class="badge role-badge-modern admin-badge-modern">Admin</span>
                                        @endif
                                        <span class="comment-time-modern">
                                            <i class="bi bi-clock"></i>
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    @auth
                                    @if($comment->canBeEditedBy(auth()->user()))
                                    <div class="dropdown">
                                        <button class="btn-comment-menu-modern" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item text-danger delete-comment-btn"
                                                        data-comment-id="{{ $comment->id }}">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @endif
                                    @endauth
                                </div>
                                <p class="comment-text-modern">{{ $comment->content }}</p>

                                {{-- Reply button --}}
                                <button type="button" 
                                        class="btn-reply-modern" 
                                        data-comment-id="{{ $comment->id }}"
                                        data-requires-auth="true">
                                    <i class="bi bi-reply"></i>
                                    Reply
                                </button>

                                {{-- Replies --}}
                                @if($comment->replies->count() > 0)
                                <div class="replies-list-modern">
                                    @foreach($comment->replies as $reply)
                                    <div class="reply-item-modern" data-comment-id="{{ $reply->id }}">
                                        <img src="{{ $reply->user->profile_photo_url }}" 
                                             alt="{{ $reply->user->name }}" 
                                             class="reply-avatar-modern">
                                        <div class="reply-body-modern">
                                            <div class="reply-header-modern">
                                                <span class="reply-author-modern">{{ $reply->user->name }}</span>
                                                <span class="reply-time-modern">{{ $reply->created_at->diffForHumans() }}</span>
                                                @auth
                                                @if($reply->canBeEditedBy(auth()->user()))
                                                <button class="btn-delete-reply-modern delete-comment-btn"
                                                        data-comment-id="{{ $reply->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @endif
                                                @endauth
                                            </div>
                                            <p class="reply-text-modern">{{ $reply->content }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Reply Input --}}
                                <div class="reply-form-modern" id="replyInput{{ $comment->id }}" style="display: none;">
                                    <input type="text" 
                                           class="reply-input-modern" 
                                           placeholder="Write a reply..."
                                           id="replyText{{ $comment->id }}">
                                    <div class="reply-form-actions-modern">
                                        <button type="button" 
                                                class="btn-submit-reply-modern submit-reply-btn"
                                                data-comment-id="{{ $comment->id }}">
                                            <i class="bi bi-send"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn-cancel-reply-modern cancel-reply-btn"
                                                data-comment-id="{{ $comment->id }}">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="no-comments-modern" id="noCommentsMessage">
                            <i class="bi bi-chat-left-text"></i>
                            <p>No comments yet. Be the first to comment!</p>
                        </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="col-lg-4">
                {{-- Author Card --}}
                <div class="sidebar-card-modern author-card-modern">
                    <div class="author-card-header-modern">
                        <img src="{{ $post->user->profile_photo_url }}" 
                             alt="{{ $post->user->name }}" 
                             class="author-card-avatar-modern">
                        <h4 class="author-card-name-modern">{{ $post->user->name }}</h4>
                        <p class="author-card-role-modern">
                            @if($post->user->hasRole('admin'))
                            <i class="bi bi-shield-fill-check"></i> Administrator
                            @elseif($post->user->hasRole('club'))
                            <i class="bi bi-star-fill"></i> Club Admin
                            @else
                            <i class="bi bi-person-fill"></i> Student
                            @endif
                        </p>
                    </div>
                    @php $authorStats = $post->user->postStats; @endphp
                    <div class="author-stats-modern">
                        <div class="stat-box-modern posts">
                            <div class="stat-value-modern">{{ $authorStats['total_posts'] }}</div>
                            <div class="stat-label-modern">Posts</div>
                        </div>
                        <div class="stat-box-modern likes">
                            <div class="stat-value-modern">{{ $authorStats['total_likes'] }}</div>
                            <div class="stat-label-modern">Likes</div>
                        </div>
                        <div class="stat-box-modern comments">
                            <div class="stat-value-modern">{{ $authorStats['total_comments'] }}</div>
                            <div class="stat-label-modern">Comments</div>
                        </div>
                    </div>
                </div>

                {{-- Related Posts --}}
                @php
                $relatedPosts = \App\Models\Post::published()
                ->where('category_id', $post->category_id) 
                ->where('id', '!=', $post->id)
                ->latest()
                ->limit(5)
                ->get();
                @endphp

                @if($relatedPosts->count() > 0)
                <div class="sidebar-card-modern related-posts-card-modern">
                    <h4 class="sidebar-card-title-modern">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        Related Posts
                    </h4>
                    <div class="related-posts-list-modern">
                        @foreach($relatedPosts as $related)
                        <a href="{{ route('forums.posts.show', $related) }}" class="related-post-item-modern">
                            @if($related->media_paths && count($related->media_paths) > 0)
                            @php
                            $firstMedia = $related->media_paths[0];
                            $thumbnailPath = is_array($firstMedia) ? ($firstMedia['path'] ?? '') : $firstMedia;
                            @endphp

                            @if(!empty($thumbnailPath))
                            <img src="{{ Storage::url($thumbnailPath) }}" 
                                 alt="{{ $related->title }}" 
                                 class="related-post-thumb-modern">
                            @else
                            <div class="related-post-thumb-placeholder-modern">
                                <i class="bi bi-file-text"></i>
                            </div>
                            @endif
                            @else
                            <div class="related-post-thumb-placeholder-modern">
                                <i class="bi bi-file-text"></i>
                            </div>
                            @endif
                            <div class="related-post-content-modern">
                                <h5 class="related-post-title-modern">{{ Str::limit($related->title, 60) }}</h5>
                                <span class="related-post-time-modern">
                                    <i class="bi bi-clock"></i>
                                    {{ $related->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Quick Actions --}}
                @auth
                <div class="sidebar-card-modern quick-actions-card-modern">
                    <h4 class="sidebar-card-title-modern">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Quick Actions
                    </h4>
                    <div class="quick-actions-list-modern">
                        <a href="{{ route('forums.posts.create') }}" class="quick-action-btn-modern create">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Create New Post</span>
                        </a>
                        <a href="{{ route('forums.index') }}" class="quick-action-btn-modern browse">
                            <i class="bi bi-grid-fill"></i>
                            <span>Browse Forum</span>
                        </a>
                    </div>
                </div>
                @endauth
            </aside>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
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
                <p>Are you sure you want to delete this post?</p>
                <p class="text-danger">
                    <i class="bi bi-info-circle"></i>
                    This action cannot be undone. All comments will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    Delete Post
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/forum-show.js', 'resources/js/media-lightbox.js'])
@endpush
