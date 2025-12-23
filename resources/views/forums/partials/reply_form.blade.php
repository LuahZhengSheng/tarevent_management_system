<div class="reply-form-modern reply-form"
     id="replyInput{{ $comment->id }}">
    <img src="{{ auth()->check()
        ? (auth()->user()->profile_photo_url ?? asset('images/default-avatar.png'))
        : asset('images/default-avatar.png') }}"
         alt="You"
         class="reply-avatar-modern">

    <div class="reply-composer-modern">
        <input type="text"
               class="reply-input-modern"
               id="replyText{{ $comment->id }}"
               placeholder="Write a reply...">

        <div class="reply-tools-modern">
            <button type="button"
                    class="reply-tool-btn btn-emoji-modern"
                    data-emoji-target="replyText{{ $comment->id }}"
                    data-requires-auth="true"
                    title="Emoji">
                <i class="bi bi-emoji-smile"></i>
            </button>

            <input type="file"
                   class="reply-media-input"
                   id="replyMedia{{ $comment->id }}"
                   multiple
                   accept="image/*,video/*"
                   hidden>

            <button type="button"
                    class="reply-tool-btn reply-camera-btn"
                    data-reply-camera-for="{{ $comment->id }}"
                    data-requires-auth="true"
                    title="Photo / Video">
                <i class="bi bi-camera"></i>
            </button>

<!--            <button type="button"
                    class="reply-cancel-btn cancel-reply-btn"
                    data-comment-id="{{ $comment->id }}">
                Cancel
            </button>-->

            <button type="button"
                    class="reply-send-btn submit-reply-btn"
                    data-comment-id="{{ $comment->id }}"
                    data-requires-auth="true"
                    title="Send"
                    disabled>
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>
