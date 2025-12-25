{{-- resources/views/forums/partials/my-posts-tab.blade.php --}}

@switch($activeTab)
    {{-- ======================= POSTS TAB ======================= --}}
    @case('posts')
        @forelse($tabs['posts']['items'] ?? [] as $item)
            @php $post = $item['post']; @endphp

            <article class="post-item" data-post-id="{{ $post->id }}">
                {{-- Post Header --}}
                <div class="post-header">
                    <div class="post-meta-left">
                        <span class="post-status status-{{ $post->status }}">
                            {{ ucfirst($post->status) }}
                        </span>

                        @if($post->visibility === 'club_only')
                            <span class="post-visibility visibility-locked">
                                <i class="bi bi-lock-fill"></i> Club only
                            </span>
                        @else
                            <span class="post-visibility visibility-public">
                                <i class="bi bi-globe"></i> Public
                            </span>
                        @endif
                    </div>

                    <time class="post-date">
                        {{ $post->formatted_date ?? $post->created_at->format('M d, Y') }}
                    </time>
                </div>

                {{-- Post Title --}}
                <h2 class="post-title">
                    <a href="{{ route('forums.posts.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </h2>

                {{-- Post Excerpt --}}
                <p class="post-excerpt">
                    {{ $item['excerpt'] }}
                </p>

                {{-- Media Gallery --}}
                @if(!empty($item['media']) && count($item['media']) > 0)
                    @php
                        $media = $item['media'];
                        $mediaCount = count($media);
                        $layoutClass = 'layout-' . min($mediaCount, 5);
                    @endphp
                    <div class="post-media">
                        <div class="media-gallery-facebook {{ $layoutClass }}">
                            @foreach($media as $index => $mediaItem)
                                @if($index < 5)
                                    @php
                                        $isVideo = str_starts_with($mediaItem['type'], 'video');
                                        $thumbUrl = $mediaItem['url'];
                                    @endphp
                                    <button
                                        type="button"
                                        class="fb-media-item item-{{ $index + 1 }}"
                                        onclick="openLightbox({{ $index }})"
                                        data-type="{{ $mediaItem['type'] }}"
                                        data-src="{{ $mediaItem['url'] }}"
                                    >
                                        <img src="{{ $thumbUrl }}" alt="Media" class="fb-media-content">

                                        @if($isVideo)
                                            <span class="fb-media-badge video-badge">
                                                <i class="bi bi-play-fill"></i> Video
                                            </span>
                                        @endif

                                        @if($index === 4 && $mediaCount > 5)
                                            <div class="fb-overlay-more">
                                                <span class="overlay-number">+{{ $mediaCount - 5 }}</span>
                                            </div>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Post Footer --}}
                <div class="post-footer">
                    <div class="post-stats">
                        <span class="stat">
                            <i class="bi bi-chat"></i>
                            {{ $item['comments_count'] }}
                        </span>
                        <span class="stat">
                            <i class="bi bi-heart"></i>
                            {{ $item['likes_count'] }}
                        </span>
                        <span class="stat">
                            <i class="bi bi-bookmark"></i>
                            {{ $item['saves_count'] }}
                        </span>
                    </div>

                    <div class="post-actions">
                        <a href="{{ route('forums.posts.edit', $post) }}" class="action-link">
                            <i class="bi bi-pencil"></i>
                            Edit
                        </a>
                        <button
                            class="action-link action-delete js-delete-post"
                            data-delete-url="{{ route('forums.posts.destroy', $post) }}"
                            data-post-title="{{ $post->title }}"
                        >
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-title">No posts yet</h3>
                <p class="empty-text">Start sharing your thoughts with the community!</p>
                <a href="{{ route('forums.create') }}" class="empty-action">
                    <i class="bi bi-plus-circle"></i>
                    Create Your First Post
                </a>
            </div>
        @endforelse
        @break

    {{-- ======================= DRAFTS TAB ======================= --}}
    @case('drafts')
        @forelse($tabs['drafts']['items'] ?? [] as $item)
            @php $post = $item['post']; @endphp

            <article class="post-item post-draft" data-post-id="{{ $post->id }}">
                <div class="post-header">
                    <span class="post-status status-draft">Draft</span>
                    <time class="post-date">
                        Updated {{ $post->updated_at->format('M d, Y') }}
                    </time>
                </div>

                <h2 class="post-title">
                    <a href="{{ route('forums.posts.edit', $post) }}">
                        {{ $post->title }}
                    </a>
                </h2>

                <p class="post-excerpt">{{ $item['excerpt'] }}</p>

                <div class="post-footer">
                    <div class="post-actions">
                        <a href="{{ route('forums.posts.edit', $post) }}" class="action-link action-primary">
                            <i class="bi bi-pencil"></i>
                            Continue Editing
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-file-earmark"></i>
                </div>
                <h3 class="empty-title">No drafts</h3>
                <p class="empty-text">All your posts are published!</p>
            </div>
        @endforelse
        @break

    {{-- ======================= LIKES TAB ======================= --}}
    @case('likes')
        @forelse($tabs['likes']['items'] ?? [] as $item)
            @php $post = $item['post']; @endphp

            <article class="post-item" data-post-id="{{ $post->id }}">
                <div class="post-header">
                    <span class="post-meta-text">
                        <i class="bi bi-heart-fill text-danger"></i>
                        You liked this on {{ $item['created_at']->format('M d, Y') }}
                    </span>
                </div>

                <h2 class="post-title">
                    <a href="{{ route('forums.posts.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </h2>

                <p class="post-excerpt">{{ $item['excerpt'] }}</p>

                <div class="post-footer">
                    <div class="post-stats">
                        <span class="stat">
                            <i class="bi bi-chat"></i>
                            {{ $item['comments_count'] }}
                        </span>
                        <span class="stat">
                            <i class="bi bi-heart"></i>
                            {{ $item['likes_count'] }}
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-heart"></i>
                </div>
                <h3 class="empty-title">No liked posts</h3>
                <p class="empty-text">Start liking posts to save them here!</p>
            </div>
        @endforelse
        @break

    {{-- ======================= SAVES TAB ======================= --}}
    @case('saves')
        @forelse($tabs['saves']['items'] ?? [] as $item)
            @php $post = $item['post']; @endphp

            <article class="post-item" data-post-id="{{ $post->id }}">
                <div class="post-header">
                    <span class="post-meta-text">
                        <i class="bi bi-bookmark-fill text-primary"></i>
                        Saved on {{ $item['created_at']->format('M d, Y') }}
                    </span>
                </div>

                <h2 class="post-title">
                    <a href="{{ route('forums.posts.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </h2>

                <p class="post-excerpt">{{ $item['excerpt'] }}</p>

                <div class="post-footer">
                    <div class="post-stats">
                        <span class="stat">
                            <i class="bi bi-chat"></i>
                            {{ $item['comments_count'] }}
                        </span>
                        <span class="stat">
                            <i class="bi bi-heart"></i>
                            {{ $item['likes_count'] }}
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-bookmark"></i>
                </div>
                <h3 class="empty-title">No saved posts</h3>
                <p class="empty-text">Bookmark posts to read them later!</p>
            </div>
        @endforelse
        @break

    {{-- ======================= COMMENTS TAB ======================= --}}
    @case('comments')
        @forelse($tabs['comments']['items'] ?? [] as $item)
            @php
                $comment = $item['comment'];
                $post    = $item['post'];
            @endphp

            <article class="comment-item">
                <div class="comment-header">
                    <span class="comment-meta">
                        Commented on
                        <a href="{{ route('forums.posts.show', $post->slug) }}" class="post-link">
                            {{ $post->title }}
                        </a>
                    </span>
                    <time class="comment-date">
                        {{ $comment->created_at->format('M d, Y') }}
                    </time>
                </div>
                <p class="comment-content">{{ $comment->content }}</p>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-chat-left-text"></i>
                </div>
                <h3 class="empty-title">No comments yet</h3>
                <p class="empty-text">Join the conversation and share your thoughts!</p>
            </div>
        @endforelse
        @break
@endswitch
