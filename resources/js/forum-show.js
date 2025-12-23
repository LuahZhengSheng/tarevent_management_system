/**
 * Forum Show Page JavaScript
 * TAREvent Management System
 */

document.addEventListener('DOMContentLoaded', function () {
    initForumShowPage();
});

// Check if user is authenticated
const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.getAttribute('content') === 'true';

let currentPostId = null;

function initForumShowPage() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const postId = getPostId();
    currentPostId = postId;

    if (!postId) {
        console.error('Post ID not found');
        return;
    }

    // Initialize features
    initImageModal();
    initShareButton();
    initAuthProtection();
    initLikeButton(postId, csrfToken);
    initCommentSystem(postId, csrfToken);
    initCommentSort(postId);
    initCommentLikeButtons(csrfToken);
    initDeletePost(postId, csrfToken);
    initReadingProgress();
    initSmoothScroll();
    initCommentComposerUI();
    initStickyCommentComposer();
    initSaveButton(postId, csrfToken);
    initReportButton(postId, csrfToken);
    initComposerCameraButtons();
    attachMediaLightbox();
}

/**
 * Get Post ID from page
 */
function getPostId() {
    const likeBtn = document.getElementById('likeBtn');
    return likeBtn ? likeBtn.getAttribute('data-post-slug') : null;
}

/**
 * Auth Protection - Show login prompt for guest users
 */
function initAuthProtection() {
    // Create login prompt modal
    createLoginModal();

    // Protect comment input
    const commentInput = document.getElementById('commentInput');
    if (commentInput && !isAuthenticated) {
        commentInput.addEventListener('click', showLoginPrompt);
        commentInput.addEventListener('focus', (e) => {
            e.preventDefault();
            showLoginPrompt();
            commentInput.blur();
        });
    }

    // Protect submit button
    const submitBtn = document.getElementById('submitCommentBtn');
    if (submitBtn && !isAuthenticated) {
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showLoginPrompt();
        });
    }

    // Protect action buttons with data-requires-auth
    document.querySelectorAll('[data-requires-auth="true"]').forEach(btn => {
        if (!isAuthenticated) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                showLoginPrompt();
            });
        }
    });

    // Protect reply buttons
    document.addEventListener('click', (e) => {
        const replyBtn = e.target.closest('.btn-reply');
        if (replyBtn && !isAuthenticated) {
            e.preventDefault();
            showLoginPrompt();
        }
    });
}

/**
 * Create Login Modal
 */
function createLoginModal() {
    const modalHTML = `
        <div id="loginPromptModal" class="login-prompt-modal">
            <div class="login-prompt-content">
                <button class="btn-close-modal" onclick="closeLoginPrompt()">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="login-prompt-icon">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h3 class="login-prompt-title">Login Required</h3>
                <p class="login-prompt-text">
                    You need to be logged in to interact with posts and comments. 
                    Join our community today!
                </p>
                <div class="login-prompt-actions">
                    <button class="btn-login-primary" onclick="window.location.href='/login'">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </button>
                    <button class="btn-login-secondary" onclick="closeLoginPrompt()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Close on backdrop click
    const modal = document.getElementById('loginPromptModal');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeLoginPrompt();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeLoginPrompt();
        }
    });
}

/**
 * Show Login Prompt
 */
function showLoginPrompt() {
    const modal = document.getElementById('loginPromptModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close Login Prompt
 */
window.closeLoginPrompt = function () {
    const modal = document.getElementById('loginPromptModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

/**
 * Image Modal
 */
function initImageModal() {
    const imageModal = document.getElementById('imageModal');
    if (!imageModal)
        return;

    imageModal.addEventListener('show.bs.modal', function (e) {
        const trigger = e.relatedTarget;
        const imageSrc = trigger.getAttribute('data-image');
        const modalImage = document.getElementById('modalImage');
        if (modalImage) {
            modalImage.src = imageSrc;
        }
    });
}

/**
 * Like Button
 */
function initLikeButton(postId, csrfToken) {
    const likeBtn = document.getElementById('likeBtn');
    if (!likeBtn)
        return;

    // Only proceed if authenticated
    if (!isAuthenticated)
        return;

    likeBtn.addEventListener('click', async function () {
        if (this.disabled)
            return;

        this.disabled = true;
        const icon = this.querySelector('i');
        const text = this.querySelector('span');

        try {
            const response = await fetch(`/forums/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Update like count
                const likesCount = document.getElementById('likesCount');
                if (likesCount) {
                    likesCount.textContent = formatNumber(data.likes_count);
                }

                // Toggle button state
                if (data.liked) {
                    this.classList.add('active');
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    text.textContent = 'Liked';

                    // Animate
                    icon.style.animation = 'heartBeat 0.5s ease';
                    setTimeout(() => icon.style.animation = '', 500);
                } else {
                    this.classList.remove('active');
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    text.textContent = 'Like';
                }

                showToast('success', data.liked ? 'Post liked!' : 'Like removed');
            }
        } catch (error) {
            console.error('Like error:', error);
            showToast('error', 'Failed to update like status');
        } finally {
            this.disabled = false;
        }
    });
}

function initCommentComposerUI() {
    const mediaInput = document.getElementById('commentMedia');
    const preview = document.getElementById('commentMediaPreview');
    const commentInput = document.getElementById('commentInput');
    const wrapper = document.querySelector('.comment-input-wrapper-modern');

    // 预览上传的媒体
    if (mediaInput && preview) {
        mediaInput.addEventListener('change', () => {
            renderMediaPreview(mediaInput.files, preview);
        });
    }

    // 只有点击 / 聚焦评论输入框时才显示工具栏
    if (commentInput && wrapper) {
        const activate = () => {
            wrapper.classList.add('is-active');
        };

        commentInput.addEventListener('focus', activate);
        commentInput.addEventListener('click', activate);
    }
}

/**
 * Sticky Comment Composer
 */
function initStickyCommentComposer() {
    const commentsSection = document.getElementById('commentsSection');
    const shell = document.getElementById('commentComposerShell');
    const composer = document.getElementById('commentFormTop');
    const sentinel = document.getElementById('commentComposerSentinel');
    const stickyWrap = document.getElementById('commentFormStickyWrap');
    const stickyInner = document.getElementById('commentFormStickyInner');
    if (!commentsSection || !shell || !composer || !sentinel || !stickyWrap || !stickyInner)
        return;

    // anchor for restoring composer to its original place (right after sentinel)
    let anchor = document.getElementById('commentComposerAnchor');
    if (!anchor) {
        anchor = document.createElement('div');
        anchor.id = 'commentComposerAnchor';
        anchor.style.height = '1px';
        shell.insertBefore(anchor, composer); // keep a stable restore point
    }

    let inCommentsView = false;
    let topAreaVisible = true;
    let isStuck = false;

    const stick = () => {
        if (isStuck)
            return;
        isStuck = true;

        stickyInner.appendChild(composer);
        stickyWrap.classList.add('is-active');
        stickyWrap.setAttribute('aria-hidden', 'false');
    };

    const unstick = () => {
        if (!isStuck)
            return;
        isStuck = false;

        shell.insertBefore(composer, anchor.nextSibling);
        stickyWrap.classList.remove('is-active');
        stickyWrap.setAttribute('aria-hidden', 'true');
    };

    const update = () => {
        if (inCommentsView && !topAreaVisible)
            stick();
        else
            unstick();
    };

    const ioSection = new IntersectionObserver((entries) => {
        const entry = entries[0];
        if (!entry)
            return;

        inCommentsView = entry.isIntersecting;
        update();
    }, {threshold: 0});

    const ioSentinel = new IntersectionObserver((entries) => {
        const entry = entries[0];
        if (!entry)
            return;

        topAreaVisible = entry.isIntersecting && entry.intersectionRatio > 0;
        update();
    }, {
        threshold: [0, 0.01],
        rootMargin: '0px 0px -20px 0px'
    });

    ioSection.observe(commentsSection);
    ioSentinel.observe(sentinel);

    update();
}

function initComposerCameraButtons() {
    const camBtn = document.getElementById('commentCameraBtn');
    const mediaInput = document.getElementById('commentMedia');
    if (camBtn && mediaInput) {
        camBtn.addEventListener('click', (e) => {
            if (!isAuthenticated)
                return;
            e.preventDefault();
            mediaInput.click();
        });
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.reply-camera-btn');
        if (!btn)
            return;
        if (!isAuthenticated)
            return;

        e.preventDefault();
        const id = btn.getAttribute('data-reply-camera-for');
        const input = document.getElementById(`replyMedia${id}`);
        if (input)
            input.click();
    });
}

// Emoji popup 逻辑
const emojiList = [
    '😀', '😃', '😄', '😁', '😆', '🥹', '😍', '🥰', '😘', '😅', '🤣', '😂', '😊',
    '😇', '🙂', '🙃', '😉', '😌', '😜', '😝', '🤩', '🥳', '😎', '🤔', '😏', '🤤',
    '😭', '😡', '🤬', '😱', '😳', '😴', '🤯', '❤️', '🧡', '💛', '💚', '💙', '💜',
    '👍', '👎', '👏', '🙌', '🙏', '🔥', '💯', '🎉', '✨', '⚡', '⭐'
];

let openEmojiPanel = null;
let openEmojiButton = null;

document.addEventListener('click', (e) => {
    const emojiButton = e.target.closest('.btn-emoji-modern');

    if (e.target.closest('.emoji-panel')) {
        return;
    }

    if (emojiButton) {
        if (!isAuthenticated)
            return;

        const targetId = emojiButton.getAttribute('data-emoji-target');
        const input = document.getElementById(targetId);
        if (!input)
            return;

        if (openEmojiPanel && openEmojiButton === emojiButton) {
            openEmojiPanel.remove();
            openEmojiPanel = null;
            openEmojiButton = null;
            return;
        }

        if (openEmojiPanel) {
            openEmojiPanel.remove();
            openEmojiPanel = null;
            openEmojiButton = null;
        }

        const panel = document.createElement('div');
        panel.className = 'emoji-panel';
        panel.style.cssText = `
            position:absolute;
            z-index:99999;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:10px;
            padding:8px;
            display:flex;
            gap:6px;
            flex-wrap:wrap;
            max-width:220px;
            max-height:180px;
            overflow-y:auto;
            box-shadow:0 10px 30px rgba(0,0,0,0.12);
        `;

        emojiList.forEach((ch) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = ch;
            b.style.cssText = 'border:0;background:transparent;font-size:18px;cursor:pointer;';
            b.addEventListener('click', () => insertAtCursor(input, ch));
            panel.appendChild(b);
        });

        const rect = emojiButton.getBoundingClientRect();
        panel.style.left = rect.left + window.scrollX + 'px';
        panel.style.top = rect.bottom + window.scrollY + 6 + 'px';

        document.body.appendChild(panel);
        openEmojiPanel = panel;
        openEmojiButton = emojiButton;

        return;
    }

    if (openEmojiPanel && !e.target.closest('.emoji-panel')) {
        openEmojiPanel.remove();
        openEmojiPanel = null;
        openEmojiButton = null;
    }
});

function insertAtCursor(input, text) {
    const start = input.selectionStart ?? input.value.length;
    const end = input.selectionEnd ?? input.value.length;

    input.value = input.value.slice(0, start) + text + input.value.slice(end);
    input.focus();

    const pos = start + text.length;
    input.selectionStart = pos;
    input.selectionEnd = pos;
}

function renderMediaPreview(files, container) {
    container.innerHTML = '';
    if (!files || files.length === 0)
        return;

    Array.from(files).forEach(file => {
        const url = URL.createObjectURL(file);
        if ((file.type || '').startsWith('video/')) {
            const v = document.createElement('video');
            v.src = url;
            v.controls = true;
            v.style.cssText = 'max-width:140px;border-radius:10px;margin-right:8px;';
            container.appendChild(v);
        } else {
            const img = document.createElement('img');
            img.src = url;
            img.style.cssText = 'max-width:140px;border-radius:10px;margin-right:8px;';
            container.appendChild(img);
        }
    });
}

function initSaveButton(postId, csrfToken) {
    const btn = document.getElementById('saveBtn');
    if (!btn)
        return;
    if (!isAuthenticated)
        return;

    btn.addEventListener('click', async function () {
        const icon = this.querySelector('i');
        const text = this.querySelector('span');

        try {
            const res = await fetch(`/forums/posts/${postId}/save`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}
            });
            const data = await res.json();
            if (!data.success)
                throw new Error(data.message || 'save failed');

            if (data.saved) {
                icon.classList.remove('bi-bookmark');
                icon.classList.add('bi-bookmark-fill');
                text.textContent = 'Saved';
            } else {
                icon.classList.remove('bi-bookmark-fill');
                icon.classList.add('bi-bookmark');
                text.textContent = 'Save';
            }
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update save status');
        }
    });
}

function initReportButton(postId, csrfToken) {
    const btn = document.getElementById('reportPostBtn');
    if (!btn)
        return;
    if (!isAuthenticated)
        return;

    btn.addEventListener('click', async function () {
        const reason = prompt('Reason (spam/harassment/other):');
        if (reason === null)
            return;
        const details = prompt('Details (optional):') || '';

        try {
            const res = await fetch(`/forums/posts/${postId}/report`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({reason, details})
            });

            const data = await res.json();
            if (!data.success)
                throw new Error(data.message || 'report failed');
            showToast('success', 'Report submitted');
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to submit report');
        }
    });
}

/**
 * Share Button
 */
function initShareButton() {
    const shareBtn = document.getElementById('shareBtn');
    if (!shareBtn)
        return;

    shareBtn.addEventListener('click', async function () {
        const url = window.location.href;
        const title = document.querySelector('.post-title')?.textContent || 'Check this out!';

        if (navigator.share) {
            try {
                await navigator.share({
                    title: title,
                    url: url
                });
                showToast('success', 'Shared successfully!');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    copyToClipboard(url);
                }
            }
        } else {
            copyToClipboard(url);
        }
    });
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('success', 'Link copied to clipboard!');
    }).catch(() => {
        showToast('error', 'Failed to copy link');
    });
}

/**
 * Comment System
 */
function initCommentSystem(postId, csrfToken) {
    if (!isAuthenticated)
        return;

    const submitBtn = document.getElementById('submitCommentBtn');
    const commentInput = document.getElementById('commentInput');

    // 顶层评论：输入内容才可 Post
    if (submitBtn && commentInput) {
        commentInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';

            const hasText = this.value.trim().length > 0;
            submitBtn.disabled = !hasText;
        });

        submitBtn.disabled = !(commentInput.value && commentInput.value.trim().length > 0);

        submitBtn.addEventListener('click', () => {
            submitComment(postId, csrfToken);
        });
    }

    if (commentInput) {
        commentInput.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                submitComment(postId, csrfToken);
            }
        });
    }

    // Reply 输入框：输入内容才可 Send
    document.addEventListener('input', (e) => {
        const replyInput = e.target.closest('.reply-input-modern');
        if (!replyInput)
            return;

        const wrapper = replyInput.closest('.reply-form');
        if (!wrapper)
            return;

        const sendBtn = wrapper.querySelector('.submit-reply-btn');
        if (!sendBtn)
            return;

        const hasText = replyInput.value.trim().length > 0;
        sendBtn.disabled = !hasText;
    });

    document.addEventListener('click', async function (e) {
        /**
         * View / Hide replies（统一状态）
         */
        const toggleBtn = e.target.closest('.btn-toggle-replies');
        if (toggleBtn) {
            const rootCommentId = toggleBtn.getAttribute('data-comment-id');
            const list = document.getElementById('repliesFor' + rootCommentId);
            if (!list)
                return;

            // 是否已经加载过至少一页
            const loadedPage = parseInt(list.getAttribute('data-loaded-page') || '0', 10);
            // 统一用 data-is-open 标记是否展开
            const isOpen = list.dataset.isOpen === 'true';

            if (loadedPage > 0) {
                const loadMoreBtn = document.querySelector(
                        `.btn-load-more-replies[data-comment-id="${rootCommentId}"]`
                        );

                if (isOpen) {
                    // 当前是展开 → 收起
                    list.style.display = 'none';
                    list.dataset.isOpen = 'false';

                    if (loadMoreBtn) {
                        loadMoreBtn.style.display = 'none';
                    }

                    // 使用最新 reply 数量恢复 View 文案
                    const count = parseInt(toggleBtn.dataset.replyCount || '0', 10);
                    if (count > 0) {
                        toggleBtn.textContent = count === 1
                                ? 'View 1 reply'
                                : `View ${count} replies`;
                    } else {
                        toggleBtn.textContent = 'View replies';
                    }
                } else {
                    // 当前是收起 → 展开
                    list.style.display = 'block';
                    list.dataset.isOpen = 'true';

                    const loadMoreBtn2 = document.querySelector(
                            `.btn-load-more-replies[data-comment-id="${rootCommentId}"]`
                            );
                    if (loadMoreBtn2 && loadMoreBtn2.dataset.hasMore === 'true') {
                        loadMoreBtn2.style.display = 'inline-block';
                    }

                    toggleBtn.textContent = 'Hide replies';
                }

                return;
            }

            // 第一次点击：通过 AJAX 加载第一页 replies，加载完后保持展开 + Hide replies 文案
            toggleBtn.disabled = true;
            try {
                await loadRepliesPage(currentPostId, rootCommentId, 1);

                list.style.display = 'block';
                list.dataset.isOpen = 'true';
                toggleBtn.textContent = 'Hide replies';
            } catch (err) {
                console.error(err);
                showToast('error', 'Failed to load replies');
            } finally {
                toggleBtn.disabled = false;
            }
            return;
        }

        /**
         * Load more replies
         */
        const loadMoreBtn = e.target.closest('.btn-load-more-replies');
        if (loadMoreBtn) {
            const commentId = loadMoreBtn.getAttribute('data-comment-id');
            const nextPage = parseInt(loadMoreBtn.getAttribute('data-next-page') || '2', 10);
            loadMoreBtn.disabled = true;
            try {
                await loadRepliesPage(currentPostId, commentId, nextPage);
            } catch (err) {
                console.error(err);
                showToast('error', 'Failed to load more replies');
            } finally {
                loadMoreBtn.disabled = false;
            }
            return;
        }

        // Reply（打开 reply form + 设置 @ 信息）
        const replyBtn = e.target.closest('.btn-reply');
        if (replyBtn) {
            if (!isAuthenticated)
                return;

            const commentId = replyBtn.getAttribute('data-comment-id');
            const replyToUserId = replyBtn.getAttribute('data-reply-to-user-id');
            const replyToUserName = replyBtn.getAttribute('data-reply-to-user-name');

            toggleReplyForm(commentId, replyToUserId, replyToUserName);
            return;
        }

        // Cancel reply
        const cancelBtn = e.target.closest('.cancel-reply-btn');
        if (cancelBtn) {
            const commentId = cancelBtn.getAttribute('data-comment-id');
            hideReplyForm(commentId);
            return;
        }

        // Submit reply
        const submitReplyBtn = e.target.closest('.submit-reply-btn');
        if (submitReplyBtn) {
            if (!isAuthenticated)
                return;

            const commentId = submitReplyBtn.getAttribute('data-comment-id');
            submitReply(postId, commentId, csrfToken);
            return;
        }

        // Delete from menu
        const del = e.target.closest('.delete-comment-btn');
        if (del) {
            if (!isAuthenticated)
                return;

            const commentId = del.getAttribute('data-comment-id');
            deleteComment(commentId, csrfToken);
            return;
        }

        // Menu open/close
        const menuBtn = e.target.closest('.comment-menu-btn');
        if (menuBtn) {
            e.preventDefault();
            const id = menuBtn.getAttribute('data-menu-for');
            toggleCommentMenu(id);
            return;
        }

        // Edit
        const editBtn = e.target.closest('.edit-comment-btn');
        if (editBtn) {
            if (!isAuthenticated)
                return;

            const commentId = editBtn.getAttribute('data-comment-id');
            openEditCommentPrompt(commentId, csrfToken);
            return;
        }

        // Close menus on outside click
        if (!e.target.closest('.comment-menu-wrap')) {
            closeAllCommentMenus();
        }
    });
}


/**
 * Sort comments: most recent / most popular
 */
function initCommentSort(postId) {
    const trigger = document.getElementById('commentsSortTrigger');
    const menu = document.getElementById('commentsSortMenu');
    const currentBtn = trigger?.querySelector('.comments-sort-current');
    const options = menu?.querySelectorAll('.comments-sort-option');
    const list = document.getElementById('commentsList');
    if (!trigger || !menu || !currentBtn || !options || !list) return;

    // 打开/关闭菜单
    currentBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('is-open');
    });

    // 点击选项
    options.forEach((opt) => {
        opt.addEventListener('click', async (e) => {
            e.stopPropagation();
            const sort = opt.getAttribute('data-sort');
            if (!sort) return;

            // UI: 更新 active / 文案
            options.forEach(o => o.classList.remove('is-active'));
            opt.classList.add('is-active');
            currentBtn.querySelector('span').textContent =
                sort === 'popular' ? 'Most popular' : 'Most recent';
            currentBtn.dataset.currentSort = sort;
            menu.classList.remove('is-open');

            // 数据：请求排序列表
            try {
                list.classList.add('is-loading');
                const res = await fetch(`/forums/posts/${postId}/comments/top-level?sort=${sort}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Failed to load comments');
                list.innerHTML = data.html || '';
            } catch (err) {
                console.error(err);
                showToast('error', 'Failed to sort comments');
            } finally {
                list.classList.remove('is-loading');
            }
        });
    });

    // 点击页面其他地方关闭菜单
    document.addEventListener('click', () => {
        menu.classList.remove('is-open');
    });
}


/**
 * Load one page of replies for a top-level comment
 */
async function loadRepliesPage(postId, commentId, page) {
    const list = document.getElementById('repliesFor' + commentId);
    const loadMoreBtn = document.querySelector(
            `.btn-load-more-replies[data-comment-id="${commentId}"]`
            );
    const toggleBtn = document.querySelector(
            `.btn-toggle-replies[data-comment-id="${commentId}"]`
            );

    if (!list)
        return;

    const res = await fetch(`/forums/posts/${postId}/comments/${commentId}/replies?page=${page}`, {
        headers: {'Accept': 'application/json'},
    });
    const data = await res.json();
    if (!data.success) {
        throw new Error(data.message || 'Failed to load replies');
    }

    if (data.html) {
        list.insertAdjacentHTML('beforeend', data.html);
    }

    list.setAttribute('data-loaded-page', data.current_page.toString());

    // 后端返回 replies_count（建议在 CommentController@listReplies 里加上）
    // 没有的话可以把这一块注释掉
    if (typeof data.replies_count === 'number' && toggleBtn) {
        const n = data.replies_count;
        toggleBtn.dataset.replyCount = String(n);

        // 如果当前是收起状态（isOpen = false），就更新 View X replies 文案
        const isOpen = list.dataset.isOpen === 'true';
        if (!isOpen) {
            if (n > 0) {
                toggleBtn.textContent = n === 1 ? 'View 1 reply' : `View ${n} replies`;
            } else {
                toggleBtn.textContent = 'View replies';
            }
        }
        // 如果当前是展开状态，按钮保持 Hide replies，不改文本
    }

    // hasMore 标记给 View / Hide 使用
    const hasMore = data.current_page < data.last_page;
    if (loadMoreBtn) {
        loadMoreBtn.dataset.hasMore = hasMore ? 'true' : 'false';
        if (hasMore && list.dataset.isOpen === 'true') {
            loadMoreBtn.style.display = 'inline-block';
            loadMoreBtn.setAttribute('data-next-page', String(data.current_page + 1));
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
}


/**
 * Toggle Comment Menu
 */
function toggleCommentMenu(commentId) {
    closeAllCommentMenus(commentId);
    const menu = document.getElementById(`commentMenu${commentId}`);
    if (!menu)
        return;
    menu.classList.toggle('is-open');
}

function closeAllCommentMenus(exceptId = null) {
    document.querySelectorAll('.comment-menu-dropdown.is-open').forEach(m => {
        if (exceptId && m.id === `commentMenu${exceptId}`)
            return;
        m.classList.remove('is-open');
    });
}

/**
 * Inline edit comment / reply
 */
async function openEditCommentPrompt(commentId, csrfToken) {
    const item = document.querySelector(`[data-comment-id="${commentId}"]`);
    if (!item)
        return;

    const textEl = item.querySelector('.comment-text-modern, .reply-text-modern');
    if (!textEl)
        return;

    // 防止重复进入编辑状态
    if (item.dataset.editing === 'true')
        return;
    item.dataset.editing = 'true';

    const originalHtml = textEl.innerHTML;
    const originalText = textEl.textContent.trim();

    // 构建编辑 UI：textarea + actions
    const wrapper = document.createElement('div');
    wrapper.className = 'comment-edit-wrapper';

    const textarea = document.createElement('textarea');
    textarea.className = 'comment-edit-input';
    textarea.value = originalText;
    textarea.rows = 1;

    const actions = document.createElement('div');
    actions.className = 'comment-edit-actions';

    const saveBtn = document.createElement('button');
    saveBtn.type = 'button';
    saveBtn.className = 'comment-edit-save';
    saveBtn.textContent = 'Save';

    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'comment-edit-cancel';
    cancelBtn.textContent = 'Cancel';

    actions.appendChild(saveBtn);
    actions.appendChild(cancelBtn);

    wrapper.appendChild(textarea);
    wrapper.appendChild(actions);

    textEl.innerHTML = '';
    textEl.appendChild(wrapper);

    // 自动高度
    const autoResize = () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    };
    autoResize();
    textarea.addEventListener('input', autoResize);
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);

    const exitEditing = () => {
        item.dataset.editing = 'false';
    };

    // 取消：恢复原内容
    const handleCancel = () => {
        textEl.innerHTML = originalHtml;
        exitEditing();
    };

    cancelBtn.addEventListener('click', handleCancel);

    textarea.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            handleCancel();
        }
    });

    // 保存：调用后端 PUT
    saveBtn.addEventListener('click', async () => {
        const content = textarea.value.trim();
        if (!content) {
            showToast('warning', 'Comment cannot be empty');
            return;
        }

        try {
            const res = await fetch(`/forums/comments/${commentId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({content}),
            });

            const data = await res.json();
            if (!data.success) {
                throw new Error(data.message || 'Edit failed');
            }

            if (data.html) {
                const container = document.createElement('div');
                container.innerHTML = data.html;
                const newNode = container.firstElementChild;
                if (newNode) {
                    item.replaceWith(newNode);
                }
            } else {
                textEl.textContent = content;
                exitEditing();
            }

            showToast('success', 'Comment updated!');
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to edit comment');
        }
    });
}


/**
 * Submit Comment
 */
async function submitComment(postId, csrfToken) {
    const commentInput = document.getElementById('commentInput');
    const mediaInput = document.getElementById('commentMedia');
    const submitBtn = document.getElementById('submitCommentBtn');

    const body = (commentInput?.value || '').trim();
    const files = mediaInput?.files;

    if (!body && (!files || files.length === 0)) {
        showToast('warning', 'Please enter a comment or add media');
        return;
    }

    submitBtn.disabled = true;

    try {
        const fd = new FormData();
        fd.append('content', body);

        if (files && files.length) {
            Array.from(files).forEach(f => fd.append('media', f));
        }

        const response = await fetch(`/forums/posts/${postId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: fd
        });

        const data = await response.json();
        if (!data.success)
            throw new Error(data.message || 'Failed');

        commentInput.value = '';
        if (mediaInput)
            mediaInput.value = '';
        const preview = document.getElementById('commentMediaPreview');
        if (preview)
            preview.innerHTML = '';

        const noCommentsMsg = document.getElementById('noCommentsMessage');
        if (noCommentsMsg)
            noCommentsMsg.remove();

        const commentsList = document.getElementById('commentsList');
        if (commentsList && data.html)
            commentsList.insertAdjacentHTML('afterbegin', data.html);

        updateCommentCount(data.totalComments);
        showToast('success', 'Comment posted!');
    } catch (e) {
        console.error(e);
        showToast('error', 'Failed to post comment');
    } finally {
        submitBtn.disabled = false;
    }
}

/**
 * Comment Like buttons
 */
function initCommentLikeButtons(csrfToken) {
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.comment-like-btn');
        if (!btn)
            return;

        if (!isAuthenticated) {
            e.preventDefault();
            showLoginPrompt();
            return;
        }

        const commentId = btn.getAttribute('data-comment-id');
        if (!commentId)
            return;

        if (btn.disabled)
            return;
        btn.disabled = true;

        try {
            const res = await fetch(`/forums/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await res.json();
            if (!data.success)
                throw new Error(data.message || 'Like failed');

            let text = data.liked ? 'Liked' : 'Like';
            if (typeof data.likes_count === 'number' && data.likes_count > 0) {
                text += ` (${data.likes_count})`;
            }

            btn.textContent = text;

            if (data.liked) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        } catch (error) {
            console.error('Comment like error', error);
            showToast('error', 'Failed to update like status');
        } finally {
            btn.disabled = false;
        }
    });
}

/**
 * Toggle reply form
 */
function toggleReplyForm(commentId, replyToUserId = null, replyToUserName = null) {
    const replyForm = document.getElementById('replyInput' + commentId);
    if (!replyForm)
        return;

    const isVisible = replyForm.style.display === 'flex';

    if (isVisible) {
        hideReplyForm(commentId);
        return;
    }

    replyForm.style.display = 'flex';

    if (replyToUserId) {
        replyForm.dataset.replyToUserId = replyToUserId;
    } else {
        delete replyForm.dataset.replyToUserId;
        delete replyForm.dataset.replyToUserName;
    }
    if (replyToUserName) {
        replyForm.dataset.replyToUserName = replyToUserName;
    }

    const input = document.getElementById('replyText' + commentId);
    if (input) {
        if (replyToUserName) {
            input.placeholder = 'Reply to ' + replyToUserName + '...';
        } else {
            input.placeholder = 'Write a reply...';
        }
        input.focus();
}
}

/**
 * Hide reply form
 */
function hideReplyForm(commentId) {
    const replyForm = document.getElementById('replyInput' + commentId);
    if (replyForm) {
        replyForm.style.display = 'none';
        delete replyForm.dataset.replyToUserId;
        delete replyForm.dataset.replyToUserName;
    }

    const input = document.getElementById('replyText' + commentId);
    if (input) {
        input.value = '';
        input.placeholder = 'Write a reply...';
    }
}

/**
 * Submit Reply（统一插入到顶层 comment 的 replies-list 中）
 */
async function submitReply(postId, commentId, csrfToken) {
    const replyForm = document.getElementById('replyInput' + commentId);
    const replyInput = document.getElementById('replyText' + commentId);
    const mediaInput = document.getElementById('replyMedia' + commentId);

    const content = replyInput?.value.trim();
    const files = mediaInput?.files;
    const replyToUserId = replyForm?.dataset.replyToUserId;

    if (!content && (!files || files.length === 0)) {
        showToast('warning', 'Please enter a reply or add media');
        return;
    }

    try {
        const fd = new FormData();
        fd.append('content', content || '');
        fd.append('parent_id', commentId);

        if (replyToUserId) {
            fd.append('reply_to_user_id', replyToUserId);
        }

        if (files && files.length) {
            Array.from(files).forEach(f => fd.append('media', f));
        }

        const response = await fetch(`/forums/posts/${postId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: fd
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to post reply');
        }

        replyInput.value = '';
        if (mediaInput)
            mediaInput.value = '';
        const sendBtn = replyForm.querySelector('.submit-reply-btn');
        if (sendBtn)
            sendBtn.disabled = true;
        hideReplyForm(commentId);

        let commentItem = document.querySelector('[data-comment-id="' + commentId + '"]');
        if (!commentItem) {
            console.warn('Comment item not found for id', commentId);
            return;
        }

        // 找到顶层 comment（带 .comment-item 的那条）
        let root = commentItem;
        while (root && !root.classList.contains('comment-item')) {
            root = root.parentElement;
        }
        if (!root)
            root = commentItem;

        const repliesWrapper = root.querySelector('.replies-wrapper');
        let repliesList = root.querySelector('.replies-list');
        if (!repliesList && repliesWrapper) {
            repliesList = repliesWrapper.querySelector('.replies-list');
        }
        if (!repliesList) {
            repliesList = document.createElement('div');
            repliesList.className = 'replies-list-modern replies-list';
            repliesList.id = 'repliesFor' + root.getAttribute('data-comment-id');
            repliesList.setAttribute('data-loaded-page', '1');
            repliesList.style.display = 'block';

            if (repliesWrapper) {
                repliesWrapper.appendChild(repliesList);
            } else {
                (root.querySelector('.comment-body-modern') || root).appendChild(repliesList);
            }
        }

        if (data.html) {
            // 插到 repliesList 的最前面
            repliesList.insertAdjacentHTML('afterbegin', data.html);
        }

        // === 更新 / 创建 “View X replies” 按钮（不打乱当前展开状态） ===
        if (data.parentId && typeof data.parentRepliesCount === 'number') {
            const wrapper = root.querySelector('.replies-wrapper');
            if (wrapper) {
                let toggleBtn = wrapper.querySelector('.btn-toggle-replies');

                const n = data.parentRepliesCount;
                const label = n === 1 ? 'View 1 reply' : `View ${n} replies`;

                if (!toggleBtn) {
                    // 第一次有 reply：创建按钮
                    toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.className = 'comment-action-link btn-toggle-replies';
                    toggleBtn.setAttribute('data-comment-id', root.getAttribute('data-comment-id'));
                    wrapper.insertBefore(toggleBtn, repliesList);
                }

                // 记录最新数量，给 Hide → View 时用
                toggleBtn.dataset.replyCount = String(n);

                // 根据当前是否展开，决定要不要改文字
                const isOpen = repliesList.dataset.isOpen === 'true';
                if (!isOpen) {
                    // 收起状态下才显示 “View X replies”
                    toggleBtn.textContent = label;
                } else {
                    // 展开状态下保持 “Hide replies”
                    toggleBtn.textContent = 'Hide replies';
                }
            }
        }

        updateCommentCount(data.totalComments);
        showToast('success', 'Reply posted successfully!');
    } catch (error) {
        console.error('Reply error:', error);
        showToast('error', 'Failed to post reply');
    }
}



/**
 * Delete Comment
 */
async function deleteComment(commentId, csrfToken) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    try {
        const response = await fetch(`/forums/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);

            // 先拿到这条属于哪个顶层 comment
            let root = commentItem;
            while (root && !root.classList.contains('comment-item')) {
                root = root.parentElement;
            }

            if (commentItem) {
                commentItem.style.opacity = '0';
                commentItem.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    commentItem.remove();

                    const commentsList = document.getElementById('commentsList');
                    if (commentsList && !commentsList.querySelector('.comment-item, .reply-item')) {
                        commentsList.innerHTML = `
                            <div class="no-comments" id="noCommentsMessage">
                                <i class="bi bi-chat-left-text"></i>
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                        `;
                    }
                }, 300);
            }

            // === 关键：如果删的是 reply，更新 “View X replies” 按钮 ===
            if (data.parentId && typeof data.parentRepliesCount === 'number' && root) {
                const wrapper = root.querySelector('.replies-wrapper');
                if (wrapper) {
                    const toggleBtn = wrapper.querySelector('.btn-toggle-replies');
                    const repliesList = wrapper.querySelector('.replies-list');

                    const n = data.parentRepliesCount;

                    if (n > 0) {
                        const label = n === 1 ? 'View 1 reply' : `View ${n} replies`;
                        if (toggleBtn) {
                            toggleBtn.textContent = label;
                        } else if (repliesList) {
                            // 极端情况下没有按钮但还有 reply —— 创建一个
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'comment-action-link btn-toggle-replies';
                            btn.setAttribute('data-comment-id', root.getAttribute('data-comment-id'));
                            btn.textContent = label;
                            wrapper.insertBefore(btn, repliesList);
                        }
                    } else {
                        // 没有任何 reply 了：移除按钮 & 隐藏列表
                        if (toggleBtn)
                            toggleBtn.remove();
                        if (repliesList) {
                            repliesList.innerHTML = '';
                            repliesList.style.display = 'none';
                            repliesList.setAttribute('data-loaded-page', '0');
                        }
                    }
                }
            }

            updateCommentCount(data.totalComments ?? data.total_comments);
            showToast('success', 'Comment deleted');
        } else {
            showToast('error', data.message || 'Failed to delete comment');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('error', 'Failed to delete comment');
    }
}

/**
 * Update Comment Count
 */
function updateCommentCount(count) {
    const elements = ['totalComments', 'commentsCount'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = `(${formatNumber(count)})`;
        }
    });
}

/**
 * Delete Post
 */
function initDeletePost(postId, csrfToken) {
    const deleteBtn = document.getElementById('deletePostBtn');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            try {
                const response = await fetch(`/forums/posts/${postId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Post deleted successfully');
                    setTimeout(() => {
                        window.location.href = '/forums';
                    }, 1500);
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('error', 'Failed to delete post');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-trash"></i>Delete Post';
            }
        });
    }
}

/**
 * Reading Progress Bar
 */
function initReadingProgress() {
    const progressBar = document.createElement('div');
    progressBar.id = 'reading-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight - windowHeight;
        const scrolled = window.scrollY;
        const progress = (scrolled / documentHeight) * 100;

        progressBar.style.width = Math.min(progress, 100) + '%';
    });
}

/**
 * Smooth Scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Format Number
 */
function formatNumber(num) {
    // 防止 undefined / 字符串导致 toString 报错
    const n = typeof num === 'number' ? num : parseInt(num ?? '0', 10);
    if (isNaN(n))
        return '0';

    if (n >= 1000000) {
        return (n / 1000000).toFixed(1) + 'M';
    }
    if (n >= 1000) {
        return (n / 1000).toFixed(1) + 'K';
    }
    return n.toString();
}

/**
 * Toast Notification
 */
function showToast(type, message, duration = 3000) {
    const bgColors = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };

    const icons = {
        success: 'check-circle-fill',
        error: 'x-circle-fill',
        warning: 'exclamation-triangle-fill',
        info: 'info-circle-fill'
    };

    const toastHTML = `
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999;">
            <div class="toast ${bgColors[type] || bgColors.info} text-white border-0" role="alert">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-${icons[type] || icons.info} fs-5"></i>
                    <span>${message}</span>
                </div>
            </div>
        </div>
    `;

    const container = document.createElement('div');
    container.innerHTML = toastHTML;
    document.body.appendChild(container);

    const toastElement = container.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, {delay: duration});
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        container.remove();
    });
}

/**
 * Attach click events to media items for lightbox
 */
function attachMediaLightbox() {
    const mediaGrid = document.getElementById('mediaGrid');
    if (!mediaGrid)
        return;

    const mediaItems = mediaGrid.querySelectorAll('.media-item');
    const visibleItems = Array.from(mediaItems).filter((item, index) => index < 5);

    visibleItems.forEach((item, displayIndex) => {
        item.addEventListener('click', function () {
            const actualIndex = parseInt(item.getAttribute('data-index'));

            const startIndex = displayIndex === 4 && mediaItems.length > 5 ? 4 : actualIndex;

            if (typeof openLightbox === 'function') {
                openLightbox(startIndex);
            }
        });
    });

    console.log(`✓ Media Lightbox attached (${visibleItems.length} visible, ${mediaItems.length} total)`);
}

/**
 * Add heartbeat animation
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes heartBeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.3); }
        50% { transform: scale(1.1); }
        75% { transform: scale(1.2); }
    }
`;
document.head.appendChild(style);

console.log('%c🎉 Forum Show Page Loaded', 'color: #2563eb; font-size: 16px; font-weight: bold;');
