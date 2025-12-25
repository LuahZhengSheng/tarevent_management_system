{{-- resources/views/forums/partials/posts_page.blade.php --}}
@php use Illuminate\Support\Str; @endphp

@forelse($posts as $post)
    <article class="post-card js-reveal" data-post-slug="{{ $post->slug }}">
        <div class="post-card-inner">
            <div class="post-header">
                <div class="post-author">
                    <img
                        class="author-avatar"
                        src="{{ $post->user?->profile_photo ? Storage::url($post->user->profile_photo) : asset('images/default-avatar.png') }}"
                        alt="Author avatar"
                        loading="lazy"
                    >
                    <div class="author-info">
                        <div class="author-name">{{ $post->user?->name ?? 'Unknown' }}</div>
                        <div class="post-time">
                            <i class="bi bi-clock"></i>
                            <span>{{ $post->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="post-badges">
                    <span class="category-badge">{{ $post->category?->name ?? 'Uncategorized' }}</span>
                    @if($post->visibility === 'club_only')
                        <span class="club-badge"><i class="bi bi-lock"></i> Club only</span>
                    @endif
                </div>
            </div>

            <div class="post-content">
                <h2 class="post-title">{{ $post->title }}</h2>
                <p class="post-excerpt">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 180) }}</p>
            </div>

            {{-- Tags (display only) --}}
            @if($post->tags && $post->tags->count() > 0)
                <div class="post-tags">
                    @foreach($post->tags->take(6) as $tag)
                        <a class="tag-item" href="{{ route('forums.index', array_merge(request()->query(), ['tag' => $tag->slug])) }}">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                    @if($post->tags->count() > 6)
                        <span class="tag-item tag-more">+{{ $post->tags->count() - 6 }}</span>
                    @endif
                </div>
            @endif

            {{-- Media gallery: DO NOT change your existing markup if you already have one.
                 If your current forum.css expects a specific gallery markup, paste that block here.
                 (Keeping minimal here to not break lightbox integration.) --}}

            <div class="post-footer">
                <div class="post-stats">
                    <span class="stat-item"><i class="bi bi-chat"></i> {{ $post->comments_count ?? 0 }}</span>
                    <span class="stat-item"><i class="bi bi-heart"></i> {{ $post->likes_count ?? 0 }}</span>
                    <span class="stat-item"><i class="bi bi-eye"></i> {{ $post->views_count ?? 0 }}</span>
                </div>

                <div class="post-action">
                    <a class="read-more" href="{{ route('forums.posts.show', $post->slug) }}">
                        Read <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </article>
@empty
    <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-inbox"></i></div>
        <h3 class="empty-title">No posts found</h3>
        <p class="empty-text">
            @if(request()->hasAny(['q','category_id','tag','sort']))
                Try adjusting your filters or search terms to find what you're looking for.
            @else
                Be the first to start a meaningful discussion in our community!
            @endif
        </p>
        <a href="{{ route('forums.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Create First Post
        </a>
    </div>
@endforelse
