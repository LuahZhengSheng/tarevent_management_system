/**
 * My Events Page JavaScript
 * Handles filtering, sorting, search, and event management
 */

$(document).ready(function() {
    // State management
    let allEvents = [];
    let filteredEvents = [];
    let currentView = 'grid';
    let currentFilters = {
        search: '',
        status: 'upcoming',
        category: 'all',
        payment: 'all',
        sort: 'status_date'
    };
    
    // Initialize
    init();
    
    function init() {
        loadEvents();
        bindEventHandlers();
    }
    
    /**
     * Load events from server
     */
    function loadEvents() {
        showLoading();
        
        $.ajax({
            url: '/my-events/fetch',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    allEvents = response.events || [];
                    updateStats(response.stats);
                    applyFiltersAndDisplay();
                } else {
                    showError('Failed to load events');
                }
            },
            error: function(xhr) {
                console.error('Load events error:', xhr);
                showError('Failed to load events. Please refresh the page.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    /**
     * Bind event handlers
     */
    function bindEventHandlers() {
        // Search
        let searchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            const value = $(this).val().trim();
            
            if (value) {
                $('#clearSearch').show();
            } else {
                $('#clearSearch').hide();
            }
            
            searchTimeout = setTimeout(function() {
                currentFilters.search = value;
                applyFiltersAndDisplay();
            }, 300);
        });
        
        $('#clearSearch').on('click', function() {
            $('#searchInput').val('');
            $(this).hide();
            currentFilters.search = '';
            applyFiltersAndDisplay();
        });
        
        // Filters
        $('#statusFilter').on('change', function() {
            currentFilters.status = $(this).val();
            applyFiltersAndDisplay();
        });
        
        $('#categoryFilter').on('change', function() {
            currentFilters.category = $(this).val();
            applyFiltersAndDisplay();
        });
        
        $('#paymentFilter').on('change', function() {
            currentFilters.payment = $(this).val();
            applyFiltersAndDisplay();
        });
        
        $('#sortBy').on('change', function() {
            currentFilters.sort = $(this).val();
            applyFiltersAndDisplay();
        });
        
        // Reset filters
        $('#resetFilters, #clearFiltersBtn').on('click', function() {
            resetFilters();
        });
        
        // View toggle
        $('.view-btn').on('click', function() {
            const view = $(this).data('view');
            if (view !== currentView) {
                currentView = view;
                $('.view-btn').removeClass('active');
                $(this).addClass('active');
                
                if (view === 'grid') {
                    $('#eventsContainer').removeClass('list-view').addClass('grid-view');
                } else {
                    $('#eventsContainer').removeClass('grid-view').addClass('list-view');
                }
            }
        });
        
        // Event delegation for dynamic elements
        $(document).on('click', '.btn-cancel-registration', function(e) {
            e.preventDefault();
            const eventId = $(this).data('event-id');
            const eventTitle = $(this).data('event-title');
            const registrationId = $(this).data('registration-id');
            const canCancel = $(this).data('can-cancel');
            
            showCancelModal(eventId, eventTitle, registrationId, canCancel);
        });
    }
    
    /**
     * Apply filters and display results
     */
    function applyFiltersAndDisplay() {
        filteredEvents = filterEvents(allEvents);
        sortEvents(filteredEvents);
        displayEvents(filteredEvents);
        updateActiveFilters();
        updateResultsCount(filteredEvents.length);
    }
    
    /**
     * Filter events based on current filters
     */
    function filterEvents(events) {
        return events.filter(event => {
            // Search filter
            if (currentFilters.search) {
                const searchLower = currentFilters.search.toLowerCase();
                const matchesSearch = 
                    event.title.toLowerCase().includes(searchLower) ||
                    event.description.toLowerCase().includes(searchLower) ||
                    event.venue.toLowerCase().includes(searchLower);
                
                if (!matchesSearch) return false;
            }
            
            // Status filter
            if (currentFilters.status !== 'all') {
                if (event.status !== currentFilters.status) return false;
            }
            
            // Category filter
            if (currentFilters.category !== 'all') {
                if (event.category !== currentFilters.category) return false;
            }
            
            // Payment filter
            if (currentFilters.payment !== 'all') {
                const isPaid = event.is_paid === 1 || event.is_paid === true;
                if (currentFilters.payment === 'paid' && !isPaid) return false;
                if (currentFilters.payment === 'free' && isPaid) return false;
            }
            
            return true;
        });
    }
    
    /**
     * Sort events based on selected sort option
     */
    function sortEvents(events) {
        const sortBy = currentFilters.sort;
        
        events.sort((a, b) => {
            switch (sortBy) {
                case 'status_date':
                    // Ongoing first, then upcoming, then past
                    const statusOrder = { ongoing: 0, upcoming: 1, past: 2, cancelled: 3 };
                    const statusDiff = (statusOrder[a.status] || 99) - (statusOrder[b.status] || 99);
                    if (statusDiff !== 0) return statusDiff;
                    // Then by start date (earliest first)
                    return new Date(a.start_time) - new Date(b.start_time);
                
                case 'date_asc':
                    return new Date(a.start_time) - new Date(b.start_time);
                
                case 'date_desc':
                    return new Date(b.start_time) - new Date(a.start_time);
                
                case 'title':
                    return a.title.localeCompare(b.title);
                
                case 'registered_date':
                    return new Date(b.registered_at) - new Date(a.registered_at);
                
                default:
                    return 0;
            }
        });
    }
    
    /**
     * Display events in the container
     */
    function displayEvents(events) {
        const container = $('#eventsContainer');
        
        if (events.length === 0) {
            container.hide();
            $('#paginationContainer').hide();
            
            if (hasActiveFilters()) {
                $('#noResultsState').show();
                $('#emptyState').hide();
            } else {
                $('#emptyState').show();
                $('#noResultsState').hide();
            }
            return;
        }
        
        $('#emptyState, #noResultsState').hide();
        container.show().empty();
        
        events.forEach(event => {
            const card = createEventCard(event);
            container.append(card);
        });
    }
    
    /**
     * Create event card HTML
     */
    function createEventCard(event) {
        const statusClass = event.status || 'upcoming';
        const statusLabel = formatStatus(event.status);
        const posterUrl = event.poster_path 
            ? `/storage/event-posters/${event.poster_path}` 
            : null;
        
        const canCancel = event.can_cancel && 
            event.status !== 'past' && 
            event.status !== 'cancelled' &&
            event.registration_status === 'confirmed';
        
        return `
            <div class="event-card" data-event-id="${event.id}">
                <span class="event-status-badge ${statusClass}">${statusLabel}</span>
                
                <div class="event-poster">
                    ${posterUrl 
                        ? `<img src="${posterUrl}" alt="${event.title}">`
                        : `<div class="event-poster-placeholder">
                             <i class="bi bi-calendar-event"></i>
                           </div>`
                    }
                </div>
                
                <div class="event-content">
                    <span class="event-category">
                        <i class="bi bi-tag"></i>
                        ${event.category}
                    </span>
                    
                    <h3 class="event-title">${escapeHtml(event.title)}</h3>
                    
                    <div class="event-meta">
                        <div class="event-meta-item">
                            <i class="bi bi-calendar-event"></i>
                            <span>${formatDate(event.start_time)}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="bi bi-clock"></i>
                            <span>${formatTime(event.start_time)}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>${escapeHtml(event.venue_short || event.venue)}</span>
                        </div>
                        ${event.is_paid 
                            ? `<div class="event-meta-item">
                                 <i class="bi bi-cash"></i>
                                 <span>RM ${event.fee_amount}</span>
                               </div>`
                            : `<div class="event-meta-item">
                                 <i class="bi bi-gift"></i>
                                 <span>Free Event</span>
                               </div>`
                        }
                    </div>
                    
                    <div class="event-registration-info">
                        <div class="registration-date">
                            Registered on ${formatDate(event.registered_at)}
                        </div>
                        <div class="registration-status ${event.registration_status}">
                            <i class="bi bi-${event.registration_status === 'confirmed' ? 'check-circle' : 'hourglass'}"></i>
                            ${event.registration_status === 'confirmed' ? 'Confirmed' : 'Pending Payment'}
                        </div>
                    </div>
                    
                    <div class="event-actions">
                        <a href="/events/${event.id}" class="btn-view">
                            <i class="bi bi-eye"></i>
                            View Details
                        </a>
                        ${canCancel 
                            ? `<button type="button" 
                                      class="btn-cancel btn-cancel-registration"
                                      data-event-id="${event.id}"
                                      data-event-title="${escapeHtml(event.title)}"
                                      data-registration-id="${event.registration_id}"
                                      data-can-cancel="${event.can_cancel}">
                                  <i class="bi bi-x-circle"></i>
                                  Cancel
                               </button>`
                            : ''
                        }
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Show cancel registration modal
     */
    function showCancelModal(eventId, eventTitle, registrationId, canCancel) {
        $('#cancelEventTitle').text(eventTitle);
        
        let warningText = 'This action cannot be undone.';
        if (!canCancel) {
            warningText = 'Cancellation is not available for this event.';
        }
        
        $('#cancelWarningText').text(warningText);
        
        // Store data for confirmation
        $('#confirmCancelBtn').data({
            'event-id': eventId,
            'registration-id': registrationId
        });
        
        $('#confirmCancelBtn').prop('disabled', !canCancel);
        
        const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
        modal.show();
    }
    
    /**
     * Handle cancel confirmation
     */
    $(document).on('click', '#confirmCancelBtn', function() {
        const btn = $(this);
        const registrationId = btn.data('registration-id');
        
        if (btn.prop('disabled')) return;
        
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Cancelling...');
        
        $.ajax({
            url: `/registrations/${registrationId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                    
                    // Show success message
                    showToast('Registration cancelled successfully', 'success');
                    
                    // Reload events
                    loadEvents();
                } else {
                    showToast(response.message || 'Failed to cancel registration', 'error');
                }
            },
            error: function(xhr) {
                console.error('Cancel error:', xhr);
                const message = xhr.responseJSON?.message || 'Failed to cancel registration';
                showToast(message, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i> Yes, Cancel');
            }
        });
    });
    
    /**
     * Update statistics
     */
    function updateStats(stats) {
        if (stats) {
            $('#ongoingCount').text(stats.ongoing || 0);
            $('#upcomingCount').text(stats.upcoming || 0);
            $('#pastCount').text(stats.past || 0);
        } else {
            // Calculate from allEvents
            const ongoing = allEvents.filter(e => e.status === 'ongoing').length;
            const upcoming = allEvents.filter(e => e.status === 'upcoming').length;
            const past = allEvents.filter(e => e.status === 'past').length;
            
            $('#ongoingCount').text(ongoing);
            $('#upcomingCount').text(upcoming);
            $('#pastCount').text(past);
        }
    }
    
    /**
     * Update active filters display
     */
    function updateActiveFilters() {
        const activeFilters = [];
        
        if (currentFilters.search) {
            activeFilters.push({
                label: `Search: "${currentFilters.search}"`,
                filter: 'search'
            });
        }
        
        if (currentFilters.status !== 'all') {
            activeFilters.push({
                label: `Status: ${formatStatus(currentFilters.status)}`,
                filter: 'status'
            });
        }
        
        if (currentFilters.category !== 'all') {
            activeFilters.push({
                label: `Category: ${currentFilters.category}`,
                filter: 'category'
            });
        }
        
        if (currentFilters.payment !== 'all') {
            activeFilters.push({
                label: `Type: ${currentFilters.payment === 'paid' ? 'Paid' : 'Free'}`,
                filter: 'payment'
            });
        }
        
        const container = $('#activeFilters');
        const tagsContainer = $('#filterTags');
        
        if (activeFilters.length > 0) {
            tagsContainer.empty();
            activeFilters.forEach(filter => {
                const tag = $(`
                    <div class="filter-tag">
                        <span>${filter.label}</span>
                        <button type="button" data-filter="${filter.filter}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
                
                tag.find('button').on('click', function() {
                    removeFilter($(this).data('filter'));
                });
                
                tagsContainer.append(tag);
            });
            container.show();
        } else {
            container.hide();
        }
    }
    
    /**
     * Remove specific filter
     */
    function removeFilter(filterName) {
        switch (filterName) {
            case 'search':
                $('#searchInput').val('');
                $('#clearSearch').hide();
                currentFilters.search = '';
                break;
            case 'status':
                $('#statusFilter').val('all');
                currentFilters.status = 'all';
                break;
            case 'category':
                $('#categoryFilter').val('all');
                currentFilters.category = 'all';
                break;
            case 'payment':
                $('#paymentFilter').val('all');
                currentFilters.payment = 'all';
                break;
        }
        applyFiltersAndDisplay();
    }
    
    /**
     * Reset all filters
     */
    function resetFilters() {
        $('#searchInput').val('');
        $('#clearSearch').hide();
        $('#statusFilter').val('upcoming');
        $('#categoryFilter').val('all');
        $('#paymentFilter').val('all');
        $('#sortBy').val('status_date');
        
        currentFilters = {
            search: '',
            status: 'upcoming',
            category: 'all',
            payment: 'all',
            sort: 'status_date'
        };
        
        applyFiltersAndDisplay();
    }
    
    /**
     * Check if any filters are active
     */
    function hasActiveFilters() {
        return currentFilters.search ||
               currentFilters.status !== 'all' ||
               currentFilters.category !== 'all' ||
               currentFilters.payment !== 'all';
    }
    
    /**
     * Update results count
     */
    function updateResultsCount(count) {
        $('#resultsCount').text(count);
    }
    
    /**
     * Show loading state
     */
    function showLoading() {
        $('#loadingState').show();
        $('#eventsContainer, #emptyState, #noResultsState').hide();
    }
    
    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#loadingState').hide();
    }
    
    /**
     * Utility: Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-MY', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
    }
    
    /**
     * Utility: Format time
     */
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-MY', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true
        });
    }
    
    /**
     * Utility: Format status
     */
    function formatStatus(status) {
        const statusMap = {
            'ongoing': 'Ongoing',
            'upcoming': 'Upcoming',
            'past': 'Past',
            'cancelled': 'Cancelled'
        };
        return statusMap[status] || status;
    }
    
    /**
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    /**
     * Utility: Show toast notification
     */
    function showToast(message, type = 'info') {
        // Use Bootstrap toast or custom notification
        alert(message); // Placeholder - implement proper toast
    }
    
    /**
     * Utility: Show error message
     */
    function showError(message) {
        console.error(message);
        showToast(message, 'error');
    }
});