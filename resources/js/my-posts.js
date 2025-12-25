// resources/js/my-posts.js - Enhanced Version

/**
 * My Posts Page - Enhanced with smooth interactions and animations
 */

(function() {
  'use strict';

  // =====================================================
  // Configuration & Constants
  // =====================================================
  const CONFIG = {
    TOAST_DELAY: 3500,
    ANIMATION_DURATION: 250,
    DEBOUNCE_DELAY: 300
  };

  const TOAST_TYPES = {
    SUCCESS: { bg: 'bg-success', icon: 'check-circle-fill' },
    ERROR: { bg: 'bg-danger', icon: 'x-circle-fill' },
    WARNING: { bg: 'bg-warning', icon: 'exclamation-triangle-fill' },
    INFO: { bg: 'bg-info', icon: 'info-circle-fill' }
  };

  // =====================================================
  // Utilities
  // =====================================================
  
  /**
   * Escape HTML to prevent XSS
   */
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

  /**
   * Debounce function
   */
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

  /**
   * Get CSRF token
   */
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

    /**
     * Show toast notification
     */
    show(type, message, delay = CONFIG.TOAST_DELAY) {
      if (!this.host) return;

      const config = TOAST_TYPES[type.toUpperCase()] || TOAST_TYPES.INFO;
      const toast = this.createToastElement(config, message, delay);
      
      this.host.appendChild(toast);
      
      // Initialize Bootstrap toast
      const bsToast = new bootstrap.Toast(toast, { 
        delay,
        animation: true 
      });
      
      bsToast.show();

      // Add entrance animation
      toast.style.animation = 'slideInRight 0.35s cubic-bezier(0.4, 0, 0.2, 1)';

      // Remove from DOM after hidden
      toast.addEventListener('hidden.bs.toast', () => {
        toast.style.animation = 'slideOutRight 0.25s cubic-bezier(0.4, 0, 1, 1)';
        setTimeout(() => toast.remove(), 250);
      });
    }

    /**
     * Create toast element
     */
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

    /**
     * Initialize modal
     */
    initModal() {
      const modalEl = document.getElementById('deleteConfirmModal');
      if (!modalEl) {
        console.warn('Delete modal element not found');
        return;
      }

      this.modal = new bootstrap.Modal(modalEl);
      this.confirmBtn = document.getElementById('confirmDeleteBtn');
      this.confirmText = document.getElementById('deleteConfirmText');
    }

    /**
     * Attach delete button listeners
     */
    attachEventListeners() {
      // Delete buttons
      document.querySelectorAll('.js-delete-post').forEach(btn => {
        btn.addEventListener('click', (e) => this.handleDeleteClick(e, btn));
      });

      // Confirm button
      if (this.confirmBtn) {
        this.confirmBtn.addEventListener('click', () => this.confirmDelete());
      }
    }

    /**
     * Handle delete button click
     */
    handleDeleteClick(e, btn) {
      e.preventDefault();
      e.stopPropagation();

      this.deleteUrl = btn.getAttribute('data-delete-url');
      const title = btn.getAttribute('data-post-title') || 'this post';

      if (this.confirmText) {
        this.confirmText.textContent = `You are about to delete "${title}". This action cannot be undone.`;
      }

      this.modal?.show();
    }

    /**
     * Confirm delete action
     */
    async confirmDelete() {
      if (!this.deleteUrl || !this.confirmBtn) return;

      // Disable button
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
          
          // Reload page after animation
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

    /**
     * Set button loading state
     */
    setButtonState(loading) {
      if (!this.confirmBtn) return;

      this.confirmBtn.disabled = loading;
      
      if (loading) {
        this.confirmBtn.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
  // Filter & Sort System
  // =====================================================
  class FilterManager {
    constructor() {
      this.grid = document.getElementById('mpPostGrid');
      this.searchInput = document.getElementById('mpSearchInput');
      this.sortSelect = document.getElementById('mpSortSelect');
      this.applyBtn = document.getElementById('mpApplyClientFilter');
      this.resetBtn = document.getElementById('mpResetClientFilter');

      this.allRows = [];
      
      if (this.grid && this.applyBtn && this.resetBtn) {
        this.init();
      }
    }

    /**
     * Initialize filter system
     */
    init() {
      // Store all posts
      this.allRows = Array.from(this.grid.querySelectorAll('.mp-rowpost'));

      // Add fade-in animation
      this.allRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 50}ms`;
        row.classList.add('mp-fade-in');
      });

      // Attach event listeners
      this.applyBtn.addEventListener('click', () => this.applyFilters());
      this.resetBtn.addEventListener('click', () => this.resetFilters());

      // Real-time search
      if (this.searchInput) {
        const debouncedSearch = debounce(() => this.applyFilters(), CONFIG.DEBOUNCE_DELAY);
        this.searchInput.addEventListener('input', debouncedSearch);
      }
    }

    /**
     * Apply filters and sorting
     */
    applyFilters() {
      const query = (this.searchInput?.value || '').trim().toLowerCase();
      const sortBy = this.sortSelect?.value || 'latest';

      // Filter posts
      let filtered = this.allRows.filter(row => {
        if (!query) return true;
        
        const title = row.getAttribute('data-post-title') || '';
        const excerpt = row.getAttribute('data-post-excerpt') || '';
        
        return title.includes(query) || excerpt.includes(query);
      });

      // Sort posts
      filtered = this.sortPosts(filtered, sortBy);

      // Update display
      this.updateDisplay(filtered);
    }

    /**
     * Sort posts
     */
    sortPosts(posts, sortBy) {
      return posts.sort((a, b) => {
        const aCreated = parseInt(a.getAttribute('data-post-created') || '0', 10);
        const bCreated = parseInt(b.getAttribute('data-post-created') || '0', 10);
        const aViews = parseInt(a.getAttribute('data-post-views') || '0', 10);
        const bViews = parseInt(b.getAttribute('data-post-views') || '0', 10);
        const aLikes = parseInt(a.getAttribute('data-post-likes') || '0', 10);
        const bLikes = parseInt(b.getAttribute('data-post-likes') || '0', 10);

        switch(sortBy) {
          case 'oldest':
            return aCreated - bCreated;
          case 'most_viewed':
            return bViews - aViews;
          case 'most_liked':
            return bLikes - aLikes;
          case 'latest':
          default:
            return bCreated - aCreated;
        }
      });
    }

    /**
     * Update display
     */
    updateDisplay(posts) {
      // Clear grid
      this.grid.innerHTML = '';

      // Show empty state if no results
      if (posts.length === 0) {
        this.showEmptyState();
        return;
      }

      // Add filtered posts with staggered animation
      posts.forEach((post, index) => {
        post.style.animationDelay = `${index * 30}ms`;
        post.classList.add('mp-fade-in');
        this.grid.appendChild(post);
      });
    }

    /**
     * Show empty state
     */
    showEmptyState() {
      const empty = document.createElement('div');
      empty.className = 'mp-empty mp-fade-in';
      empty.innerHTML = `
        <div class="mp-empty__icon">
          <i class="bi bi-search"></i>
        </div>
        <div class="mp-empty__title">No results found</div>
        <div class="mp-empty__text">
          Try adjusting your search terms or reset the filters to see all posts.
        </div>
        <button class="btn btn-primary mp-btn-pill mt-3" onclick="document.getElementById('mpResetClientFilter').click()">
          <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filters
        </button>
      `;
      this.grid.appendChild(empty);
    }

    /**
     * Reset filters
     */
    resetFilters() {
      if (this.searchInput) this.searchInput.value = '';
      if (this.sortSelect) this.sortSelect.value = 'latest';

      this.grid.innerHTML = '';
      this.allRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 30}ms`;
        row.classList.add('mp-fade-in');
        this.grid.appendChild(row);
      });
    }
  }

  // =====================================================
  // Lightbox Integration
  // =====================================================
  class LightboxManager {
    constructor() {
      this.instance = null;
      this.init();
    }

    /**
     * Initialize lightbox
     */
    init() {
      // Check for MediaLightbox
      if (typeof window.MediaLightbox === 'function') {
        this.instance = new window.MediaLightbox();
      } else if (typeof MediaLightbox === 'function') {
        this.instance = new MediaLightbox();
      }

      if (this.instance) {
        this.attachEventListeners();
      }
    }

    /**
     * Attach lightbox event listeners
     */
    attachEventListeners() {
      const lightboxItems = document.querySelectorAll('[data-lightbox="true"]');

      lightboxItems.forEach(el => {
        // Click event
        el.addEventListener('click', (e) => {
          e.preventDefault();
          this.openLightbox(el);
        });

        // Keyboard accessibility
        el.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.openLightbox(el);
          }
        });
      });
    }

    /**
     * Open lightbox
     */
    openLightbox(element) {
      const group = element.getAttribute('data-group') || 'default';
      const groupElements = Array.from(
        document.querySelectorAll(`[data-lightbox="true"][data-group="${CSS.escape(group)}"]`)
      );

      const items = groupElements
        .map(node => ({
          type: node.getAttribute('data-type') || 'image',
          src: node.getAttribute('data-src')
        }))
        .filter(item => item.src);

      const index = groupElements.indexOf(element);

      if (typeof this.instance.open === 'function') {
        this.instance.open(items, Math.max(0, index));
      } else if (typeof this.instance.show === 'function') {
        this.instance.show(items, Math.max(0, index));
      }
    }
  }

  // =====================================================
  // Smooth Scroll Enhancement
  // =====================================================
  function enhanceSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  }

  // =====================================================
  // Loading States
  // =====================================================
  function addLoadingStates() {
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Processing...
          `;
        }
      });
    });
  }

  // =====================================================
  // Intersection Observer for Animations
  // =====================================================
  function setupIntersectionObserver() {
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      },
      { threshold: 0.1 }
    );

    // Observe cards and posts
    document.querySelectorAll('.mp-card, .mp-rowpost, .mp-item').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(el);
    });
  }

  // =====================================================
  // Initialize Application
  // =====================================================
  function init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
      return;
    }

    console.log('🚀 My Posts - Initializing...');

    try {
      // Initialize managers
      const toast = new ToastManager();
      new DeleteManager(toast);
      new FilterManager();
      new LightboxManager();

      // Enhance interactions
      enhanceSmoothScroll();
      addLoadingStates();
      setupIntersectionObserver();

      console.log('✅ My Posts - Initialized successfully');
    } catch (error) {
      console.error('❌ My Posts - Initialization error:', error);
    }
  }

  // Start initialization
  init();

})();