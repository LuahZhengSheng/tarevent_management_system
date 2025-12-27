@extends(auth()->check() && auth()->user()->hasRole('club') ? 'layouts.club' : 'layouts.app')

@section('title', 'Campus Events')

@section('content')
<div class="events-page-modern">
    <!-- Hero Section -->
    <div class="hero-section-modern">
        <div class="hero-background">
            <div class="hero-pattern"></div>
            <div class="hero-gradient"></div>
        </div>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="hero-content">
                        <div class="hero-badge">
                            <i class="bi bi-stars me-2"></i>
                            <span id="totalEventsCount">{{ $events->total() }} Events Available</span>
                        </div>
                        <h1 class="hero-title">Discover Campus Events</h1>
                        <p class="hero-description">
                            Explore exciting workshops, seminars, and activities. Connect with peers and expand your horizons.
                        </p>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-value" id="heroTotalEvents">{{ $events->total() }}</div>
                                <div class="stat-label">Total Events</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value">{{ count($categories) }}</div>
                                <div class="stat-label">Categories</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value">Free</div>
                                <div class="stat-label">Join Now</div>
                            </div>
                        </div>
                    </div>
                </div>
                @auth
                @if(auth()->user()->hasRole('club'))
                <div class="col-lg-5">
                    <div class="hero-cta">
                        <div class="cta-card">
                            <div class="cta-icon">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <h3>Create Your Event</h3>
                            <p>Share your event with the entire campus community</p>
                            <a href="{{ route('events.create') }}" class="btn-cta">
                                Create Event
                                <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="container-modern">
        <!-- Enhanced Filter Section (No form tag - using AJAX) -->
        <div class="filter-section">
            <div class="filter-container">
                <!-- Search Bar -->
                <div class="search-wrapper">
                    <div class="search-input-group">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" 
                               id="searchInput"
                               class="search-input" 
                               placeholder="Search events by title or description..." 
                               value="">
                        <button type="button" class="clear-search" style="display: none;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                <!-- Filter Pills -->
                <div class="filter-pills">
                    <!-- Category Filter -->
                    <div class="filter-pill">
                        <label class="filter-label">
                            <i class="bi bi-bookmark me-2"></i>Category
                        </label>
                        <select class="filter-select" id="categoryFilter">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Organizer (Club) Filter -->
                    <div class="filter-pill">
                        <label class="filter-label">
                            <i class="bi bi-building me-2"></i>Organizer
                        </label>
                        <select class="filter-select" id="organizerFilter" style="min-width: 100px">
                            <option value="all">All Clubs</option>
                            @foreach($clubs as $clubId => $clubName)
                            <option value="{{ $clubId }}">{{ $clubName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Visibility Filter -->
                    <div class="filter-pill">
                        <label class="filter-label">
                            <i class="bi bi-eye me-2"></i>Visibility
                        </label>
                        <select class="filter-select" id="visibilityFilter">
                            <option value="all">All Events</option>
                            <option value="public">Public Only</option>
                            <option value="private">Club Members Only</option>
                        </select>
                    </div>

                    <!-- Fee Type Filter -->
                    <div class="filter-pill">
                        <label class="filter-label">
                            <i class="bi bi-cash-coin me-2"></i>Price
                        </label>
                        <select class="filter-select" id="feeTypeFilter" style="min-width: 100px">
                            <option value="all">All Events</option>
                            <option value="free">Free Only</option>
                            <option value="paid">Paid Only</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button type="button" class="filter-clear" id="clearAllFilters" style="display: none;">
                        <i class="bi bi-x-circle me-2"></i>Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Header with Sort -->
        <div class="results-header">
            <div class="results-info">
                <h2 class="results-title" id="resultsTitle">All Events</h2>
                <p class="results-count">
                    Showing <span id="resultsCount">{{ $events->count() }}</span> of <span id="totalCount">{{ $events->total() }}</span> events
                </p>
            </div>
            <div class="view-controls">
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
                <div class="sort-select">
                    <select class="form-select-modern" id="sortSelect">
                        <option value="date_asc">Date: Earliest First</option>
                        <option value="date_desc">Date: Latest First</option>
                        <option value="title_asc">Title: A-Z</option>
                        <option value="title_desc">Title: Z-A</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Active Filters Display -->
        <div class="active-filters-display" id="activeFilters" style="display: none;">
            <span class="filters-label">Active Filters:</span>
            <div class="filter-tags" id="filterTags"></div>
        </div>

        <!-- Events Grid -->
        <div id="eventsContainer" class="events-grid view-grid">
            <!-- Initial server-rendered events -->
            @forelse($events as $event)
            <div class="event-card-wrapper">
                <article class="event-card-modern">
                    <!-- Event Image -->
                    <div class="event-image">
                        @if($event->poster_path)
                        <img src="{{ Storage::url('event-posters/'.$event->poster_path) }}" 
                             alt="{{ $event->title }}"
                             loading="lazy">
                        @else
                        <div class="image-placeholder">
                            <i class="bi bi-calendar-event"></i>
                            <span>{{ $event->category }}</span>
                        </div>
                        @endif

                        <!-- Image Overlay Tags -->
                        <div class="event-tags">
                            <span class="tag tag-category">{{ $event->category }}</span>
                            @if(!$event->is_public)
                            <span class="tag tag-private">
                                <i class="bi bi-lock-fill me-1"></i>
                                Private
                            </span>
                            @endif
                            @if($event->is_paid)
                            <span class="tag tag-price">RM {{ number_format($event->fee_amount, 2) }}</span>
                            @else
                            <span class="tag tag-free">Free</span>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        @if($event->is_full)
                        <div class="status-badge status-full">
                            <i class="bi bi-x-circle"></i>
                            <span>Full</span>
                        </div>
                        @elseif($event->is_registration_open)
                        <div class="status-badge status-open">
                            <i class="bi bi-check-circle"></i>
                            <span>Open</span>
                        </div>
                        @elseif($event->registration_start_time > now())
                        <div class="status-badge status-upcoming" style="background-color: #003366; color: white;">
                            <i class="bi bi-hourglass-split"></i>
                            <span>Upcoming</span>
                        </div>
                        @else
                        <div class="status-badge status-closed">
                            <i class="bi bi-clock"></i>
                            <span>Closed</span>
                        </div>
                        @endif

                        <!-- Quick Action -->
                        <a href="{{ route('events.show', $event) }}" class="quick-view">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Event Content -->
                    <div class="event-content">
                        <h3 class="event-title">
                            <a href="{{ route('events.show', $event) }}">
                                {{ $event->title }}
                            </a>
                        </h3>

                        <p class="event-description">
                            {{ Str::limit($event->description, 100) }}
                        </p>

                        <div class="event-info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Date</div>
                                    <div class="info-value">{{ $event->start_time->format('d M Y') }}</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Time</div>
                                    <div class="info-value">{{ $event->start_time->format('h:i A') }}</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Venue</div>
                                    <div class="info-value">{{ Str::limit($event->venue, 20) }}</div>
                                </div>
                            </div>

                            @if($event->max_participants)
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Capacity</div>
                                    <div class="info-value">{{ $event->remaining_seats }} / {{ $event->max_participants }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Event Footer -->
                    <div class="event-footer">
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="organizer-name">
                                {{ $event->organizer->name ?? 'TARCampus' }}
                            </div>
                        </div>
                        <a href="{{ route('events.show', $event) }}" class="btn-details">
                            View Details
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            @empty
            <div class="empty-state-wrapper">
                <div class="empty-state-modern">
                    <div class="empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="empty-title">No Events Found</h3>
                    <p class="empty-description">
                        There are currently no events available. Check back later for upcoming events!
                    </p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationContainer">
            @if($events->hasPages())
                {{ $events->links() }}
            @endif
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/events-index-modern.css')
<style>
/* Active Filters Display */
.active-filters-display {
    background: var(--bg-primary);
    border-radius: 0.875rem;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filters-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    background-color: var(--primary-light);
    color: var(--primary);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.filter-tag i {
    font-size: 0.875rem;
}

.remove-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    background-color: var(--primary);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: 0.25rem;
    border: none;
    padding: 0;
}

.remove-tag:hover {
    background-color: var(--primary-hover);
    transform: scale(1.1);
}

.remove-tag i {
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .active-filters-display {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endpush

@push('scripts')
<script>
window.eventsConfig = {
    fetchUrl: "{{ route('events.fetch') }}",
    showUrl: "{{ route('events.show', ':id') }}",
    clubs: @json($clubs)
};
</script>
@vite('resources/js/events/events-index.js')
@endpush

@endsection