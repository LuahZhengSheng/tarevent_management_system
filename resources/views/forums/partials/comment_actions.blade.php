@php
    $likedByMe = auth()->check() ? $comment->isLikedBy(auth()->user()) : false;
@endphp

<div class="comment-actions-modern">
    {{-- Like --}}
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

    {{-- Reply --}}
    <button type="button"
            class="comment-action-link btn-reply btn-reply-modern"
            data-comment-id="{{ $comment->id }}"
            data-reply-to-user-id="{{ $comment->user_id }}"
            data-reply-to-user-name="{{ $authorName }}"
            data-requires-auth="true">
        Reply
    </button>
</div>
