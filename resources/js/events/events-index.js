/**
 * Public Events Index - AJAX Implementation
 * Handles filtering, searching, and sorting without page reload
 */

(function ($) {
    'use strict';

    // Configuration
    const config = window.eventsConfig;

    function fetchEvents() {
        $.ajax({
            url: config.fetchUrl,
            type: 'GET',
            data: {page: currentPage, ...currentFilters},
            dataType: 'json'
        });
    }

    function renderEventCard(event) {
        const showUrl = config.showUrl.replace(':id', event.id);
        // 用 showUrl 生成详情链接
    }

    // State management
    let currentPage = 1;
    let currentFilters = {
        search: '',
        category: '',
        fee_type: '',
        start_date: '',
        sort: 'date_asc',
        view: 'grid'
    };

    let searchTimeout = null;

    /**
     * Initialize the page
     */
    function init() {
        bindEvents();
        loadSavedView();
        fetchEvents();
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Search input with debounce
        $('#searchInput, .search-input').on('input', function () {
            clearTimeout(searchTimeout);
            const value = $(this).val().trim();

            const $clearBtn = $(this).siblings('.clear-search');
            if (value) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }

            searchTimeout = setTimeout(function () {
                currentFilters.search = value;
                currentPage = 1;
                fetchEvents();
            }, 500);
        });

        // Clear search
        $('.clear-search').on('click', function () {
            const $input = $(this).siblings('input');
            $input.val('');
            $(this).hide();
            currentFilters.search = '';
            currentPage = 1;
            fetchEvents();
        });

        // Filter selects
        $('select[name="category"], .filter-select[name="category"]').on('change', function () {
            currentFilters.category = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('select[name="fee_type"], .filter-select[name="fee_type"]').on('change', function () {
            currentFilters.fee_type = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('input[name="start_date"], .filter-date[name="start_date"]').on('change', function () {
            currentFilters.start_date = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        // Sort select
        $('.form-select-modern, #sortSelect').on('change', function () {
            currentFilters.sort = $(this).val();
            fetchEvents();
        });

        // View toggle
        $('.view-btn').on('click', function () {
            const view = $(this).data('view');
            switchView(view);
        });

        // Clear all filters
        $('.filter-clear').on('click', function (e) {
            e.preventDefault();
            resetAllFilters();
        });
    }

    /**
     * Fetch events from server via AJAX
     */
    function fetchEvents() {
        showLoading();

        $.ajax({
            url: config.fetchUrl,
            type: 'GET',
            data: {
                page: currentPage,
                ...currentFilters
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderEvents(response.events);
                    renderPagination(response.pagination);
                    updateResultsCount(response.pagination.total);
                    hideLoading();
                } else {
                    showError('Failed to load events');
                    hideLoading();
                }
            },
            error: function (xhr, status, error) {
                console.error('Fetch events error:', error);
                showError('Failed to load events. Please try again.');
                hideLoading();
            }
        });
    }

    /**
     * Render events in the container
     */
    function renderEvents(events) {
        const $container = $('#eventsContainer, .events-grid');

        if (!events || events.length === 0) {
            showEmptyState();
            return;
        }

        hideEmptyState();

        let html = '';
        events.forEach(function (event) {
            html += renderEventCard(event);
        });

        $container.html(html);

        // Trigger animation
        setTimeout(function () {
            $('.event-card-wrapper').addClass('animate-in');
        }, 100);
    }

    /**
     * Render a single event card
     */
    function renderEventCard(event) {
        const showUrl = config.showUrl.replace(':id', event.id);
        const posterUrl = event.poster_path ? `/storage/event-posters/${event.poster_path}` : null;

        let statusBadge = '';

        const privateBadge = !event.is_public
                ? `<span class="tag tag-private">
               <i class="bi bi-lock-fill me-1"></i>Private
           </span>`
                : '';

        if (event.is_full) {
            statusBadge = `
                <div class="status-badge status-full">
                    <i class="bi bi-x-circle"></i>
                    <span>Full</span>
                </div>
            `;
        } else if (event.is_registration_open) {
            statusBadge = `
                <div class="status-badge status-open">
                    <i class="bi bi-check-circle"></i>
                    <span>Open</span>
                </div>
            `;
        } else {
            statusBadge = `
                <div class="status-badge status-closed">
                    <i class="bi bi-clock"></i>
                    <span>Closed</span>
                </div>
            `;
        }

        return `
            <div class="event-card-wrapper">
                <article class="event-card-modern">
                    <div class="event-image">
                        ${posterUrl ?
                `<img src="${posterUrl}" alt="${event.title}" loading="lazy">` :
                `<div class="image-placeholder">
                                <i class="bi bi-calendar-event"></i>
                                <span>${event.category}</span>
                            </div>`
                }
                        
                        <div class="event-tags">
                            <span class="tag tag-category">${event.category}</span>
                            ${privateBadge}
                            ${event.is_paid ?
                `<span class="tag tag-price">RM ${parseFloat(event.fee_amount).toFixed(2)}</span>` :
                `<span class="tag tag-free">Free</span>`
                }
                        </div>
                        
                        ${statusBadge}
                        
                        <a href="${showUrl}" class="quick-view">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="event-content">
                        <h3 class="event-title">
                            <a href="${showUrl}">${event.title}</a>
                        </h3>
                        
                        <p class="event-description">
                            ${event.description_short}
                        </p>
                        
                        <div class="event-info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Date</div>
                                    <div class="info-value">${event.start_time_formatted}</div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Time</div>
                                    <div class="info-value">${event.start_time_time}</div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Venue</div>
                                    <div class="info-value">${event.venue_short}</div>
                                </div>
                            </div>
                            
                            ${event.max_participants ? `
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Capacity</div>
                                    <div class="info-value">${event.remaining_seats} / ${event.max_participants}</div>
                                </div>
                            </div>` : ''}
                        </div>
                    </div>
                    
                    <div class="event-footer">
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="organizer-name">
                                ${event.organizer_name}
                            </div>
                        </div>
                        <a href="${showUrl}" class="btn-details">
                            Details
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
        `;
    }

    /**
     * Render pagination
     */
    function renderPagination(pagination) {
        const $wrapper = $('.pagination-wrapper');

        if (!pagination || pagination.total <= pagination.per_page) {
            $wrapper.hide();
            return;
        }

        $wrapper.show();

        // Use Laravel's pagination links if available, or render custom
        let html = '<nav><ul class="pagination justify-content-center">';

        // Previous
        html += `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;

        // Pages
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next
        html += `
            <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;

        html += '</ul></nav>';
        $wrapper.html(html);

        // Bind pagination links
        $('.page-link').on('click', function (e) {
            e.preventDefault();
            const $item = $(this).parent();
            if (!$item.hasClass('disabled') && !$item.hasClass('active')) {
                currentPage = parseInt($(this).data('page'));
                fetchEvents();
                $('html, body').animate({scrollTop: 0}, 400);
            }
        });
    }

    /**
     * Update results count
     */
    function updateResultsCount(total) {
        $('.results-count, #resultsCount').text(`Showing ${total} ${total === 1 ? 'event' : 'events'}`);
    }

    /**
     * Switch view (grid/list)
     */
    function switchView(view) {
        $('.view-btn').removeClass('active');
        $(`.view-btn[data-view="${view}"]`).addClass('active');

        const $container = $('#eventsContainer, .events-grid');
        if (view === 'grid') {
            $container.removeClass('view-list').addClass('view-grid');
        } else {
            $container.removeClass('view-grid').addClass('view-list');
        }

        currentFilters.view = view;
        localStorage.setItem('eventsView', view);
    }

    /**
     * Load saved view preference
     */
    function loadSavedView() {
        const savedView = localStorage.getItem('eventsView') || 'grid';
        switchView(savedView);
    }

    /**
     * Reset all filters
     */
    function resetAllFilters() {
        $('input[type="text"], input[type="date"]').val('');
        $('select').prop('selectedIndex', 0);
        $('.clear-search').hide();

        currentFilters = {
            search: '',
            category: '',
            fee_type: '',
            start_date: '',
            sort: currentFilters.sort,
            view: currentFilters.view
        };

        currentPage = 1;
        fetchEvents();
    }

    /**
     * Show loading state
     */
    function showLoading() {
        const $container = $('#eventsContainer, .events-grid');
        $container.html(`
            <div class="loading-state" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p style="margin-top: 1rem; color: var(--text-secondary);">Loading events...</p>
            </div>
        `);
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('.loading-state').remove();
    }

    /**
     * Show empty state
     */
    function showEmptyState() {
        const $container = $('#eventsContainer, .events-grid');
        $container.html(`
            <div class="empty-state-wrapper" style="grid-column: 1 / -1;">
                <div class="empty-state-modern">
                    <div class="empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="empty-title">No Events Found</h3>
                    <p class="empty-description">
                        We couldn't find any events matching your criteria. 
                        Try adjusting your filters or check back later.
                    </p>
                    <a href="${window.location.pathname}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        Reset Filters
                    </a>
                </div>
            </div>
        `);
    }

    /**
     * Hide empty state
     */
    function hideEmptyState() {
        $('.empty-state-wrapper').remove();
    }

    /**
     * Show error message
     */
    function showError(message) {
        console.error(message);
        // You can implement a toast notification here
    }

    // Initialize when document is ready
    $(document).ready(function () {
        // Only initialize if we're on the events index page
        if ($('#eventsContainer, .events-grid').length > 0) {
            init();
        }
    });

})(jQuery);