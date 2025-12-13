/**
 * Forum Helper Functions
 * Common utilities for forum functionality
 */

const ForumHelper = {
    /**
     * Show toast notification
     */
    showToast(type, message, duration = 3000) {
        const bgColor = type === 'success' ? 'bg-success' : 
                       type === 'warning' ? 'bg-warning' : 
                       type === 'info' ? 'bg-info' : 'bg-danger';
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'warning' ? 'exclamation-triangle' :
                    type === 'info' ? 'info-circle' : 'x-circle';
        
        const toast = $(`
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast ${bgColor} text-white" role="alert">
                    <div class="toast-body">
                        <i class="bi bi-${icon} me-2"></i>${message}
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast.find('.toast')[0], { delay: duration });
        bsToast.show();
        
        toast.find('.toast').on('hidden.bs.toast', function() {
            toast.remove();
        });
    },

    /**
     * Show confirmation modal
     */
    showConfirmation(title, message, onConfirm) {
        const modal = $(`
            <div class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${message}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        modal.find('#confirmBtn').on('click', function() {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            bootstrap.Modal.getInstance(modal[0]).hide();
        });

        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });

        $('body').append(modal);
        new bootstrap.Modal(modal[0]).show();
    },

    /**
     * Format number with K, M suffix
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    },

    /**
     * Truncate text
     */
    truncate(text, length = 100) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    },

    /**
     * Copy to clipboard
     */
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('success', 'Copied to clipboard!');
            });
        } else {
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showToast('success', 'Copied to clipboard!');
        }
    },

    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Format date
     */
    formatDate(date) {
        const d = new Date(date);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return d.toLocaleDateString('en-US', options);
    },

    /**
     * Time ago format
     */
    timeAgo(date) {
        const now = new Date();
        const past = new Date(date);
        const seconds = Math.floor((now - past) / 1000);

        let interval = Math.floor(seconds / 31536000);
        if (interval >= 1) return interval + (interval === 1 ? ' year ago' : ' years ago');

        interval = Math.floor(seconds / 2592000);
        if (interval >= 1) return interval + (interval === 1 ? ' month ago' : ' months ago');

        interval = Math.floor(seconds / 86400);
        if (interval >= 1) return interval + (interval === 1 ? ' day ago' : ' days ago');

        interval = Math.floor(seconds / 3600);
        if (interval >= 1) return interval + (interval === 1 ? ' hour ago' : ' hours ago');

        interval = Math.floor(seconds / 60);
        if (interval >= 1) return interval + (interval === 1 ? ' minute ago' : ' minutes ago');

        return 'Just now';
    }
};

// Export for use in other scripts
window.ForumHelper = ForumHelper;