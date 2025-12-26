@extends('layouts.app')

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
                            <span>{{ $events->total() }} Events Available</span>
                        </div>
                        <h1 class="hero-title">Discover Campus Events</h1>
                        <p class="hero-description">
                            Explore exciting workshops, seminars, and activities. Connect with peers and expand your horizons.
                        </p>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ $events->total() }}</div>
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
        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form action="{{ route('events.index') }}" method="GET" id="filterForm">
                <div class="filter-container">
                    <!-- Search Bar -->
                    <div class="search-wrapper">
                        <div class="search-input-group">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   name="search" 
                                   placeholder="Search events by title or description..." 
                                   value="{{ request('search') }}">
                            @if(request('search'))
                            <button type="button" class="clear-search">
                                <i class="bi bi-x"></i>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Filter Pills -->
                    <div class="filter-pills">
                        <!-- Category Filter -->
                        <div class="filter-pill">
                            <label class="filter-label">
                                <i class="bi bi-bookmark me-2"></i>Category
                            </label>
                            <select class="filter-select" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fee Type Filter -->
                        <div class="filter-pill">
                            <label class="filter-label">
                                <i class="bi bi-cash-coin me-2"></i>Price
                            </label>
                            <select class="filter-select" name="fee_type">
                                <option value="">All Events</option>
                                <option value="free" {{ request('fee_type') == 'free' ? 'selected' : '' }}>
                                    Free Only
                                </option>
                                <option value="paid" {{ request('fee_type') == 'paid' ? 'selected' : '' }}>
                                    Paid Only
                                </option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="filter-pill">
                            <label class="filter-label">
                                <i class="bi bi-calendar-range me-2"></i>From Date
                            </label>
                            <input type="date" 
                                   class="filter-date" 
                                   name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>

                        <!-- Clear Filters -->
                        @if(request()->hasAny(['search', 'category', 'fee_type', 'start_date']))
                        <a href="{{ route('events.index') }}" class="filter-clear">
                            <i class="bi bi-x-circle me-2"></i>Clear All
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-info">
                <h2 class="results-title">
                    @if(request()->hasAny(['search', 'category', 'fee_type', 'start_date']))
                    Filtered Results
                    @else
                    All Events
                    @endif
                </h2>
                <p class="results-count">
                    Showing {{ $events->count() }} of {{ $events->total() }} {{ Str::plural('event', $events->total()) }}
                </p>
            </div>
            <div class="view-controls">
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="Grid View">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="view-btn" data-view="list" title="List View">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
                <div class="sort-select">
                    <select class="form-select-modern">
                        <option value="date_asc">Date: Earliest First</option>
                        <option value="date_desc">Date: Latest First</option>
                        <option value="title">Title: A-Z</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Events Grid -->
        <div id="eventsContainer" class="events-grid view-grid">
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
                            <span>{{ $event->category instanceof \App\Enums\EventCategory ? $event->category->value : $event->category }}</span>
                        </div>
                        @endif

                        <!-- Image Overlay Tags -->
                        <div class="event-tags">
                            <span class="tag tag-category">{{ $event->category }}</span>
                            {{-- Private Event Badge --}}
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
                        <div class="status-badge status-upcoming" style="background-color: #003366; color: white; border-color: #0dcaf0;">
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
                        <!-- Event Title -->
                        <h3 class="event-title">
                            <a href="{{ route('events.show', $event) }}">
                                {{ $event->title }}
                            </a>
                        </h3>

                        <!-- Event Description -->
                        <p class="event-description">
                            {{ Str::limit($event->description, 100) }}
                        </p>

                        <!-- Event Info Grid -->
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
                            Details
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
                        We couldn't find any events matching your criteria. 
                        Try adjusting your filters or check back later.
                    </p>
                    @if(request()->hasAny(['search', 'category', 'fee_type', 'start_date']))
                    <a href="{{ route('events.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        Reset Filters
                    </a>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
        <div class="pagination-wrapper">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
@vite('resources/css/events/events-index-modern.css')
@endpush

@push('scripts')
<script>
    window.eventsConfig = {
        fetchUrl: "{{ route('events.fetch') }}",
        showUrl: "{{ route('events.show', ':id') }}"
    };
</script>
@vite('resources/js/events/events-index.js')
@endpush

@endsection