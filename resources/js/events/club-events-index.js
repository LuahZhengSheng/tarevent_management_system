/**
 * Club Events Index - AJAX Implementation
 * Handles filtering, searching, sorting, and pagination without page reload
 */

(function ($) {
    'use strict';

    // Configuration
    const config = window.clubEventsConfig || {};

    // State management
    let currentPage = 1;
    let currentFilters = {
        search: '',
        timeFilter: 'all', // all, upcoming, ongoing, past, draft
        status: '',
        category: '',
        feeType: '',
        visibility: '',
        registration: '',
        dateFrom: '',
        dateTo: '',
        sort: 'date_desc',
        view: 'grid'
    };

    // Statistics
    let stats = {
        total: 0,
        upcoming: 0,
        ongoing: 0,
        draft: 0
    };

    let deleteEventId = null;
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
        $('#searchInput').on('input', function () {
            clearTimeout(searchTimeout);
            const value = $(this).val().trim();

            if (value) {
                $('#clearSearch').show();
            } else {
                $('#clearSearch').hide();
            }

            searchTimeout = setTimeout(function () {
                currentFilters.search = value;
                currentPage = 1;
                fetchEvents();
            }, 500);
        });

        // Clear search
        $('#clearSearch').on('click', function () {
            $('#searchInput').val('');
            $(this).hide();
            currentFilters.search = '';
            currentPage = 1;
            fetchEvents();
        });

        // Filter tabs (time-based)
        $('.filter-tab').on('click', function () {
            $('.filter-tab').removeClass('active');
            $(this).addClass('active');
            currentFilters.timeFilter = $(this).data('filter');
            currentPage = 1;
            fetchEvents();
        });

        // Advanced filters
        $('#statusFilter').on('change', function () {
            currentFilters.status = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#categoryFilter').on('change', function () {
            currentFilters.category = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#feeTypeFilter').on('change', function () {
            currentFilters.feeType = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#visibilityFilter').on('change', function () {
            currentFilters.visibility = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#registrationFilter').on('change', function () {
            currentFilters.registration = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#dateFromFilter').on('change', function () {
            currentFilters.dateFrom = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        $('#dateToFilter').on('change', function () {
            currentFilters.dateTo = $(this).val();
            currentPage = 1;
            fetchEvents();
        });

        // Sort select
        $('#sortSelect').on('change', function () {
            currentFilters.sort = $(this).val();
            fetchEvents();
        });

        // View toggle
        $('.view-btn').on('click', function () {
            const view = $(this).data('view');
            switchView(view);
        });

        // Reset filters
        $('#resetFilters').on('click', function () {
            resetAllFilters();
        });

        // Stat cards click
        $('.stat-card').on('click', function () {
            const stat = $(this).data('stat');
            $('.stat-card').removeClass('active');
            $(this).addClass('active');

            // Set appropriate filter
            switch (stat) {
                case 'total':
                    $('.filter-tab[data-filter="all"]').click();
                    break;
                case 'upcoming':
                    $('.filter-tab[data-filter="upcoming"]').click();
                    break;
                case 'ongoing':
                    $('.filter-tab[data-filter="ongoing"]').click();
                    break;
                case 'draft':
                    $('.filter-tab[data-filter="draft"]').click();
                    break;
            }
        });

        // Delete modal confirmation
        $('#confirmDelete').on('click', function () {
            if (deleteEventId) {
                deleteEvent(deleteEventId);
            }
        });

        // Clear deleteEventId when modal closes
        $('#deleteModal').on('hidden.bs.modal', function () {
            deleteEventId = null;
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
                console.log('club fetch response >>>', response);
                console.log('events length >>>', response.events ? response.events.length : 'no events');
                if (response.success) {
                    updateStats(response.stats);
                    renderEvents(response.events);
                    renderPagination(response.pagination);
                    updateActiveFilters();
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
     * Update statistics
     */
    function updateStats(statsData) {
        stats = statsData;
        $('#totalEvents').text(stats.total || 0);
        $('#upcomingEvents').text(stats.upcoming || 0);
        $('#ongoingEvents').text(stats.ongoing || 0);
        $('#draftEvents').text(stats.draft || 0);
    }

    /**
     * Render events in the container
     */
    function renderEvents(events) {
        const $container = $('#eventsContainer');

        // 先清空容器
        $container.html('');

        if (!events || events.length === 0) {
            // 在 grid 里输出 club 页的 empty UI
            $container.html(`
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="empty-title">No Events Found</h3>
                <p class="empty-description">
                    Try adjusting your filters or create a new event to get started.
                </p>
                <a href="${config.createUrl}" class="btn-primary btn-create-event">
                    <i class="bi bi-plus-lg me-2"></i>
                    Create Your First Event
                </a>
            </div>
        `);
            $('#resultsCount').text(0);
            updateResultsSubtitle();
            $('#paginationWrapper').hide();
            return;
        }

        // 有数据
        let html = '';
        events.forEach(function (event) {
            html += renderEventCard(event);
        });

        $container.html(html);
        bindEventActions();
        $('#resultsCount').text(events.length);
        updateResultsSubtitle();
        $('#paginationWrapper').show();
    }

    /**
     * Render a single event card
     */
    function renderEventCard(event) {
        const editUrl = config.editUrl.replace(':id', event.id);
        const showUrl = config.showUrl.replace(':id', event.id);
        const posterUrl = event.poster_path ? `/storage/event-posters/${event.poster_path}` : null;

        return `
            <div class="event-card">
                ${event.status === 'draft' ? '<div class="draft-banner"><i class="bi bi-file-earmark"></i>Draft</div>' : ''}
                
                <div class="event-card-image">
                    ${posterUrl ?
                `<img src="${posterUrl}" alt="${event.title}" loading="lazy">` :
                `<div class="image-placeholder">
                            <i class="bi bi-calendar-event"></i>
                        </div>`
                }
                    
                    <div class="event-badges">
                        <span class="event-badge badge-category">
                            <i class="bi bi-bookmark"></i>
                            ${event.category}
                        </span>
                        ${event.is_paid ?
                `<span class="event-badge badge-paid">
                                <i class="bi bi-cash"></i>
                                RM ${parseFloat(event.fee_amount).toFixed(2)}
                            </span>` :
                `<span class="event-badge badge-free">
                                <i class="bi bi-gift"></i>
                                Free
                            </span>`
                }
                        ${event.is_public ?
                `<span class="event-badge badge-public">
                                <i class="bi bi-globe"></i>
                                Public
                            </span>` :
                `<span class="event-badge badge-private">
                                <i class="bi bi-lock"></i>
                                Private
                            </span>`
                }
                    </div>
                </div>
                
                <div class="event-card-content">
                    <div class="event-card-header">
                        <h3 class="event-card-title">
                            <a href="${showUrl}">${event.title}</a>
                        </h3>
                        <div class="event-card-meta">
                            <span class="meta-item">
                                <i class="bi bi-calendar3"></i>
                                ${event.start_time_formatted}
                            </span>
                            <span class="meta-item">
                                <i class="bi bi-clock"></i>
                                ${event.start_time_time}
                            </span>
                        </div>
                    </div>
                    
                    <p class="event-card-description">
                        ${event.description_short}
                    </p>
                    
                    <div class="event-info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Venue</div>
                                <div class="info-value" title="${event.venue}">${event.venue_short}</div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Status</div>
                                <div class="info-value">${event.registration_status}</div>
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
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Updated</div>
                                <div class="info-value">${event.updated_at_human}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="event-card-footer">
                    <div class="event-stats">
                        <div class="stat-item-small">
                            <i class="bi bi-people-fill"></i>
                            <strong>${event.registrations_count}</strong> registered
                        </div>
                        ${event.views_count ? `
                        <div class="stat-item-small">
                            <i class="bi bi-eye-fill"></i>
                            <strong>${event.views_count}</strong> views
                        </div>` : ''}
                    </div>
                    
                    <div class="event-actions">
                        <a href="${showUrl}" class="btn-action" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="${editUrl}" class="btn-action btn-edit" title="Edit Event">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn-action btn-delete" 
                                data-event-id="${event.id}" 
                                data-event-title="${event.title}"
                                title="Delete Event">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Bind event action buttons
     */
    function bindEventActions() {
        $('.btn-delete').off('click').on('click', function () {
            deleteEventId = $(this).data('event-id');
            const eventTitle = $(this).data('event-title');

            // Update modal content
            $('#deleteModal .modal-body p').first().html(
                    `Are you sure you want to delete "<strong>${eventTitle}</strong>"?`
                    );

            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    }

    /**
     * Delete event via AJAX
     */
    function deleteEvent(eventId) {
        const deleteUrl = config.deleteUrl.replace(':id', eventId);

        $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': config.csrfToken
            },
            dataType: 'json',
            success: function (response) {
                // Close modal
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                deleteModal.hide();

                // Show success message
                showSuccessToast('Event deleted successfully');

                // Refresh events
                fetchEvents();
            },
            error: function (xhr, status, error) {
                // 1. 关闭 Modal
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                deleteModal.hide();

                // 2. 尝试解析后端返回的 JSON 错误信息
                let errorMessage = 'Failed to delete event. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    // 如果后端返回了 { message: "Past events cannot be deleted..." }
                    errorMessage = xhr.responseJSON.message;
                }

                // 3. 显示具体的错误信息给用户
                showErrorToast(errorMessage);

                console.error('Delete event error:', errorMessage);
            }
        });
    }

    /**
     * Render pagination
     */
    function renderPagination(pagination) {
        const $wrapper = $('#paginationWrapper');

        if (!pagination || pagination.total <= pagination.per_page) {
            $wrapper.hide();
            return;
        }

        $wrapper.show();

        let html = '<div class="pagination-controls">';

        // Previous button
        html += `
            <button class="pagination-btn" 
                    data-page="${pagination.current_page - 1}" 
                    ${pagination.current_page === 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i>
            </button>
        `;

        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            html += `<button class="pagination-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-info">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <button class="pagination-btn ${i === pagination.current_page ? 'active' : ''}" 
                        data-page="${i}">
                    ${i}
                </button>
            `;
        }

        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += `<span class="pagination-info">...</span>`;
            }
            html += `<button class="pagination-btn" data-page="${pagination.last_page}">${pagination.last_page}</button>`;
        }

        // Next button
        html += `
            <button class="pagination-btn" 
                    data-page="${pagination.current_page + 1}" 
                    ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                <i class="bi bi-chevron-right"></i>
            </button>
        `;

        html += '</div>';

        $wrapper.html(html);

        // Bind pagination buttons
        $('.pagination-btn').off('click').on('click', function () {
            if (!$(this).prop('disabled') && !$(this).hasClass('active')) {
                currentPage = parseInt($(this).data('page'));
                fetchEvents();
                $('html, body').animate({scrollTop: 0}, 400);
            }
        });
    }

    /**
     * Update active filters display
     */
    function updateActiveFilters() {
        const $container = $('#activeFilters');
        const $list = $('#activeFiltersList');
        const activeFilters = [];

        // Check each filter
        if (currentFilters.search) {
            activeFilters.push({
                label: `Search: "${currentFilters.search}"`,
                key: 'search'
            });
        }

        if (currentFilters.status) {
            activeFilters.push({
                label: `Status: ${capitalizeFirst(currentFilters.status)}`,
                key: 'status'
            });
        }

        if (currentFilters.category) {
            activeFilters.push({
                label: `Category: ${currentFilters.category}`,
                key: 'category'
            });
        }

        if (currentFilters.feeType) {
            activeFilters.push({
                label: `Fee: ${capitalizeFirst(currentFilters.feeType)}`,
                key: 'feeType'
            });
        }

        if (currentFilters.visibility) {
            activeFilters.push({
                label: `Visibility: ${capitalizeFirst(currentFilters.visibility)}`,
                key: 'visibility'
            });
        }

        if (currentFilters.registration) {
            activeFilters.push({
                label: `Registration: ${capitalizeFirst(currentFilters.registration)}`,
                key: 'registration'
            });
        }

        if (currentFilters.dateFrom) {
            activeFilters.push({
                label: `From: ${currentFilters.dateFrom}`,
                key: 'dateFrom'
            });
        }

        if (currentFilters.dateTo) {
            activeFilters.push({
                label: `To: ${currentFilters.dateTo}`,
                key: 'dateTo'
            });
        }

        if (activeFilters.length > 0) {
            let html = '';
            activeFilters.forEach(function (filter) {
                html += `
                    <div class="filter-badge">
                        <span>${filter.label}</span>
                        <button class="filter-badge-remove" data-filter-key="${filter.key}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
            });

            $list.html(html);
            $container.show();
            $('#resetFilters').show();

            // Bind remove buttons
            $('.filter-badge-remove').on('click', function () {
                const key = $(this).data('filter-key');
                removeFilter(key);
            });
        } else {
            $container.hide();
            $('#resetFilters').hide();
        }
    }

    /**
     * Remove a specific filter
     */
    function removeFilter(key) {
        switch (key) {
            case 'search':
                $('#searchInput').val('');
                $('#clearSearch').hide();
                currentFilters.search = '';
                break;
            case 'status':
                $('#statusFilter').val('');
                currentFilters.status = '';
                break;
            case 'category':
                $('#categoryFilter').val('');
                currentFilters.category = '';
                break;
            case 'feeType':
                $('#feeTypeFilter').val('');
                currentFilters.feeType = '';
                break;
            case 'visibility':
                $('#visibilityFilter').val('');
                currentFilters.visibility = '';
                break;
            case 'registration':
                $('#registrationFilter').val('');
                currentFilters.registration = '';
                break;
            case 'dateFrom':
                $('#dateFromFilter').val('');
                currentFilters.dateFrom = '';
                break;
            case 'dateTo':
                $('#dateToFilter').val('');
                currentFilters.dateTo = '';
                break;
        }

        currentPage = 1;
        fetchEvents();
    }

    /**
     * Reset all filters
     */
    function resetAllFilters() {
        $('#searchInput').val('');
        $('#clearSearch').hide();
        $('#statusFilter').val('');
        $('#categoryFilter').val('');
        $('#feeTypeFilter').val('');
        $('#visibilityFilter').val('');
        $('#registrationFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');

        $('.filter-tab').removeClass('active');
        $('.filter-tab[data-filter="all"]').addClass('active');

        currentFilters = {
            search: '',
            timeFilter: 'all',
            status: '',
            category: '',
            feeType: '',
            visibility: '',
            registration: '',
            dateFrom: '',
            dateTo: '',
            sort: currentFilters.sort,
            view: currentFilters.view
        };

        currentPage = 1;
        fetchEvents();
    }

    /**
     * Update results subtitle
     */
    function updateResultsSubtitle() {
        let subtitle = '';

        if (currentFilters.timeFilter !== 'all') {
            subtitle = `Showing ${currentFilters.timeFilter} events`;
        } else {
            subtitle = 'Showing all events';
        }

        if (currentFilters.search) {
            subtitle += ` matching "${currentFilters.search}"`;
        }

        $('#resultsSubtitle').text(subtitle);
    }

    /**
     * Switch view (grid/list)
     */
    function switchView(view) {
        $('.view-btn').removeClass('active');
        $(`.view-btn[data-view="${view}"]`).addClass('active');

        const $container = $('#eventsContainer');
        if (view === 'grid') {
            $container.removeClass('view-list').addClass('view-grid');
        } else {
            $container.removeClass('view-grid').addClass('view-list');
        }

        currentFilters.view = view;
        localStorage.setItem('clubEventsView', view);
    }

    /**
     * Load saved view preference
     */
    function loadSavedView() {
        const savedView = localStorage.getItem('clubEventsView') || 'grid';
        switchView(savedView);
    }

    /**
     * Show loading state
     */
    function showLoading() {
        $('#loadingState').show();
        $('#eventsContainer').hide();
        $('#emptyState').hide();
        $('#paginationWrapper').hide();
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#loadingState').hide();
        $('#eventsContainer').show();
    }

    /**
     * Show empty state
     */
    function showEmptyState() {
        $('#eventsContainer').hide();
        $('#emptyState').show();
        $('#paginationWrapper').hide();
    }

    /**
     * Hide empty state
     */
    function hideEmptyState() {
        $('#emptyState').hide();
        $('#eventsContainer').show();
    }

    /**
     * Show error message
     */
    function showError(message) {
        console.error(message);
        // You can implement a toast notification here
    }

    /**
     * Show success toast
     */
    function showSuccessToast(message) {
        showToast(message, 'success');
    }

    /**
     * Show error toast
     */
    function showErrorToast(message) {
        showToast(message, 'error');
    }

    /**
     * Show toast notification
     */
    function showToast(message, type) {
        const toast = $(`
            <div class="toast-notification toast-${type}">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'} me-2"></i>
                <span>${message}</span>
            </div>
        `);

        $('body').append(toast);

        setTimeout(function () {
            toast.addClass('show');
        }, 100);

        setTimeout(function () {
            toast.removeClass('show');
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Capitalize first letter
     */
    function capitalizeFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Initialize when document is ready
    $(document).ready(function () {
        init();
    });

})(jQuery);