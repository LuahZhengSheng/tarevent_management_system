/**
 * Forum JavaScript - TAREvent
 */

document.addEventListener('DOMContentLoaded', function() {
    initForumFeatures();
});

function initForumFeatures() {
    // Search input debounce
    initSearchDebounce();
    
    // Smooth scroll to top
    initScrollToTop();
    
    // Post card hover effects
    initPostCardEffects();
    
    // Filter animations
    initFilterAnimations();
}

/**
 * Search Input Debounce
 */
function initSearchDebounce() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    let debounceTimer;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        
        const searchIcon = document.querySelector('.search-icon');
        searchIcon.classList.add('searching');
        
        debounceTimer = setTimeout(() => {
            searchIcon.classList.remove('searching');
            // Auto-submit after 500ms of no typing
            // Uncomment if you want auto-submit:
            // e.target.form.submit();
        }, 500);
    });
}

/**
 * Post Card Effects
 */
function initPostCardEffects() {
    const postCards = document.querySelectorAll('.post-card');
    
    postCards.forEach(card => {
        // Prevent card click when clicking on links inside
        card.addEventListener('click', function(e) {
            // If clicking on a link or button, don't navigate
            if (e.target.closest('a') || e.target.closest('button')) {
                e.stopPropagation();
                return;
            }
        });

        // Add ripple effect on click
        card.addEventListener('mousedown', function(e) {
            const ripple = document.createElement('div');
            ripple.className = 'card-ripple';
            
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            card.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

/**
 * Filter Animations
 */
function initFilterAnimations() {
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add loading animation
            const form = this.closest('form');
            if (form) {
                form.classList.add('loading');
            }
        });
    });
}

/**
 * Scroll to Top Button
 */
function initScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Add CSS for scroll-to-top button and ripple effect
 */
const style = document.createElement('style');
style.textContent = `
    .scroll-to-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top:hover {
        transform: translateY(-4px) scale(1.1);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .scroll-to-top:active {
        transform: translateY(-2px) scale(1.05);
    }

    .card-ripple {
        position: absolute;
        border-radius: 50%;
        background: var(--primary);
        opacity: 0.3;
        pointer-events: none;
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }

    .search-icon.searching {
        animation: search-pulse 1s ease-in-out infinite;
    }

    @keyframes search-pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .filter-form.loading {
        opacity: 0.6;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .scroll-to-top {
            bottom: 1rem;
            right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1rem;
        }
    }
`;
document.head.appendChild(style);



(function () {
  'use strict';

  const cfg = window.FORUM_INDEX || {};
  const form = document.getElementById('forumFiltersForm');
  const postsGrid = document.getElementById('postsGrid');
  const sentinel = document.getElementById('infiniteSentinel');
  const loadingEl = document.getElementById('feedLoading');
  const endEl = document.getElementById('feedEnd');

  if (!form || !postsGrid || !sentinel) return;

  let currentPage = Number(cfg.initialPage || 1);
  let lastPage = Number(cfg.lastPage || 1);
  let isLoading = false;

  // --- helpers
  function qsFromForm(extra = {}) {
    const fd = new FormData(form);
    const params = new URLSearchParams();
    for (const [k, v] of fd.entries()) {
      if (v !== null && String(v).trim() !== '') params.set(k, String(v));
    }
    for (const [k, v] of Object.entries(extra)) {
      if (v === null || v === undefined || String(v).trim() === '') params.delete(k);
      else params.set(k, String(v));
    }
    return params.toString();
  }

  function showLoading(show) {
    if (!loadingEl) return;
    loadingEl.style.display = show ? 'flex' : 'none';
  }

  function showEnd(show) {
    if (!endEl) return;
    endEl.style.display = show ? 'flex' : 'none';
  }

  // Reveal animation (IntersectionObserver)
  const revealObserver = new IntersectionObserver(
    entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          revealObserver.unobserve(e.target);
        }
      });
    },
    { root: null, threshold: 0.12 }
  );

  function observeReveals(root = document) {
    root.querySelectorAll('.js-reveal:not(.is-visible)').forEach(el => revealObserver.observe(el));
  }

  observeReveals(document);

  // Clicking card navigates (keep links working)
  postsGrid.addEventListener('click', (e) => {
    const card = e.target.closest('.post-card');
    if (!card) return;
    if (e.target.closest('a,button,input,select,textarea')) return;

    const slug = card.getAttribute('data-post-slug');
    if (slug) window.location.href = `${cfg.ajaxUrl.replace(/\/$/, '')}/posts/${slug}`;
  });

  // Tag chips -> set hidden tag + submit
  document.querySelectorAll('.tag-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      const tag = btn.getAttribute('data-tag') || '';
      const hidden = document.getElementById('tagHidden');
      if (hidden) hidden.value = tag;
      form.requestSubmit();
    });
  });

  // Reset
  const resetBtn = document.getElementById('resetBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      window.location.href = cfg.ajaxUrl;
    });
  }

  // Infinite scroll loader
  async function loadNextPage() {
    if (isLoading) return;
    if (currentPage >= lastPage) {
      showEnd(true);
      return;
    }

    isLoading = true;
    showLoading(true);

    try {
      const nextPage = currentPage + 1;
      const query = qsFromForm({ page: nextPage, ajax: 1 });

      const res = await fetch(`${cfg.ajaxUrl}?${query}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) throw new Error('Request failed');
      const data = await res.json();

      if (data && data.html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = data.html;
        // append children
        Array.from(tmp.children).forEach(child => postsGrid.appendChild(child));
        observeReveals(postsGrid);
      }

      currentPage = Number(data?.meta?.current_page || nextPage);
      lastPage = Number(data?.meta?.last_page || lastPage);

      if (currentPage >= lastPage) showEnd(true);
    } catch (e) {
      // fail silently: keep UI usable
      console.error(e);
    } finally {
      showLoading(false);
      isLoading = false;
    }
  }

  const infiniteObserver = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) loadNextPage();
      });
    },
    { root: null, rootMargin: '600px 0px', threshold: 0.01 }
  );

  infiniteObserver.observe(sentinel);
})();


// ===============================
// Forum Index AJAX (search/filter/sort/tags + infinite scroll)
// Put this at the END of resources/js/forum.js
// ===============================
(function () {
  'use strict';

  const form = document.getElementById('filterForm');
  const postsGrid = document.getElementById('postsGrid');
  if (!form || !postsGrid) return; // only run on index

  const resultsSummary = document.getElementById('resultsSummary');
  const trendingTags = document.getElementById('trendingTags');
  const selectedTagsInputs = document.getElementById('selectedTagsInputs');

  // loading UI (if you have these ids; otherwise it silently works)
  const loadingEl = document.getElementById('feedLoading');
  const endEl = document.getElementById('feedEnd');
  const sentinel = document.getElementById('infiniteSentinel');

  const ajaxUrl = form.getAttribute('action') || window.location.pathname;

  // State
  const state = {
    page: 1,
    lastPage: 1,
    isLoading: false,
    selectedTags: new Set(),
    aborter: null,
  };

  // Init selected tags from URL (tags[]=) or legacy (tag=)
  (function initTagsFromUrl() {
    const url = new URL(window.location.href);
    url.searchParams.getAll('tags[]').forEach(s => state.selectedTags.add(s));
    const legacy = url.searchParams.get('tag');
    if (legacy) state.selectedTags.add(legacy);
    syncSelectedTagsInputs();
  })();

  // Helper: show/hide
  function showLoading(show) {
    if (!loadingEl) return;
    loadingEl.style.display = show ? 'flex' : 'none';
  }
  function showEnd(show) {
    if (!endEl) return;
    endEl.style.display = show ? 'flex' : 'none';
  }

  // Sync tags[] hidden inputs
  function syncSelectedTagsInputs() {
    if (!selectedTagsInputs) return;
    selectedTagsInputs.innerHTML = '';
    Array.from(state.selectedTags).forEach(slug => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'tags[]';
      input.value = slug;
      selectedTagsInputs.appendChild(input);
    });
  }

  // Build query from form + tags[] + extra
  function buildQuery(extra = {}) {
    const fd = new FormData(form);
    const params = new URLSearchParams();

    // Copy normal fields
    for (const [k, v] of fd.entries()) {
      if (k === 'tags[]') continue; // we manage tags ourselves
      if (v !== null && String(v).trim() !== '') params.set(k, String(v));
    }

    // Add tags[]
    state.selectedTags.forEach(slug => params.append('tags[]', slug));

    // Remove legacy tag param if exists (we unify on tags[])
    params.delete('tag');

    // Apply extra
    Object.entries(extra).forEach(([k, v]) => {
      if (v === null || v === undefined || String(v).trim() === '') params.delete(k);
      else params.set(k, String(v));
    });

    return params;
  }

  // Update URL querystring
  function updateUrl(params, replace = false) {
    const url = new URL(window.location.href);
    url.search = params.toString();
    if (replace) history.replaceState({}, '', url);
    else history.pushState({}, '', url);
  }

  // Reveal animation for new cards (use your existing .js-reveal / .is-visible styles in forum.css) [file:174]
  const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('is-visible');
        revealObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  function observeReveals(root = document) {
    root.querySelectorAll('.js-reveal:not(.is-visible)').forEach(el => revealObserver.observe(el));
  }

  // Apply active state on tag chips in sidebar/trending (if exists)
  function syncTagChipActiveStates() {
    document.querySelectorAll('.js-tag-chip').forEach(btn => {
      const slug = btn.getAttribute('data-tag');
      if (!slug) return;
      btn.classList.toggle('is-active', state.selectedTags.has(slug));
    });
  }

  // Core: fetch + replace UI
  async function fetchAndRender({ append = false, page = 1, pushUrl = true } = {}) {
    if (state.isLoading) return;
    state.isLoading = true;
    showLoading(true);

    // cancel previous
    if (state.aborter) state.aborter.abort();
    state.aborter = new AbortController();

    const params = buildQuery({ ajax: 1, page });
    if (pushUrl) updateUrl(buildQuery({ page }), false); // keep URL clean without ajax=1
    else updateUrl(buildQuery({ page }), true);

    try {
      const res = await fetch(`${ajaxUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: state.aborter.signal,
      });

      if (!res.ok) throw new Error('Request failed');
      const data = await res.json();

      if (!data || !data.success) throw new Error('Bad response');

      // Replace/append posts
      if (!append) {
        postsGrid.innerHTML = data.posts_html || '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        const tmp = document.createElement('div');
        tmp.innerHTML = data.posts_html || '';
        Array.from(tmp.children).forEach(ch => postsGrid.appendChild(ch));
      }

      // Replace summary + trending
      if (resultsSummary && data.summary_html !== undefined) resultsSummary.innerHTML = data.summary_html;
      if (trendingTags && data.trending_html !== undefined) trendingTags.innerHTML = data.trending_html;

      // Update meta
      state.page = Number(data?.meta?.current_page || page);
      state.lastPage = Number(data?.meta?.last_page || state.lastPage);

      // Reveal animation
      observeReveals(postsGrid);

      // Re-bind tag chips after trending HTML replaced
      bindDynamicHandlers();

      // End state
      showEnd(state.page >= state.lastPage);
    } catch (e) {
      if (e.name !== 'AbortError') console.error(e);
    } finally {
      showLoading(false);
      state.isLoading = false;
    }
  }

  // Bind handlers that may be replaced by AJAX (summary close buttons, trending tags)
  function bindDynamicHandlers() {
    // Tag chips (trending)
    document.querySelectorAll('.js-tag-chip').forEach(btn => {
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const slug = btn.getAttribute('data-tag');
        if (!slug) return;

        if (state.selectedTags.has(slug)) state.selectedTags.delete(slug);
        else state.selectedTags.add(slug);

        syncSelectedTagsInputs();
        syncTagChipActiveStates();

        // reset paging then fetch
        fetchAndRender({ append: false, page: 1, pushUrl: true });
      });
    });

    // Clear filter buttons in summary
    document.querySelectorAll('.js-clear-filter').forEach(a => {
      if (a.dataset.bound === '1') return;
      a.dataset.bound = '1';

      a.addEventListener('click', (e) => {
        e.preventDefault();
        const key = a.getAttribute('data-clear');
        if (!key) return;

        if (key === 'search') {
          const inp = form.querySelector('input[name="search"]');
          if (inp) inp.value = '';
        } else if (key === 'category_id') {
          const sel = form.querySelector('select[name="category_id"]');
          if (sel) sel.value = '';
        }
        fetchAndRender({ append: false, page: 1, pushUrl: true });
      });
    });

    // Remove single tag in summary
    document.querySelectorAll('.js-remove-tag').forEach(a => {
      if (a.dataset.bound === '1') return;
      a.dataset.bound = '1';

      a.addEventListener('click', (e) => {
        e.preventDefault();
        const slug = a.getAttribute('data-tag');
        if (!slug) return;
        state.selectedTags.delete(slug);
        syncSelectedTagsInputs();
        syncTagChipActiveStates();
        fetchAndRender({ append: false, page: 1, pushUrl: true });
      });
    });

    syncTagChipActiveStates();
  }

  // Intercept submit
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    fetchAndRender({ append: false, page: 1, pushUrl: true });
  });

  // Auto AJAX on filter select change (no full submit)
  form.querySelectorAll('select.filter-select').forEach(sel => {
    sel.addEventListener('change', () => fetchAndRender({ append: false, page: 1, pushUrl: true }));
  });

  // Search debounce -> AJAX
  const searchInput = form.querySelector('input[name="search"]');
  if (searchInput) {
    let t = null;
    searchInput.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => fetchAndRender({ append: false, page: 1, pushUrl: true }), 450);
    });
  }

  // Infinite scroll
  async function loadNextPage() {
    if (state.isLoading) return;
    if (state.page >= state.lastPage) {
      showEnd(true);
      return;
    }
    await fetchAndRender({ append: true, page: state.page + 1, pushUrl: false });
  }

  if (sentinel) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(en => {
        if (en.isIntersecting) loadNextPage();
      });
    }, { rootMargin: '600px 0px', threshold: 0.01 });

    io.observe(sentinel);
  }

  // Handle back/forward
  window.addEventListener('popstate', () => {
    // Rebuild state from URL
    const url = new URL(window.location.href);
    state.selectedTags = new Set(url.searchParams.getAll('tags[]'));
    const legacy = url.searchParams.get('tag');
    if (legacy) state.selectedTags.add(legacy);
    syncSelectedTagsInputs();

    // Sync form fields
    const s = url.searchParams.get('search') || '';
    if (searchInput) searchInput.value = s;
    const cat = url.searchParams.get('category_id') || '';
    const catSel = form.querySelector('select[name="category_id"]');
    if (catSel) catSel.value = cat;
    const sort = url.searchParams.get('sort') || 'recent';
    const sortSel = form.querySelector('select[name="sort"]');
    if (sortSel) sortSel.value = sort;

    fetchAndRender({ append: false, page: Number(url.searchParams.get('page') || 1), pushUrl: false });
  });

  // Initial bindings (for initial HTML)
  observeReveals(document);
  bindDynamicHandlers();

})();
