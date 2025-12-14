@extends('layouts.app')

@section('title', 'My Events')

@push('styles')
@vite('resources/css/events/my-events.css')
@endpush

@section('content')
<div class="my-events-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="bi bi-bookmark-heart"></i>
                    My Events
                </h1>
                <p class="page-subtitle">Manage your event registrations and participation history</p>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <div class="stat-icon ongoing">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="ongoingCount">0</div>
                        <div class="stat-label">Ongoing</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon upcoming">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="upcomingCount">0</div>
                        <div class="stat-label">Upcoming</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon past">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="pastCount">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="filter-section">
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       id="searchInput" 
                       class="search-input" 
                       placeholder="Search your events...">
                <button type="button" id="clearSearch" class="clear-search" style="display: none;">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>

            <div class="filter-controls">
                <!-- Status Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="bi bi-funnel"></i> Status
                    </label>
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Events</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="upcoming" selected>Upcoming</option>
                        <option value="past">Past</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="bi bi-tags"></i> Category
                    </label>
                    <select id="categoryFilter" class="filter-select">
                        <option value="all">All Categories</option>
                        <option value="Academic">Academic</option>
                        <option value="Sports">Sports</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Social">Social</option>
                        <option value="Career">Career</option>
                        <option value="Technology">Technology</option>
                    </select>
                </div>

                <!-- Payment Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="bi bi-cash"></i> Type
                    </label>
                    <select id="paymentFilter" class="filter-select">
                        <option value="all">All Types</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="bi bi-sort-down"></i> Sort By
                    </label>
                    <select id="sortBy" class="filter-select">
                        <option value="status_date">Status & Date</option>
                        <option value="date_asc">Date (Earliest)</option>
                        <option value="date_desc">Date (Latest)</option>
                        <option value="title">Title (A-Z)</option>
                        <option value="registered_date">Registration Date</option>
                    </select>
                </div>

                <!-- Reset Filters -->
                <button type="button" id="resetFilters" class="btn-reset" title="Reset all filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Active Filters Display -->
        <div id="activeFilters" class="active-filters" style="display: none;">
            <span class="filter-label">Active Filters:</span>
            <div id="filterTags" class="filter-tags"></div>
        </div>

        <!-- View Toggle -->
        <div class="view-toggle">
            <div class="view-buttons">
                <button type="button" class="view-btn active" data-view="grid">
                    <i class="bi bi-grid-3x3-gap"></i>
                    Grid
                </button>
                <button type="button" class="view-btn" data-view="list">
                    <i class="bi bi-list-ul"></i>
                    List
                </button>
            </div>
            <div class="results-count">
                <span id="resultsCount">0</span> events found
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="loading-state">
            <div class="spinner"></div>
            <p>Loading your events...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="empty-title">No Events Found</h3>
            <p class="empty-message">You haven't registered for any events yet.</p>
            <a href="{{ route('events.index') }}" class="btn-primary">
                <i class="bi bi-search"></i>
                Browse Events
            </a>
        </div>

        <!-- No Results State -->
        <div id="noResultsState" class="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="empty-title">No Matching Events</h3>
            <p class="empty-message">Try adjusting your filters or search terms.</p>
            <button type="button" id="clearFiltersBtn" class="btn-secondary">
                <i class="bi bi-arrow-counterclockwise"></i>
                Clear All Filters
            </button>
        </div>

        <!-- Events Grid/List -->
        <div id="eventsContainer" class="events-container grid-view">
            <!-- Events will be loaded here dynamically -->
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="pagination-container" style="display: none;">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Cancel Registration Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Cancel Registration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel your registration for <strong id="cancelEventTitle"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <span id="cancelWarningText">This action cannot be undone.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Keep Registration
                </button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bi bi-x-circle"></i>
                    Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/events/my-events.js')
@endpush