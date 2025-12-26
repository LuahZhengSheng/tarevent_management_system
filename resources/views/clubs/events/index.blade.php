@extends('layouts.club')

@section('title', 'My Events - Club Dashboard')

@section('content')
<div class="club-events-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('home') }}">Home</a>
                        <span>/</span>
                        <span>My Events</span>
                    </div>
                    <h1 class="page-title">My Events</h1>
                    <p class="page-description">Manage and track all your club events in one place</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('events.create') }}" class="btn-create-event">
                        <i class="bi bi-plus-lg me-2"></i>
                        Create New Event
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card" data-stat="total">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="totalEvents">0</div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="upcoming">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="upcomingEvents">0</div>
                        <div class="stat-label">Upcoming</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="ongoing">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="ongoingEvents">0</div>
                        <div class="stat-label">Ongoing</div>
                    </div>
                </div>
                <div class="stat-card" data-stat="draft">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-value" id="draftEvents">0</div>
                        <div class="stat-label">Drafts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Filter and Search Section -->
        <div class="filter-section">
            <div class="filter-header">
                <h2 class="filter-title">
                    <i class="bi bi-funnel me-2"></i>
                    Filter & Search
                </h2>
                <button class="btn-reset-filters" id="resetFilters" style="display: none;">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>
                    Reset All
                </button>
            </div>

            <!-- Search Bar -->
            <div class="search-bar-wrapper">
                <div class="search-input-group">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" 
                           class="search-input" 
                           id="searchInput"
                           placeholder="Search by event title, description, or venue...">
                    <button class="clear-search" id="clearSearch" style="display: none;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">
                    <i class="bi bi-calendar3 me-2"></i>
                    All Events
                </button>
                <button class="filter-tab" data-filter="upcoming">
                    <i class="bi bi-calendar-check me-2"></i>
                    Upcoming
                </button>
                <button class="filter-tab" data-filter="ongoing">
                    <i class="bi bi-clock-history me-2"></i>
                    Ongoing
                </button>
                <button class="filter-tab" data-filter="past">
                    <i class="bi bi-calendar-x me-2"></i>
                    Past
                </button>
                <button class="filter-tab" data-filter="draft">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Drafts
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <div class="filter-row">
                    <!-- Status Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-toggle-on me-2"></i>
                            Status
                        </label>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-bookmark me-2"></i>
                            Category
                        </label>
                        <select class="filter-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="Academic">Academic</option>
                            <option value="Sports">Sports</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Social">Social</option>
                            <option value="Career">Career</option>
                            <option value="Technology">Technology</option>
                        </select>
                    </div>

                    <!-- Fee Type Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-cash-coin me-2"></i>
                            Fee Type
                        </label>
                        <select class="filter-select" id="feeTypeFilter">
                            <option value="">All Types</option>
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <!-- Visibility Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-eye me-2"></i>
                            Visibility
                        </label>
                        <select class="filter-select" id="visibilityFilter">
                            <option value="">All Events</option>
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </div>

                    <!-- Registration Status Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-person-check me-2"></i>
                            Registration
                        </label>
                        <select class="filter-select" id="registrationFilter">
                            <option value="">All</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="full">Full</option>
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-calendar-range me-2"></i>
                            Date From
                        </label>
                        <input type="date" class="filter-date" id="dateFromFilter">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="bi bi-calendar-range me-2"></i>
                            Date To
                        </label>
                        <input type="date" class="filter-date" id="dateToFilter">
                    </div>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div class="active-filters" id="activeFilters" style="display: none;">
                <div class="active-filters-label">Active Filters:</div>
                <div class="active-filters-list" id="activeFiltersList"></div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-info">
                    <h3 class="results-title">
                        <span id="resultsCount">0</span> Events Found
                    </h3>
                    <p class="results-subtitle" id="resultsSubtitle"></p>
                </div>
                <div class="results-controls">
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid" title="Grid View">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button class="view-btn" data-view="list" title="List View">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>

                    <!-- Sort Select -->
                    <div class="sort-group">
                        <label class="sort-label">
                            <i class="bi bi-sort-down me-2"></i>
                            Sort By:
                        </label>
                        <select class="sort-select" id="sortSelect">
                            <option value="date_desc">Date: Newest First</option>
                            <option value="date_asc">Date: Oldest First</option>
                            <option value="title_asc">Title: A-Z</option>
                            <option value="title_desc">Title: Z-A</option>
                            <option value="registrations_desc">Most Registrations</option>
                            <option value="registrations_asc">Least Registrations</option>
                            <option value="created_desc">Recently Created</option>
                            <option value="updated_desc">Recently Updated</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div class="loading-state" id="loadingState" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading events...</p>
            </div>

            <!-- Events Container -->
            <div id="eventsContainer" class="events-container view-grid">
                <!-- Events will be loaded here via AJAX -->
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="empty-title">No Events Found</h3>
                <p class="empty-description">
                    Try adjusting your filters or create a new event to get started.
                </p>
                <a href="{{ route('events.create') }}" class="btn-primary btn-create-event">
                    <i class="bi bi-plus-lg me-2"></i>
                    Create Your First Event
                </a>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="paginationWrapper">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Delete Event
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this event?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All registrations and associated data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Event</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/club-events-index.css')
@endpush

@push('scripts')
<script>
    // Pass route to JavaScript
    window.clubEventsConfig = {
        fetchUrl: "{{ route('club.events.fetch') }}",
        deleteUrl: "{{ route('events.destroy', ':id') }}",
        editUrl: "{{ route('events.edit', ':id') }}",
        showUrl: "{{ route('events.show', ':id') }}",
        createUrl: "{{ route('events.create') }}",
        csrfToken: "{{ csrf_token() }}"
    };

    console.log(window.clubEventsConfig);
</script>
@vite('resources/js/events/club-events-index.js')
@endpush


@endsection