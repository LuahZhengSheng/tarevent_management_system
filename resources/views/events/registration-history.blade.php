@extends('layouts.app')

@section('title', 'Registration History - ' . $event->title)

@section('content')
<div class="registration-history-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header-custom">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('events.index') }}">Events</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('events.show', $event) }}">{{ Str::limit($event->title, 30) }}</a></li>
                    <li class="breadcrumb-item active">Registration History</li>
                </ol>
            </nav>

            <div class="header-content">
                <h1 class="page-title">
                    <i class="bi bi-clock-history me-2"></i>
                    Registration History
                </h1>
                <p class="page-subtitle">{{ $event->title }}</p>
            </div>
        </div>

        <!-- Event Info Card -->
        <div class="event-info-card">
            <div class="row align-items-center">
                <div class="col-md-2">
                    @if($event->poster_path)
                    <img src="{{ Storage::url('event-posters/'.$event->poster_path) }}" 
                         alt="{{ $event->title }}" 
                         class="event-thumbnail">
                    @else
                    <div class="event-thumbnail-placeholder">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h3 class="event-title">{{ $event->title }}</h3>
                    <div class="event-meta">
                        <span class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            {{ $event->start_time->format('d M Y, h:i A') }}
                        </span>
                        <span class="meta-item">
                            <i class="bi bi-geo-alt"></i>
                            {{ $event->venue }}
                        </span>
                        <span class="meta-item">
                            <i class="bi bi-bookmark"></i>
                            {{ $event->category }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="controls-section">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" 
                               class="form-control" 
                               id="searchInput" 
                               placeholder="Search by registration number...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending_payment">Pending Payment</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="waitlisted">Waitlisted</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" id="sortFilter">
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="applyFiltersBtn">
                        <i class="bi bi-funnel me-1"></i>
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Registration List -->
        <div class="registrations-container">
            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading registration history...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state d-none">
                <i class="bi bi-inbox"></i>
                <h4>No Registrations Found</h4>
                <p>No registration records match your search criteria.</p>
            </div>

            <!-- Results -->
            <div id="resultsContainer" class="d-none">
                <div class="results-header">
                    <span id="resultsCount">0 registrations found</span>
                </div>
                <div id="registrationsList" class="registrations-list">
                    <!-- Populated via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Registration Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Registration Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Populated via AJAX -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary d-none" id="downloadReceiptBtn">
                    <i class="bi bi-download me-2"></i>
                    Download Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/registration-history.css')
@endpush

@push('scripts')
@vite('resources/js/events/registration-history.js')
@endpush