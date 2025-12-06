@extends('layouts.app')

@section('title', 'Campus Events')

@section('content')
<div class="events-page">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title mb-3">Discover Campus Events</h1>
                    <p class="hero-subtitle">Explore exciting events, workshops, and activities happening around campus</p>
                </div>
                @auth
                    @if(auth()->user()->hasRole('club'))
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('events.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Create Event
                        </a>
                    </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Search and Filter Section -->
        <div class="card shadow-sm mb-4 filter-card">
            <div class="card-body">
                <form action="{{ route('events.index') }}" method="GET" id="filterForm">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Search events..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-3">
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fee Type Filter -->
                        <div class="col-md-3">
                            <select class="form-select" name="fee_type" onchange="this.form.submit()">
                                <option value="">All Events</option>
                                <option value="free" {{ request('fee_type') == 'free' ? 'selected' : '' }}>
                                    Free Events
                                </option>
                                <option value="paid" {{ request('fee_type') == 'paid' ? 'selected' : '' }}>
                                    Paid Events
                                </option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="col-md-2">
                            <input type="date" 
                                   class="form-control" 
                                   name="start_date" 
                                   value="{{ request('start_date') }}"
                                   onchange="this.form.submit()">
                        </div>
                    </div>

                    @if(request()->hasAny(['search', 'category', 'fee_type', 'start_date']))
                    <div class="mt-3">
                        <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Events Count -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event me-2 text-primary"></i>
                {{ $events->total() }} {{ Str::plural('Event', $events->total()) }} Found
            </h5>
            <div class="view-toggle">
                <button class="btn btn-sm btn-outline-secondary active" data-view="grid">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" data-view="list">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <!-- Events Grid -->
        <div id="eventsContainer" class="row g-4 view-grid">
            @forelse($events as $event)
            <div class="col-lg-4 col-md-6 event-item">
                <div class="card event-card h-100">
                    <!-- Event Poster -->
                    <div class="event-poster">
                        @if($event->poster_path)
                            <img src="{{ Storage::url($event->poster_path) }}" 
                                 alt="{{ $event->title }}" 
                                 class="card-img-top">
                        @else
                            <div class="placeholder-poster">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                        @endif

                        <!-- Event Badges -->
                        <div class="event-badges">
                            <span class="badge bg-primary">{{ $event->category }}</span>
                            @if($event->is_paid)
                                <span class="badge bg-success">{{ $event->formatted_fee }}</span>
                            @else
                                <span class="badge bg-info">Free</span>
                            @endif
                        </div>

                        <!-- Registration Status -->
                        @if($event->is_full)
                            <div class="event-status-badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>Full
                            </div>
                        @elseif($event->is_registration_open)
                            <div class="event-status-badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Open
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <!-- Event Title -->
                        <h5 class="card-title event-title">
                            <a href="{{ route('events.show', $event) }}" class="text-decoration-none">
                                {{ $event->title }}
                            </a>
                        </h5>

                        <!-- Event Meta -->
                        <div class="event-meta">
                            <div class="meta-item">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <span>{{ $event->start_time->format('d M Y') }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-clock text-primary"></i>
                                <span>{{ $event->start_time->format('h:i A') }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <span>{{ Str::limit($event->venue, 20) }}</span>
                            </div>
                            @if($event->max_participants)
                            <div class="meta-item">
                                <i class="bi bi-people text-primary"></i>
                                <span>{{ $event->remaining_seats }} / {{ $event->max_participants }} left</span>
                            </div>
                            @endif
                        </div>

                        <!-- Event Description -->
                        <p class="card-text text-muted mt-3">
                            {{ Str::limit($event->description, 100) }}
                        </p>
                    </div>

                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>
                                {{ $event->organizer->name ?? 'TARCampus' }}
                            </small>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                View Details <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x display-1 text-muted"></i>
                        <h4 class="mt-3">No Events Found</h4>
                        <p class="text-muted">Try adjusting your filters or check back later for new events.</p>
                        @if(request()->hasAny(['search', 'category', 'fee_type', 'start_date']))
                        <a href="{{ route('events.index') }}" class="btn btn-primary mt-3">
                            Clear Filters
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
        <div class="mt-5">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 4rem 0;
    margin-bottom: 2rem;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.95;
}

.filter-card {
    border: none;
    border-radius: 1rem;
}

.event-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl) !important;
}

.event-poster {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.event-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card:hover .event-poster img {
    transform: scale(1.1);
}

.placeholder-poster {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-light), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
}

.placeholder-poster i {
    font-size: 4rem;
    color: var(--primary);
    opacity: 0.3;
}

.event-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.event-badges .badge {
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    border-radius: 0.5rem;
}

.event-status-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}

.event-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.event-title a {
    color: var(--text-primary);
    transition: color 0.3s ease;
}

.event-title a:hover {
    color: var(--primary);
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.meta-item i {
    font-size: 1rem;
}

.view-toggle button {
    transition: all 0.3s ease;
}

.view-toggle button.active {
    background-color: var(--primary);
    color: white;
    border-color: var(--primary);
}

.view-list .col-lg-4 {
    flex: 0 0 100%;
    max-width: 100%;
}

.view-list .event-card {
    display: flex;
    flex-direction: row;
}

.view-list .event-poster {
    width: 300px;
    height: auto;
}

.empty-state {
    padding: 3rem;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .view-list .event-card {
        flex-direction: column;
    }

    .view-list .event-poster {
        width: 100%;
        height: 200px;
    }
}

/* Dark Mode Adjustments */
[data-theme="dark"] .filter-card,
[data-theme="dark"] .event-card {
    background-color: var(--bg-secondary);
}

[data-theme="dark"] .placeholder-poster {
    background: linear-gradient(135deg, var(--bg-tertiary), var(--primary-light));
}
</style>

<script>
$(function () {
    const $viewButtons = $('.view-toggle button');
    const $container = $('#eventsContainer');

    $viewButtons.on('click', function () {
        $viewButtons.removeClass('active');
        $(this).addClass('active');

        const view = $(this).data('view');
        if (view === 'grid') {
            $container.attr('class', 'row g-4 view-grid');
        } else {
            $container.attr('class', 'row g-4 view-list');
        }
    });
});
</script>
@endsection