// resources/js/post-feed.js
(function () {
  'use strict';

  // ---- DOM ----
  const form = document.getElementById('filterForm');
  const postsGrid = document.getElementById('postsGrid');
  if (!form || !postsGrid) return;

  const resultsSummary = document.getElementById('resultsSummary');
  const trendingTags = document.getElementById('trendingTags');
  const selectedTagsInputs = document.getElementById('selectedTagsInputs');

  const loadingEl = document.getElementById('feedLoading');
  const endEl = document.getElementById('feedEnd');
  const sentinel = document.getElementById('infiniteSentinel');

  const ajaxUrl = form.getAttribute('action') || window.location.pathname;

  // ---- State ----
  const state = {
    page: 1,
    lastPage: 1,
    isLoading: false,
    selectedTags: new Set(),
    aborter: null,
    searchTimer: null,
  };

  // ---- utils ----
  const nowReqId = () => {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return window.crypto.randomUUID(); // UUID v4
    }
    // fallback：至少保证唯一
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const y = d.getFullYear();
    const m = pad(d.getMonth() + 1);
    const day = pad(d.getDate());
    const hh = pad(d.getHours());
    const mm = pad(d.getMinutes());
    const ss = pad(d.getSeconds());
    return `REQ-${y}${m}${day}-${hh}${mm}${ss}-${Math.random().toString(16).slice(2, 10)}`;
  };

  const showLoading = (show) => {
    if (!loadingEl) return;
    loadingEl.style.display = show ? 'flex' : 'none';
  };

  const showEnd = (show) => {
    if (!endEl) return;
    endEl.style.display = show ? 'flex' : 'none';
  };

  function showApiError(message) {
    const msg = message || 'Request failed.';
    console.error(msg);
    alert(msg);
  }

  async function safeReadJson(res) {
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) return null;
    try {
      return await res.json();
    } catch {
      return null;
    }
  }

  // ---- reveal animation (optional) ----
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          revealObserver.unobserve(e.target);
        }
      });
    },
    { threshold: 0.12 }
  );

  function observeReveals(root = document) {
    root.querySelectorAll('.js-reveal:not(.is-visible)').forEach((el) => revealObserver.observe(el));
  }

  // ---- tags init from URL ----
  (function initTagsFromUrl() {
    const url = new URL(window.location.href);
    url.searchParams.getAll('tags[]').forEach((s) => state.selectedTags.add(s));
    const legacy = url.searchParams.get('tag');
    if (legacy) state.selectedTags.add(legacy);
    syncSelectedTagsInputs();
  })();

  function syncSelectedTagsInputs() {
    if (!selectedTagsInputs) return;
    selectedTagsInputs.innerHTML = '';
    Array.from(state.selectedTags).forEach((slug) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'tags[]';
      input.value = slug;
      selectedTagsInputs.appendChild(input);
    });
  }

  function buildQuery(extra = {}) {
    const fd = new FormData(form);
    const params = new URLSearchParams();

    for (const [k, v] of fd.entries()) {
      if (k === 'tags[]') continue; // managed by JS
      if (v !== null && String(v).trim() !== '') params.set(k, String(v));
    }

    state.selectedTags.forEach((slug) => params.append('tags[]', slug));
    params.delete('tag'); // unify on tags[]

    Object.entries(extra).forEach(([k, v]) => {
      if (v === null || v === undefined || String(v).trim() === '') params.delete(k);
      else params.set(k, String(v));
    });

    return params;
  }

  function updateUrl(params, replace = false) {
    const url = new URL(window.location.href);
    url.search = params.toString();
    if (replace) history.replaceState({}, '', url);
    else history.pushState({}, '', url);
  }

  function syncTagChipActiveStates() {
    document.querySelectorAll('.js-tag-chip').forEach((btn) => {
      const slug = btn.getAttribute('data-tag');
      if (!slug) return;
      btn.classList.toggle('is-active', state.selectedTags.has(slug));
    });
  }

  function normalizeResponse(payload) {
    // supports:
    // 1) forum: { success: true, posts_html, summary_html, trending_html, meta }
    // 2) club api: { status:'S', data:{ posts_html, summary_html, trending_html, meta } }
    if (!payload) return null;

    if (payload.success) {
      return {
        ok: true,
        posts_html: payload.posts_html,
        summary_html: payload.summary_html,
        trending_html: payload.trending_html,
        meta: payload.meta || {},
      };
    }

    if (payload.status === 'S' && payload.data) {
      return {
        ok: true,
        posts_html: payload.data.posts_html,
        summary_html: payload.data.summary_html,
        trending_html: payload.data.trending_html,
        meta: payload.data.meta || {},
        categories: payload.data.categories || [],
        popular_tags: payload.data.popular_tags || [],
      };
    }

    return {
      ok: false,
      message: payload.message || 'Bad response',
    };
  }

  function applyRender(data, { append, page }) {
    // Replace/append posts
    if (!append) {
      postsGrid.innerHTML = data.posts_html || '';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      const tmp = document.createElement('div');
      tmp.innerHTML = data.posts_html || '';
      Array.from(tmp.children).forEach((ch) => postsGrid.appendChild(ch));
    }

    // Replace summary + trending
    if (resultsSummary && data.summary_html !== undefined) resultsSummary.innerHTML = data.summary_html || '';
    if (trendingTags && data.trending_html !== undefined) trendingTags.innerHTML = data.trending_html || '';

    // Replace categories options (from API)
    const catSel = form.querySelector('select[name="category_id"]');
    if (catSel && Array.isArray(data.categories)) {
      const current = catSel.value;

      catSel.innerHTML = '';
      const optAll = document.createElement('option');
      optAll.value = '';
      optAll.textContent = 'All categories';
      catSel.appendChild(optAll);

      data.categories.forEach((c) => {
        const opt = document.createElement('option');
        opt.value = String(c.id);
        opt.textContent = c.name;
        catSel.appendChild(opt);
      });

      catSel.value = current;
    }

    // Meta
    state.page = Number(data?.meta?.current_page || page);
    state.lastPage = Number(data?.meta?.last_page || state.lastPage);

    observeReveals(postsGrid);
    bindDynamicHandlers();
    showEnd(state.page >= state.lastPage);
  }

  async function fetchAndRender({ append = false, page = 1, pushUrl = true } = {}) {
    if (state.isLoading) return;
    state.isLoading = true;
    showLoading(true);

    if (state.aborter) state.aborter.abort();
    state.aborter = new AbortController();

    // keep URL clean (no ajax=1 in URL)
    const paramsForFetch = buildQuery({ ajax: 1, page, requestId: nowReqId() });
    const paramsForUrl = buildQuery({ page });

    if (pushUrl) updateUrl(paramsForUrl, false);
    else updateUrl(paramsForUrl, true);

    try {
      // Bearer token from localStorage
      const token = localStorage.getItem('api_token'); 
      const headers = { 'X-Requested-With': 'XMLHttpRequest' };
      if (token) headers['Authorization'] = `Bearer ${token}`;

      const res = await fetch(`${ajaxUrl}?${paramsForFetch.toString()}`, {
        headers,
        signal: state.aborter.signal,
      });

      const raw = await safeReadJson(res);

      // 401 (auth:sanctum)
      if (res.status === 401) {
        // 可选：清掉 token，逼用户重新拿 token
        // localStorage.removeItem('api_token');
        showApiError('Unauthorized (401). Missing/expired token. Please login again.');
        return;
      }

      // 非 JSON（例如后端返回 HTML）
      if (!raw) {
        showApiError(`Unexpected response (${res.status}). Not JSON.`);
        return;
      }

      // ClubPostFeedController: status S/F/E
      if (raw.status === 'S') {
        const data = normalizeResponse(raw);
        if (!data || !data.ok) {
          showApiError('Bad success payload.');
          return;
        }
        applyRender(data, { append, page });
        return;
      }

      if (raw.status === 'F') {
        showApiError(raw.message || `Request failed (${res.status}).`);
        return;
      }

      if (raw.status === 'E') {
        showApiError(raw.message || 'Server error.');
        return;
      }

      // Backward-compatible: forum endpoint format
      if (raw.success) {
        const data = normalizeResponse(raw);
        if (!data || !data.ok) {
          showApiError(data?.message || 'Bad response');
          return;
        }
        applyRender(data, { append, page });
        return;
      }

      // Unknown format
      showApiError(raw.message || `Unknown response (${res.status}).`);
    } catch (e) {
      if (e.name !== 'AbortError') console.error(e);
    } finally {
      showLoading(false);
      state.isLoading = false;
    }
  }

  function bindDynamicHandlers() {
    // Tag chips (trending) - multi select
    document.querySelectorAll('.js-tag-chip').forEach((btn) => {
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
        fetchAndRender({ append: false, page: 1, pushUrl: true });
      });
    });

    // Clear filters (from summary UI)
    document.querySelectorAll('.js-clear-filter').forEach((a) => {
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
        } else if (key === 'sort') {
          const sel = form.querySelector('select[name="sort"]');
          if (sel) sel.value = 'recent';
        }

        fetchAndRender({ append: false, page: 1, pushUrl: true });
      });
    });

    // Remove single tag (from summary UI)
    document.querySelectorAll('.js-remove-tag').forEach((a) => {
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

  // Auto AJAX on filter select change
  form.querySelectorAll('select.filter-select').forEach((sel) => {
    sel.addEventListener('change', () => fetchAndRender({ append: false, page: 1, pushUrl: true }));
  });

  // Search debounce -> AJAX
  const searchInput = form.querySelector('input[name="search"]');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(state.searchTimer);
      state.searchTimer = setTimeout(() => {
        fetchAndRender({ append: false, page: 1, pushUrl: true });
      }, 450);
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
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) loadNextPage();
        });
      },
      { rootMargin: '600px 0px', threshold: 0.01 }
    );
    io.observe(sentinel);
  }

  // Back/forward support
  window.addEventListener('popstate', () => {
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

    fetchAndRender({
      append: false,
      page: Number(url.searchParams.get('page') || 1),
      pushUrl: false,
    });
  });

  // Initial
  observeReveals(document);
  bindDynamicHandlers();
  fetchAndRender({ append: false, page: 1, pushUrl: true });
})();
