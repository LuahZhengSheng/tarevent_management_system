/**
 * Forum Show Page JavaScript
 * TAREvent Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    initForumShowPage();
});

// Check if user is authenticated
const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.getAttribute('content') === 'true';

function initForumShowPage() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const postId = getPostId();

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
    initDeletePost(postId, csrfToken);
    initReadingProgress();
    initSmoothScroll();
    attachMediaLightbox();
}

/**
 * Get Post ID from page
 */
function getPostId() {
    const likeBtn = document.getElementById('likeBtn');
    return likeBtn ? likeBtn.getAttribute('data-post-id') : null;
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
window.closeLoginPrompt = function() {
    const modal = document.getElementById('loginPromptModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Image Modal
 */
function initImageModal() {
    const imageModal = document.getElementById('imageModal');
    if (!imageModal) return;

    imageModal.addEventListener('show.bs.modal', function(e) {
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
    if (!likeBtn) return;

    // Only proceed if authenticated
    if (!isAuthenticated) return;

    likeBtn.addEventListener('click', async function() {
        if (this.disabled) return;
        
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

/**
 * Share Button
 */
function initShareButton() {
    const shareBtn = document.getElementById('shareBtn');
    if (!shareBtn) return;

    shareBtn.addEventListener('click', async function() {
        const url = window.location.href;
        const title = document.querySelector('.post-title')?.textContent || 'Check this out!';

        // Try native share API
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
    if (!isAuthenticated) return;

    // Submit comment
    const submitBtn = document.getElementById('submitCommentBtn');
    const commentInput = document.getElementById('commentInput');

    if (submitBtn && commentInput) {
        // Auto-resize textarea
        commentInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        submitBtn.addEventListener('click', () => submitComment(postId, csrfToken));
        
        // Submit on Ctrl+Enter
        commentInput.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                submitComment(postId, csrfToken);
            }
        });
    }

    // Reply buttons
    document.addEventListener('click', function(e) {
        if (!isAuthenticated) return;

        if (e.target.closest('.btn-reply')) {
            const commentId = e.target.closest('.btn-reply').getAttribute('data-comment-id');
            toggleReplyForm(commentId);
        }

        if (e.target.closest('.cancel-reply-btn')) {
            const commentId = e.target.closest('.cancel-reply-btn').getAttribute('data-comment-id');
            hideReplyForm(commentId);
        }

        if (e.target.closest('.submit-reply-btn')) {
            const commentId = e.target.closest('.submit-reply-btn').getAttribute('data-comment-id');
            submitReply(postId, commentId, csrfToken);
        }

        if (e.target.closest('.delete-comment-btn')) {
            const commentId = e.target.closest('.delete-comment-btn').getAttribute('data-comment-id');
            deleteComment(commentId, csrfToken);
        }
    });
}

/**
 * Submit Comment
 */
async function submitComment(postId, csrfToken) {
    const commentInput = document.getElementById('commentInput');
    const submitBtn = document.getElementById('submitCommentBtn');
    const content = commentInput.value.trim();

    if (!content) {
        showToast('warning', 'Please enter a comment');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';

    try {
        const response = await fetch(`/forums/posts/${postId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content })
        });

        const data = await response.json();

        if (data.success) {
            commentInput.value = '';
            commentInput.style.height = 'auto';
            
            // Remove no comments message
            const noCommentsMsg = document.getElementById('noCommentsMessage');
            if (noCommentsMsg) {
                noCommentsMsg.remove();
            }

            // Add new comment
            const commentsList = document.getElementById('commentsList');
            if (commentsList && data.html) {
                commentsList.insertAdjacentHTML('afterbegin', data.html);
            }

            // Update counts
            updateCommentCount(data.total_comments);
            
            showToast('success', 'Comment posted successfully!');
        }
    } catch (error) {
        console.error('Comment error:', error);
        showToast('error', 'Failed to post comment');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send-fill"></i><span>Post Comment</span>';
    }
}

/**
 * Toggle Reply Form
 */
function toggleReplyForm(commentId) {
    const replyForm = document.getElementById(`replyInput${commentId}`);
    if (!replyForm) return;

    const isVisible = replyForm.style.display !== 'none';
    
    // Hide all reply forms
    document.querySelectorAll('.reply-form').forEach(form => {
        form.style.display = 'none';
    });

    if (!isVisible) {
        replyForm.style.display = 'flex';
        const input = document.getElementById(`replyText${commentId}`);
        if (input) {
            input.focus();
        }
    }
}

/**
 * Hide Reply Form
 */
function hideReplyForm(commentId) {
    const replyForm = document.getElementById(`replyInput${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'none';
        const input = document.getElementById(`replyText${commentId}`);
        if (input) {
            input.value = '';
        }
    }
}

/**
 * Submit Reply
 */
async function submitReply(postId, commentId, csrfToken) {
    const replyInput = document.getElementById(`replyText${commentId}`);
    const content = replyInput.value.trim();

    if (!content) {
        showToast('warning', 'Please enter a reply');
        return;
    }

    try {
        const response = await fetch(`/forums/posts/${postId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                content,
                parent_id: commentId
            })
        });

        const data = await response.json();

        if (data.success) {
            replyInput.value = '';
            hideReplyForm(commentId);

            // Add reply to list
            const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
            if (commentItem && data.html) {
                let repliesList = commentItem.querySelector('.replies-list');
                if (!repliesList) {
                    repliesList = document.createElement('div');
                    repliesList.className = 'replies-list';
                    const commentBody = commentItem.querySelector('.comment-body');
                    commentBody.appendChild(repliesList);
                }
                repliesList.insertAdjacentHTML('beforeend', data.html);
            }

            // Update counts
            updateCommentCount(data.total_comments);
            
            showToast('success', 'Reply posted successfully!');
        }
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
            if (commentItem) {
                commentItem.style.opacity = '0';
                commentItem.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    commentItem.remove();
                    
                    // Check if no comments left
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

            // Update counts
            updateCommentCount(data.total_comments);
            
            showToast('success', 'Comment deleted');
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
        deleteBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function() {
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
        anchor.addEventListener('click', function(e) {
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
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
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
    const toast = new bootstrap.Toast(toastElement, { delay: duration });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        container.remove();
    });
}

/**
 * Attach click events to media items for lightbox
 * Uses global openLightbox() from media-lightbox.js
 */
function attachMediaLightbox() {
    const mediaGrid = document.getElementById('mediaGrid');
    if (!mediaGrid) return;
    
    // 获取所有 media items（包括隐藏的）
    const mediaItems = mediaGrid.querySelectorAll('.media-item');
    
    // 只给可见的前 5 个添加点击事件
    const visibleItems = Array.from(mediaItems).filter((item, index) => index < 5);
    
    visibleItems.forEach((item, displayIndex) => {
        item.addEventListener('click', function(e) {
            // 获取实际的 index（从 data-index）
            const actualIndex = parseInt(item.getAttribute('data-index'));
            
            // 如果点击的是第 5 个 item（显示 +N 的），从第 5 个开始
            // 否则从点击的 index 开始
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

// Console message
console.log('%c🎉 Forum Show Page Loaded', 'color: #2563eb; font-size: 16px; font-weight: bold;');
