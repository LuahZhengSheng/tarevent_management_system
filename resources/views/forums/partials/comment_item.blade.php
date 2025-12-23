@php
$isReply    = $isReply ?? ($comment->parent_id !== null);
$avatar     = $comment->user->profile_photo_url ?? asset('images/default-avatar.png');
$authorName = $comment->user->name ?? 'Unknown';
$timeText   = $comment->created_at ? $comment->created_at->diffForHumans() : '';

// 你原本用 canBeEditedBy 来判断可编辑/删除
$canManage  = auth()->check() && $comment->canBeEditedBy(auth()->user());
@endphp

@if(!$isReply)
{{-- 顶层评论 --}}
<div class="comment-item-modern comment-item" data-comment-id="{{ $comment->id }}">
    <img src="{{ $avatar }}" alt="{{ $authorName }}" class="comment-avatar-modern">

    <div class="comment-body-modern">
        <div class="comment-mainline-modern">
            <div class="comment-content-modern">
                <div class="comment-meta-modern">
                    <span class="comment-author-modern">{{ $authorName }}</span>
                    <span class="comment-time-modern">{{ $timeText }}</span>
                </div>

                <div class="comment-text-modern">
                    {{-- 只有后端真的有 replyTo（reply_to_user_id 不为 null）才显示 @ --}}
                    @if ($comment->replyTo)
                    <span class="comment-mention">
                        @ {{ $comment->replyTo->name }}
                    </span>
                    @endif

                    {{ $comment->content }}
                </div>

                @if(isset($comment->media) && $comment->media->count() > 0)
                <div class="comment-media-grid">
                    @foreach($comment->media as $m)
                    @if(($m->type ?? 'image') === 'video')
                    <video class="comment-media-item" controls preload="metadata">
                        <source src="{{ $m->url }}" type="{{ $m->mime_type }}">
                    </video>
                    @else
                    <img class="comment-media-item"
                         src="{{ $m->url }}"
                         alt="comment media"
                         loading="lazy">
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            {{-- hover 才出现菜单 --}}
            @if($canManage)
            <div class="comment-menu-wrap">
                <button type="button"
                        class="btn-comment-menu-modern comment-menu-btn"
                        aria-label="Comment menu"
                        data-menu-for="{{ $comment->id }}">
                    <i class="bi bi-three-dots"></i>
                </button>

                <div class="comment-menu-dropdown" id="commentMenu{{ $comment->id }}">
                    <button type="button"
                            class="comment-menu-item edit-comment-btn"
                            data-comment-id="{{ $comment->id }}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button"
                            class="comment-menu-item delete-comment-btn"
                            data-comment-id="{{ $comment->id }}">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
            @endif
        </div>

        {{-- actions：Like + Reply --}}
        @php
        $likedByMe = auth()->check() ? $comment->isLikedBy(auth()->user()) : false;
        @endphp
        <div class="comment-actions-modern">
            <button type="button"
                    class="comment-action-link comment-like-btn {{ $likedByMe ? 'active' : '' }}"
                    data-comment-id="{{ $comment->id }}"
                    data-requires-auth="true">
                @if($likedByMe)
                Liked
                @else
                Like
                @endif
                @if($comment->likes_count > 0)
                {{ $comment->likes_count }}
                @endif
            </button>

            {{-- 顶层评论的 Reply：不带 user-id，表示只是“回复这条 comment”，不 @ 某人 --}}
            <button type="button"
                    class="comment-action-link btn-reply btn-reply-modern"
                    data-comment-id="{{ $comment->id }}"
                    data-requires-auth="true">
                Reply
            </button>
        </div>

        {{-- Reply form：顶层评论的内联回复框 --}}
        @include('forums.partials.reply_form')
        

        {{-- Reply 区域：默认不展开，只显示“View X reply” --}}
        @php
        $replyCount = $comment->replies_count ?? ($comment->replies->count() ?? 0);
        @endphp

        <div class="replies-wrapper" data-comment-id="{{ $comment->id }}">
            @if($replyCount > 0)
            <button type="button"
                    class="comment-action-link btn-toggle-replies"
                    data-comment-id="{{ $comment->id }}"
                    data-page="1">
                View {{ $replyCount }} repl{{ $replyCount > 1 ? 'ies' : 'y' }}
            </button>
            @endif

            <div class="replies-list-modern replies-list"
                 id="repliesFor{{ $comment->id }}"
                 data-loaded-page="0"
                 style="display:none;"></div>

            {{-- “Load more” 占位 --}}
            <button type="button"
                    class="comment-action-link btn-load-more-replies"
                    data-comment-id="{{ $comment->id }}"
                    data-next-page="2"
                    style="display:none;">
                Load more replies
            </button>
        </div>
    </div>
</div>
@else
{{-- 回复（子评论） --}}
<div class="reply-item-modern reply-item" data-comment-id="{{ $comment->id }}">
    <img src="{{ $avatar }}" alt="{{ $authorName }}" class="reply-avatar-modern">

    <div class="reply-body-modern">
        <div class="comment-mainline-modern">
            <div class="reply-content-modern">
                <div class="comment-meta-modern">
                    <span class="reply-author-modern">{{ $authorName }}</span>
                    <span class="reply-time-modern">{{ $timeText }}</span>
                </div>

                <div class="comment-text-modern">
                    {{-- 只有真正“回复某个人”的子回复才显示 @ --}}
                    @if ($comment->replyTo)
                    <span class="comment-mention">
                        @ {{ $comment->replyTo->name }}
                    </span>
                    @endif

                    {{ $comment->content }}
                </div>

                @if(isset($comment->media) && $comment->media->count() > 0)
                <div class="comment-media-grid">
                    @foreach($comment->media as $m)
                    @if(($m->type ?? 'image') === 'video')
                    <video class="comment-media-item" controls preload="metadata">
                        <source src="{{ $m->url }}" type="{{ $m->mime_type }}">
                    </video>
                    @else
                    <img class="comment-media-item"
                         src="{{ $m->url }}"
                         alt="comment media"
                         loading="lazy">
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            @if($canManage)
            <div class="comment-menu-wrap">
                <button type="button"
                        class="btn-comment-menu-modern comment-menu-btn"
                        aria-label="Comment menu"
                        data-menu-for="{{ $comment->id }}">
                    <i class="bi bi-three-dots"></i>
                </button>

                <div class="comment-menu-dropdown" id="commentMenu{{ $comment->id }}">
                    <button type="button"
                            class="comment-menu-item edit-comment-btn"
                            data-comment-id="{{ $comment->id }}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button"
                            class="comment-menu-item delete-comment-btn"
                            data-comment-id="{{ $comment->id }}">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
            @endif
        </div>

        {{-- actions：子回复也可以 Like + Reply --}}
        @php
        $likedByMe = auth()->check() ? $comment->isLikedBy(auth()->user()) : false;
        @endphp
        <div class="comment-actions-modern">
            <button type="button"
                    class="comment-action-link comment-like-btn {{ $likedByMe ? 'active' : '' }}"
                    data-comment-id="{{ $comment->id }}"
                    data-requires-auth="true">
                @if($likedByMe) Liked @else Like @endif
                @if($comment->likes_count > 0)
                {{ $comment->likes_count }}
                @endif
            </button>

            {{-- 子回复的 Reply：这里才带 user-id，用来标记“回复谁”，从而显示 @ --}}
            <button type="button"
                    class="comment-action-link btn-reply btn-reply-modern"
                    data-comment-id="{{ $comment->id }}"
                    data-reply-to-user-id="{{ $comment->user_id }}"
                    data-reply-to-user-name="{{ $authorName }}"
                    data-requires-auth="true">
                Reply
            </button>
        </div>

        {{-- 子回复自己的 reply form（id 规则与顶层一致） --}}
        @include('forums.partials.reply_form')
    </div>
</div>
@endif
