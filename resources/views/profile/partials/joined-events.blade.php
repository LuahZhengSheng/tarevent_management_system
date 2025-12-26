<!-- Author: Tang Lit Xuan -->
<div class="profile-section-header">
    <h2 class="profile-section-title">
        <i class="bi bi-calendar-event me-2"></i>
        My Joined Events
    </h2>
    <p class="profile-section-subtitle">View all events you have registered and confirmed.</p>
</div>

<!-- Loading State -->
<div id="eventsLoadingState" class="events-loading-state">
    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <span>Loading events...</span>
</div>

<!-- Error State -->
<div id="eventsErrorState" class="events-error-state" style="display: none;">
    <div class="events-error-content">
        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
        <div>
            <strong>Failed to load events</strong>
            <p id="eventsErrorMessage" class="mb-0">An error occurred while loading your events.</p>
        </div>
    </div>
    <button type="button" id="retryLoadEvents" class="btn-retry">
        <i class="bi bi-arrow-clockwise me-2"></i>
        Retry
    </button>
</div>

<!-- Empty State -->
<div id="eventsEmptyState" class="events-empty-state" style="display: none;">
    <div class="events-empty-content">
        <i class="bi bi-calendar-x text-muted"></i>
        <p class="events-empty-text">You haven't joined any events yet.</p>
        <a href="{{ route('home') }}" class="btn-view-events">
            <i class="bi bi-arrow-right me-2"></i>
            Browse Events
        </a>
    </div>
</div>

<!-- Events List Container -->
<div id="eventsContainer" class="events-list-container" style="display: none;">
    <div class="events-summary mb-3">
        <span class="events-count-badge">
            <i class="bi bi-calendar-check me-1"></i>
            <span id="totalEventsCount">0</span> event(s)
        </span>
    </div>
    <div id="eventsList" class="events-list"></div>
</div>

<style>
.events-loading-state,
.events-error-state,
.events-empty-state {
    padding: 2rem;
    text-align: center;
}

.events-loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    color: var(--text-secondary);
    font-size: 0.9375rem;
}

.events-error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.events-error-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: var(--warning-light);
    border-radius: 0.75rem;
    color: var(--warning);
    max-width: 500px;
    width: 100%;
}

.events-error-content strong {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.9375rem;
}

.events-error-content p {
    font-size: 0.875rem;
    margin: 0;
}

.btn-retry {
    padding: 0.625rem 1.25rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.btn-retry:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.events-empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.events-empty-content i {
    font-size: 3rem;
    opacity: 0.5;
}

.events-empty-text {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin: 0;
}

.btn-view-events {
    display: inline-flex;
    align-items: center;
    padding: 0rem 0.5rem;
    background: transparent;
    color: var(--primary);
    text-decoration: none;
    border: 1px solid var(--primary);
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-view-events:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.events-list-container {
    margin-top: 1rem;
}

.events-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.events-count-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.events-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.event-item {
    padding: 1.25rem 1.5rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.event-item:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-sm);
    transform: translateX(4px);
}

.event-info {
    flex: 1;
    min-width: 0;
}

.event-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.event-name a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.2s ease;
}

.event-name a:hover {
    color: var(--primary);
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.event-meta-item i {
    font-size: 0.875rem;
}

.event-status {
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-badge.confirmed {
    background: var(--success-light);
    color: var(--success);
}

.status-badge.pending {
    background: var(--warning-light);
    color: var(--warning);
}

.status-badge.cancelled {
    background: var(--error-light);
    color: var(--error);
}

@media (max-width: 768px) {
    .event-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .event-status {
        align-self: flex-end;
    }
}
</style>

@push('scripts')
<script>
(function() {
    const API_URL = '/api/user/joined-events';
    const $loadingState = $('#eventsLoadingState');
    const $errorState = $('#eventsErrorState');
    const $emptyState = $('#eventsEmptyState');
    const $container = $('#eventsContainer');
    const $eventsList = $('#eventsList');
    const $totalCount = $('#totalEventsCount');
    const $errorMessage = $('#eventsErrorMessage');
    const $retryBtn = $('#retryLoadEvents');

    // Generate UUID for requestID
    function generateUUID() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        // Fallback for older browsers
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Get API token from localStorage
    function getApiToken() {
        return localStorage.getItem('api_token');
    }

    // Load events from API
    function loadEvents() {
        // Show loading state
        $loadingState.show();
        $errorState.hide();
        $emptyState.hide();
        $container.hide();

        const requestID = generateUUID();
        const token = getApiToken();

        if (!token) {
            showError('Authentication required. Please refresh the page and login again.');
            return;
        }

        $.ajax({
            url: API_URL,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: {
                requestID: requestID
            },
            success: function(response) {
                $loadingState.hide();

                if (response.status === 'S' && response.data) {
                    const events = response.data.events || [];
                    const totalEvents = response.data.totalEvents || 0;

                    if (totalEvents === 0 || events.length === 0) {
                        $emptyState.show();
                    } else {
                        renderEvents(events, totalEvents);
                        $container.show();
                    }
                } else {
                    showError(response.message || 'Failed to load events.');
                }
            },
            error: function(xhr) {
                $loadingState.hide();
                let errorMessage = 'Failed to load events.';

                if (xhr.responseJSON) {
                    const response = xhr.responseJSON;
                    if (response.status === 'F' && response.message) {
                        if (response.message.includes('Unauthenticated')) {
                            errorMessage = 'Authentication required. Please refresh the page and login again.';
                        } else if (response.message.includes('Forbidden')) {
                            errorMessage = 'You do not have permission to view this information.';
                        } else {
                            errorMessage = response.message;
                        }
                    } else if (response.status === 'E' && response.message) {
                        errorMessage = response.message;
                    }
                } else if (xhr.status === 401) {
                    errorMessage = 'Authentication required. Please refresh the page and login again.';
                } else if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to view this information.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                }

                showError(errorMessage);
            }
        });
    }

    // Show error state
    function showError(message) {
        $errorMessage.text(message);
        $errorState.show();
    }

    // Render events list
    function renderEvents(events, totalEvents) {
        $totalCount.text(totalEvents);
        $eventsList.empty();

        events.forEach(function(event) {
            const eventItem = createEventItem(event);
            $eventsList.append(eventItem);
        });
    }

    // Create event item HTML
    function createEventItem(event) {
        const registerDate = new Date(event.register_date);
        const formattedDate = registerDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const formattedTime = registerDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const statusClass = event.status === 'confirmed' ? 'confirmed' : 
                           event.status === 'pending' ? 'pending' : 'cancelled';

        return $(`
            <div class="event-item">
                <div class="event-info">
                    <div class="event-name">
                        <i class="bi bi-calendar-event"></i>
                        <a href="/events/${event.event_id}">${escapeHtml(event.event_name)}</a>
                    </div>
                    <div class="event-meta">
                        <div class="event-meta-item">
                            <i class="bi bi-calendar3"></i>
                            <span>Registered: ${formattedDate}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="bi bi-clock"></i>
                            <span>${formattedTime}</span>
                        </div>
                    </div>
                </div>
                <div class="event-status">
                    <span class="status-badge ${statusClass}">
                        ${escapeHtml(event.status)}
                    </span>
                </div>
            </div>
        `);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Retry button click handler
    $retryBtn.on('click', function() {
        loadEvents();
    });

    // Initial load
    loadEvents();
})();
</script>
@endpush

