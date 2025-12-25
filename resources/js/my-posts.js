// resources/js/my-posts.js - Enhanced with AJAX

/**
 * My Posts Page - Enhanced with AJAX updates
 */

(function () {
    'use strict';

    // =====================================================
    // Configuration & Constants
    // =====================================================
    const CONFIG = {
        TOAST_DELAY: 3500,
        ANIMATION_DURATION: 250,
        DEBOUNCE_DELAY: 500
    };

    const TOAST_TYPES = {
        SUCCESS: {bg: 'bg-success', icon: 'check-circle-fill'},
        ERROR: {bg: 'bg-danger', icon: 'x-circle-fill'},
        WARNING: {bg: 'bg-warning', icon: 'exclamation-triangle-fill'},
        INFO: {bg: 'bg-info', icon: 'info-circle-fill'}
    };

    // =====================================================
    // State Management
    // =====================================================
    let currentState = window.AJAX_CONFIG ? {
        tab: window.currentState?.tab || 'posts',
        search: window.currentState?.search || '',
        status: window.currentState?.status || '',
        visibility: window.currentState?.visibility || '',
        sort: window.currentState?.sort || 'latest'
    } : null;

    // =====================================================
    // Utilities
    // =====================================================

    function escapeHtml(str) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(str ?? '').replace(/[&<>"']/g, m => map[m]);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // =====================================================
    // Toast Notification System
    // =====================================================
    class ToastManager {
        constructor() {
            this.host = document.getElementById('myToastHost');
            if (!this.host) {
                console.warn('Toast host element not found');
            }
        }

        show(type, message, delay = CONFIG.TOAST_DELAY) {
            if (!this.host)
                return;

            const config = TOAST_TYPES[type.toUpperCase()] || TOAST_TYPES.INFO;
            const toast = this.createToastElement(config, message, delay);

            this.host.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast, {
                delay,
                animation: true
            });

            bsToast.show();
            toast.style.animation = 'slideInRight 0.35s cubic-bezier(0.4, 0, 0.2, 1)';

            toast.addEventListener('hidden.bs.toast', () => {
                toast.style.animation = 'slideOutRight 0.25s cubic-bezier(0.4, 0, 1, 1)';
                setTimeout(() => toast.remove(), 250);
            });
        }

        createToastElement(config, message, delay) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white border-0 ${config.bg}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
        <div class="d-flex align-items-center">
          <div class="toast-body">
            <i class="bi bi-${config.icon} me-2"></i>
            <span>${escapeHtml(message)}</span>
          </div>
          <button type="button" 
                  class="btn-close btn-close-white me-2 m-auto" 
                  data-bs-dismiss="toast" 
                  aria-label="Close"></button>
        </div>
      `;

            return toast;
        }

        success(message, delay) {
            this.show('SUCCESS', message, delay);
        }
        error(message, delay) {
            this.show('ERROR', message, delay);
        }
        warning(message, delay) {
            this.show('WARNING', message, delay);
        }
        info(message, delay) {
            this.show('INFO', message, delay);
        }
    }

    // =====================================================
    // AJAX Content Loader
    // =====================================================
    class AjaxContentLoader {
        constructor(toastManager) {
            this.toast = toastManager;
            this.container = document.getElementById('postsContainer');
            this.loadingOverlay = document.getElementById('loadingOverlay');
            this.baseUrl = window.AJAX_CONFIG?.baseUrl;

            if (!this.baseUrl) {
                console.warn('AJAX base URL not configured');
            }
        }

        async loadContent(params = {}) {
            if (!this.container || !this.baseUrl)
                return;

            this.showLoading();

            try {
                // Build URL with query parameters
                const url = new URL(this.baseUrl, window.location.origin);
                Object.keys(params).forEach(key => {
                    if (params[key]) {
                        url.searchParams.append(key, params[key]);
                    }
                });

                // Add AJAX header
                url.searchParams.append('ajax', '1');

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success !== false) {
                    this.updateContent(data.html || data.content || '');
                    this.updateTabCounts(data.tabs);
                    this.updateUrl(params);

                    // Reinitialize lightbox for new content
                    this.initializeLightbox();
                } else {
                    throw new Error(data.message || 'Failed to load content');
                }

            } catch (error) {
                console.error('AJAX load error:', error);
                this.toast.error('Failed to load content. Please try again.');
            } finally {
                this.hideLoading();
        }
        }

        updateContent(html) {
            // Find the tab content element
            const tabContent = document.getElementById('tabContent');
            if (tabContent) {
                tabContent.innerHTML = html;

                // Trigger fade-in animation
                tabContent.style.opacity = '0';
                requestAnimationFrame(() => {
                    tabContent.style.transition = 'opacity 0.3s ease';
                    tabContent.style.opacity = '1';
                });
            }
        }

        updateTabCounts(tabs) {
            if (!tabs)
                return;

            Object.keys(tabs).forEach(tabKey => {
                const tabBtn = document.querySelector(`[data-tab="${tabKey}"]`);
                const countSpan = tabBtn?.querySelector('.tab-count');

                if (countSpan && tabs[tabKey].count !== undefined) {
                    countSpan.textContent = tabs[tabKey].count;
                }
            });
        }

        updateUrl(params) {
            const url = new URL(window.location);

            // Clear existing params
            url.search = '';

            // Add new params
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                }
            });

            // Update URL without reload
            window.history.pushState({}, '', url);
        }

        initializeLightbox() {
            // Reinitialize lightbox for dynamically loaded content
            document.querySelectorAll('.media-gallery-facebook').forEach(gallery => {
                const items = [];
                gallery.querySelectorAll('.fb-media-item').forEach(item => {
                    items.push({
                        type: item.dataset.type || 'image',
                        url: item.dataset.src
                    });
                });

                gallery.dataset.lightboxItems = JSON.stringify(items);
            });
        }

        showLoading() {
            if (this.loadingOverlay) {
                this.loadingOverlay.style.display = 'flex';
            }
        }

        hideLoading() {
            if (this.loadingOverlay) {
                setTimeout(() => {
                    this.loadingOverlay.style.display = 'none';
                }, 200);
            }
        }
    }

    // =====================================================
    // Tab Manager
    // =====================================================
    class TabManager {
        constructor(ajaxLoader) {
            this.ajaxLoader = ajaxLoader;
            this.tabs = document.querySelectorAll('.tab-item');
            this.currentTab = currentState?.tab || 'posts';

            this.init();
        }

        init() {
            this.tabs.forEach(tab => {
                tab.addEventListener('click', (e) => this.handleTabClick(e, tab));
            });
        }

        async handleTabClick(e, tab) {
            e.preventDefault();

            const tabKey = tab.dataset.tab;
            if (tabKey === this.currentTab)
                return;

            // Update UI
            this.tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Update state
            this.currentTab = tabKey;
            if (currentState)
                currentState.tab = tabKey;

            // Update hidden input
            const tabInput = document.getElementById('currentTab');
            if (tabInput)
                tabInput.value = tabKey;

            // Load content
            await this.ajaxLoader.loadContent(this.getFilterParams());
        }

        getFilterParams() {
            const sortValue = document.getElementById('sortFilter')?.value || 'latest';
            return {
                tab: this.currentTab,
                q: currentState?.search || '',
                status: currentState?.status || '',
                visibility: currentState?.visibility || '',
                sort: sortValue,
            };
        }

    }

    // =====================================================
    // Filter Manager
    // =====================================================
    class FilterManager {
        constructor(ajaxLoader, tabManager) {
            this.ajaxLoader = ajaxLoader;
            this.tabManager = tabManager;

            this.searchInput = document.getElementById('searchInput');
            this.clearBtn = document.getElementById('clearSearch');
            this.statusFilter = document.getElementById('statusFilter');
            this.visibilityFilter = document.getElementById('visibilityFilter');
            this.sortFilter = document.getElementById('sortFilter');
            this.resetBtn = document.getElementById('resetFilters');

            this.init();
        }

        init() {
            if (!this.searchInput)
                return;

            // Search input with debounce
            const debouncedSearch = debounce(() => this.handleSearch(), CONFIG.DEBOUNCE_DELAY);
            this.searchInput.addEventListener('input', debouncedSearch);

            // Clear search button
            if (this.clearBtn) {
                this.clearBtn.addEventListener('click', () => this.clearSearch());
            }

            // Update clear button visibility
            this.searchInput.addEventListener('input', () => {
                if (this.clearBtn) {
                    this.clearBtn.style.display = this.searchInput.value ? 'flex' : 'none';
                }
            });

            // Filter selects
            [this.statusFilter, this.visibilityFilter, this.sortFilter].forEach(select => {
                if (select) {
                    select.addEventListener('change', () => this.handleFilterChange());
                }
            });

            // Reset button
            if (this.resetBtn) {
                this.resetBtn.addEventListener('click', () => this.resetFilters());
            }

            // Initial clear button state
            if (this.clearBtn) {
                this.clearBtn.style.display = this.searchInput.value ? 'flex' : 'none';
            }
        }

        async handleSearch() {
            if (currentState) {
                currentState.search = this.searchInput.value.trim();
            }
            await this.applyFilters();
        }

        async handleFilterChange() {
            if (currentState) {
                currentState.status = this.statusFilter?.value || '';
                currentState.visibility = this.visibilityFilter?.value || '';
                currentState.sort = this.sortFilter?.value || 'latest';
            }
            await this.applyFilters();
        }

        async applyFilters() {
            const params = {
                tab: this.tabManager.currentTab,
                q: this.searchInput?.value.trim() || '',
                status: this.statusFilter?.value || '',
                visibility: this.visibilityFilter?.value || '',
                sort: this.sortFilter?.value || 'latest'
            };

            await this.ajaxLoader.loadContent(params);
        }

        clearSearch() {
            if (this.searchInput) {
                this.searchInput.value = '';
                this.searchInput.focus();
                if (this.clearBtn) {
                    this.clearBtn.style.display = 'none';
                }
                if (currentState) {
                    currentState.search = '';
                }
                this.applyFilters();
            }
        }

        resetFilters() {
            if (this.searchInput)
                this.searchInput.value = '';
            if (this.statusFilter)
                this.statusFilter.value = '';
            if (this.visibilityFilter)
                this.visibilityFilter.value = '';
            if (this.sortFilter)
                this.sortFilter.value = 'latest';
            if (this.clearBtn)
                this.clearBtn.style.display = 'none';

            if (currentState) {
                currentState.search = '';
                currentState.status = '';
                currentState.visibility = '';
                currentState.sort = 'latest';
            }

            this.applyFilters();
        }
    }

    // =====================================================
    // Delete Functionality
    // =====================================================
    class DeleteManager {
        constructor(toastManager) {
            this.toast = toastManager;
            this.modal = null;
            this.deleteUrl = null;

            this.initModal();
            this.attachEventListeners();
        }

        initModal() {
            const modalEl = document.getElementById('deleteConfirmModal');
            if (!modalEl)
                return;

            this.modal = new bootstrap.Modal(modalEl);
            this.confirmBtn = document.getElementById('confirmDeleteBtn');
            this.confirmText = document.getElementById('deleteConfirmText');
        }

        attachEventListeners() {
            // Use event delegation for dynamically loaded content
            document.addEventListener('click', (e) => {
                const deleteBtn = e.target.closest('.js-delete-post');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleDeleteClick(deleteBtn);
                }
            });

            if (this.confirmBtn) {
                this.confirmBtn.addEventListener('click', () => this.confirmDelete());
            }
        }

        handleDeleteClick(btn) {
            this.deleteUrl = btn.getAttribute('data-delete-url');
            const title = btn.getAttribute('data-post-title') || 'this post';

            if (this.confirmText) {
                this.confirmText.textContent = `You are about to delete "${title}". This action cannot be undone.`;
            }

            this.modal?.show();
        }

        async confirmDelete() {
            if (!this.deleteUrl || !this.confirmBtn)
                return;

            this.setButtonState(true);

            try {
                const response = await fetch(this.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success !== false) {
                    this.toast.success('Post deleted successfully.');
                    this.modal?.hide();

                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    throw new Error(data.message || 'Failed to delete post');
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.toast.error(error.message || 'Failed to delete post. Please try again.');
                this.setButtonState(false);
            }
        }

        setButtonState(loading) {
            if (!this.confirmBtn)
                return;

            this.confirmBtn.disabled = loading;

            if (loading) {
                this.confirmBtn.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2"></span>
          Deleting...
        `;
            } else {
                this.confirmBtn.innerHTML = `
          <i class="bi bi-trash me-1"></i> Delete
        `;
            }
        }
    }

    // =====================================================
    // Lightbox Integration
    // =====================================================
    window.openLightbox = function (index, el) {
        const gallery = el?.closest?.('.media-gallery-facebook');
        if (!gallery)
            return;

        const items = JSON.parse(gallery.dataset.lightboxItems || '[]');
        if (window.lightbox && items.length > 0)
            window.lightbox.open(items, index);
    };


    // =====================================================
    // Initialize Application
    // =====================================================
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        console.log('🚀 My Posts - Initializing...');

        try {
            const toast = new ToastManager();
            const ajaxLoader = new AjaxContentLoader(toast);
            const tabManager = new TabManager(ajaxLoader);
            const filterManager = new FilterManager(ajaxLoader, tabManager);
            new DeleteManager(toast);

            // Initialize lightbox for initial content
            ajaxLoader.initializeLightbox();

            console.log('✅ My Posts - Initialized successfully');
        } catch (error) {
            console.error('❌ My Posts - Initialization error:', error);
        }

        document.addEventListener('click', (e) => {
            // 点到链接本身就让浏览器正常跳
            if (e.target.closest('a'))
                return;

            // 点到 media 或 delete/edit 按钮不跳
            if (e.target.closest('.fb-media-item'))
                return;
            if (e.target.closest('.js-delete-post'))
                return;
            if (e.target.closest('.post-actions'))
                return;

            const card = e.target.closest('.post-item');
            if (!card)
                return;

            const link = card.querySelector('.post-title a');
            if (link?.href)
                window.location.href = link.href;
        });
    }

    init();

})();