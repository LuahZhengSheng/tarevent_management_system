@extends('layouts.app')

@section('title', 'Campus Clubs')

@push('styles')
@include('clubs.join-modal')
<style>
    .clubs-page-modern {
        min-height: 100vh;
        background: var(--bg-primary, #f8f9fa);
    }

    .hero-section-modern {
        position: relative;
        padding: 4rem 0 3rem;
        background: linear-gradient(135deg, var(--primary, #4f46e5) 0%, var(--primary-hover, #6366f1) 100%);
        color: white;
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 0;
    }

    .hero-pattern {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0.1;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .hero-gradient {
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.1) 100%);
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
    }

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.hero-description {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.95;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}

.stat-divider {
    width: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.container-modern {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.filter-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.filter-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.search-wrapper {
    width: 100%;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 1rem;
    color: #6c757d;
    font-size: 1.25rem;
}

.search-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary, #4f46e5);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.clear-search {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.filter-pills {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-pill {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
}

.filter-select, .filter-date {
    padding: 0.625rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 0.9375rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:focus, .filter-date:focus {
    outline: none;
    border-color: var(--primary, #4f46e5);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.filter-clear {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1rem;
    color: #dc3545;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9375rem;
    border: 2px solid #dc3545;
    border-radius: 6px;
    transition: all 0.2s;
    margin-left: auto;
}

.filter-clear:hover {
    background: #dc3545;
    color: white;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.results-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    margin: 0;
}

.status-filter-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.status-tab {
    padding: 0.625rem 1.25rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-tab:hover {
    border-color: var(--primary, #4f46e5);
    color: var(--primary, #4f46e5);
    background: var(--primary-light, #eef2ff);
}

.status-tab.active {
    background: var(--primary, #4f46e5);
    color: white;
    border-color: var(--primary, #4f46e5);
}

.status-tab i {
    font-size: 1rem;
}

.clubs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.club-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.club-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.club-card-header {
    position: relative;
    height: 180px;
    background: linear-gradient(135deg, var(--primary, #4f46e5) 0%, var(--primary-hover, #6366f1) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.club-logo-wrapper {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.club-logo-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.club-logo-wrapper i {
    font-size: 3rem;
    color: var(--primary, #4f46e5);
}

.club-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.club-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.club-category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--primary-light, #eef2ff);
    color: var(--primary, #4f46e5);
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.club-description {
    color: #6c757d;
    font-size: 0.9375rem;
    line-height: 1.6;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.club-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.club-stat {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.club-stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
}

.club-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.club-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: auto;
}

.club-status-available {
    background: #d1fae5;
    color: #065f46;
}

.club-status-member {
    background: #dbeafe;
    color: #1e40af;
}

.club-status-pending {
    background: #fef3c7;
    color: #92400e;
}

.club-status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.club-card-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    display: flex;
    gap: 0.75rem;
}

.btn-club-primary, .btn-club-secondary {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-club-primary {
    background: var(--primary, #4f46e5);
    color: white;
}

.btn-club-primary:hover {
    background: var(--primary-hover, #6366f1);
    color: white;
}

.btn-club-secondary {
    background: white;
    color: var(--primary, #4f46e5);
    border: 2px solid var(--primary, #4f46e5);
}

.btn-club-secondary:hover {
    background: var(--primary-light, #eef2ff);
}

.loading-state, .empty-state, .error-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.loading-state i, .empty-state i, .error-state i {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.loading-state h3, .empty-state h3, .error-state h3 {
    font-size: 1.5rem;
    color: #212529;
    margin-bottom: 0.5rem;
}

.loading-state p, .empty-state p, .error-state p {
    color: #6c757d;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .hero-description {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.95;
        line-height: 1.6;
    }

    .hero-stats {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.5rem;
    }

    .stat-divider {
        width: 1px;
        background: rgba(255, 255, 255, 0.3);
    }

    .container-modern {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .filter-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .search-wrapper {
        width: 100%;
    }

    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        color: #6c757d;
        font-size: 1.25rem;
    }

    .search-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary, #4f46e5);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .clear-search {
        position: absolute;
        right: 1rem;
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .filter-pills {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-pill {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
    }

    .filter-select, .filter-date {
        padding: 0.625rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        font-size: 0.9375rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus, .filter-date:focus {
        outline: none;
        border-color: var(--primary, #4f46e5);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .filter-clear {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1rem;
        color: #dc3545;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        border: 2px solid #dc3545;
        border-radius: 6px;
        transition: all 0.2s;
        margin-left: auto;
    }

    .filter-clear:hover {
        background: #dc3545;
        color: white;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .results-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        margin: 0;
    }

    .clubs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .club-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }

    .club-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .club-card-header {
        position: relative;
        height: 180px;
        background: linear-gradient(135deg, var(--primary, #4f46e5) 0%, var(--primary-hover, #6366f1) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .club-logo-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .club-logo-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .club-logo-wrapper i {
        font-size: 3rem;
        color: var(--primary, #4f46e5);
    }

    .club-card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .club-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .club-category-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--primary-light, #eef2ff);
        color: var(--primary, #4f46e5);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .club-description {
        color: #6c757d;
        font-size: 0.9375rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .club-stats {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .club-stat {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .club-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #212529;
    }

    .club-stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .club-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: auto;
    }

    .club-status-available {
        background: #d1fae5;
        color: #065f46;
    }

    .club-status-member {
        background: #dbeafe;
        color: #1e40af;
    }

    .club-status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .club-status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .club-card-footer {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        display: flex;
        gap: 0.75rem;
    }

    .btn-club-primary, .btn-club-secondary {
        flex: 1;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-club-primary {
        background: var(--primary, #4f46e5);
        color: white;
    }

    .btn-club-primary:hover {
        background: var(--primary-hover, #6366f1);
        color: white;
    }

    .btn-club-secondary {
        background: white;
        color: var(--primary, #4f46e5);
        border: 2px solid var(--primary, #4f46e5);
    }

    .btn-club-secondary:hover {
        background: var(--primary-light, #eef2ff);
    }

    .loading-state, .empty-state, .error-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .loading-state i, .empty-state i, .error-state i {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .loading-state h3, .empty-state h3, .error-state h3 {
        font-size: 1.5rem;
        color: #212529;
        margin-bottom: 0.5rem;
    }

    .loading-state p, .empty-state p, .error-state p {
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .clubs-grid {
            grid-template-columns: 1fr;
        }

        .filter-pills {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

@section('content')
<div class="clubs-page-modern">
    <!-- Hero Section -->
    <div class="hero-section-modern">
        <div class="hero-background">
            <div class="hero-pattern"></div>
            <div class="hero-gradient"></div>
        </div>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="hero-content">
                        <div class="hero-badge">
                            <i class="bi bi-stars me-2"></i>
                            <span id="totalClubsBadge">Discover Campus Clubs</span>
                        </div>
                        <h1 class="hero-title">Join Your Community</h1>
                        <p class="hero-description">
                            Explore diverse clubs, connect with like-minded students, and build lasting friendships. 
                            Find your passion and make your mark on campus.
                        </p>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-value" id="totalClubsStat">-</div>
                                <div class="stat-label">Total Clubs</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value" id="availableClubsStat">-</div>
                                <div class="stat-label">Available</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value" id="memberClubsStat">-</div>
                                <div class="stat-label">Your Clubs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-modern">
        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form action="{{ route('clubs.index') }}" method="GET" id="filterForm">
                <div class="filter-container">
                    <!-- Search Bar -->
                    <div class="search-wrapper">
                        <div class="search-input-group">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   id="searchInput"
                                   name="search" 
                                   placeholder="Search clubs by name or description..." 
                                   value="{{ $search }}">
                            @if($search)
                            <button type="button" class="clear-search" id="clearSearch">
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
                            <select class="filter-select" id="categoryFilter" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        @if($search || $category)
                        <a href="{{ route('clubs.index') }}" class="filter-clear">
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
                <h2 class="results-title" id="resultsTitle">
                    @if($search || $category)
                    Filtered Results
                    @else
                    All Clubs
                    @endif
                </h2>
                <p class="text-muted mb-0" id="resultsCount"></p>
            </div>
            <!-- Status Filter Tabs -->
            <div class="status-filter-tabs" id="statusFilterTabs">
                <button class="status-tab active" data-status="all" onclick="filterByStatus('all')">
                    <i class="bi bi-grid"></i> All
                </button>
                <button class="status-tab" data-status="joined" onclick="filterByStatus('joined')">
                    <i class="bi bi-check-circle"></i> Joined
                </button>
                <button class="status-tab" data-status="available" onclick="filterByStatus('available')">
                    <i class="bi bi-plus-circle"></i> Available
                </button>
                <button class="status-tab" data-status="pending" onclick="filterByStatus('pending')">
                    <i class="bi bi-clock"></i> Request Pending
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="loadingState">
            <i class="bi bi-arrow-repeat"></i>
            <h3>Loading Clubs...</h3>
            <p>Please wait while we fetch the latest clubs</p>
        </div>

        <!-- Error State -->
        <div class="error-state" id="errorState" style="display: none;">
            <i class="bi bi-exclamation-triangle"></i>
            <h3>Something went wrong</h3>
            <p id="errorMessage">Failed to load clubs. Please try again later.</p>
            <button class="btn-club-primary mt-3" onclick="loadClubs()">Retry</button>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-inbox"></i>
            <h3>No clubs found</h3>
            <p>Try adjusting your search or filters</p>
        </div>

        <!-- Clubs Grid -->
        <div class="clubs-grid" id="clubsGrid"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Define openJoinModal in global scope immediately so buttons can access it
window.openJoinModal = function(clubId) {
    console.log('openJoinModal called with clubId:', clubId);
    
    // Check if join modal is available
    if (typeof window.openJoinClubModal === 'function') {
        console.log('Calling window.openJoinClubModal');
        window.openJoinClubModal(clubId, function(joinedClubId) {
            console.log('Join modal callback triggered for club:', joinedClubId);
            // Reload clubs data to refresh button status
            if (typeof window.loadClubs === 'function') {
                window.loadClubs();
            } else {
                // Fallback: reload page
                window.location.reload();
            }
        });
    } else {
        console.warn('window.openJoinClubModal not available, waiting...');
        // Wait a bit for modal to initialize, then try again
        setTimeout(function() {
            if (typeof window.openJoinClubModal === 'function') {
                window.openJoinClubModal(clubId, function(joinedClubId) {
                    console.log('Join modal callback triggered for club:', joinedClubId);
                    if (typeof window.loadClubs === 'function') {
                        window.loadClubs();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                console.error('Join modal still not available, redirecting...');
                // Fallback: redirect to club detail page
                window.location.href = `/clubs/${clubId}`;
            }
        }, 500);
    }
};

(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let allClubs = [];
    let filteredClubs = [];
    let currentStatusFilter = 'all'; // 'all', 'joined', 'available', 'pending'

    // Generate timestamp in IFA format (YYYY-MM-DD HH:MM:SS)
    function generateTimestamp() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    // Load clubs from API
    function loadClubs() {
        const loadingState = document.getElementById('loadingState');
        const errorState = document.getElementById('errorState');
        const emptyState = document.getElementById('emptyState');
        const clubsGrid = document.getElementById('clubsGrid');

        loadingState.style.display = 'block';
        errorState.style.display = 'none';
        emptyState.style.display = 'none';
        clubsGrid.innerHTML = '';

        const timestamp = generateTimestamp();
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';
        const url = `/api/clubs/available?timestamp=${encodeURIComponent(timestamp)}${categoryFilter ? `&category=${encodeURIComponent(categoryFilter)}` : ''}`;

        // Get Bearer token from localStorage
        const token = localStorage.getItem('api_token') || '';

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': `Bearer ${token}`
            },
            credentials: 'same-origin',
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                // Handle authentication errors
                if (response.status === 401) {
                    throw new Error('Please login to view clubs. Your session may have expired.');
                }
                throw new Error(data.message || 'Failed to load clubs');
            }

            if (data.success && data.data && data.data.clubs) {
                allClubs = data.data.clubs;
                filterAndRenderClubs();
                updateStats();
            } else {
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error loading clubs:', error);
            loadingState.style.display = 'none';
            errorState.style.display = 'block';
            let errorMessage = error.message;
            
            // Provide more helpful error messages
            if (errorMessage.includes('Unauthenticated') || errorMessage.includes('login')) {
                errorMessage = 'Please login to view clubs. If you are already logged in, please refresh the page.';
            }
            
            document.getElementById('errorMessage').textContent = errorMessage;
        });
    }

    // Filter clubs based on search and status
    function filterAndRenderClubs() {
        const searchInput = document.getElementById('searchInput');
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        
        filteredClubs = allClubs.filter(club => {
            // Apply status filter
            if (currentStatusFilter !== 'all') {
                if (currentStatusFilter === 'joined' && club.join_status !== 'member') {
                    return false;
                }
                if (currentStatusFilter === 'available' && club.join_status !== 'available') {
                    return false;
                }
                if (currentStatusFilter === 'pending' && club.join_status !== 'pending') {
                    return false;
                }
            }
            
            // Apply search filter
            if (searchTerm) {
                return club.name.toLowerCase().includes(searchTerm) ||
                       (club.description && club.description.toLowerCase().includes(searchTerm));
            }
            
            return true;
        });

        // Sort: Put member clubs first, then others
        filteredClubs.sort((a, b) => {
            // If both are members or both are not members, maintain original order
            const aIsMember = a.join_status === 'member';
            const bIsMember = b.join_status === 'member';
            
            if (aIsMember && !bIsMember) return -1; // a comes first
            if (!aIsMember && bIsMember) return 1;  // b comes first
            return 0; // maintain order for others
        });

        renderClubs();
    }

    // Filter by status
    function filterByStatus(status) {
        currentStatusFilter = status;
        
        // Update active tab
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.status === status) {
                tab.classList.add('active');
            }
        });
        
        // Re-filter and render
        filterAndRenderClubs();
    }

    // Render clubs grid
    function renderClubs() {
        const clubsGrid = document.getElementById('clubsGrid');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const resultsCount = document.getElementById('resultsCount');
        const resultsTitle = document.getElementById('resultsTitle');

        loadingState.style.display = 'none';

        if (filteredClubs.length === 0) {
            clubsGrid.innerHTML = '';
            emptyState.style.display = 'block';
            resultsCount.textContent = '0 clubs found';
            return;
        }

        emptyState.style.display = 'none';
        resultsCount.textContent = `${filteredClubs.length} club${filteredClubs.length !== 1 ? 's' : ''} found`;

        const categoryLabels = {
            'academic': 'Academic',
            'sports': 'Sports',
            'cultural': 'Cultural',
            'social': 'Social',
            'volunteer': 'Volunteer',
            'professional': 'Professional',
            'other': 'Other'
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        clubsGrid.innerHTML = filteredClubs.map((club) => {
                // API already returns logo with /storage/ prefix
                const logoUrl = club.logo
                        ? (club.logo.startsWith('http://') || club.logo.startsWith('https://') || club.logo.startsWith('/storage/') 
                           ? club.logo 
                           : `/storage/${club.logo}`)
                        : null;

                const logoHtml = logoUrl
                        ? `<img src="${logoUrl}" alt="${escapeHtml(club.name)}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                        : '';

                const categoryBadge = club.category
                        ? `<span class="club-category-badge">${categoryLabels[club.category] || club.category}</span>`
                        : '';

                let statusBadge = '';
                let actionButton = '';

                switch (club.join_status) {
                    case 'available':
                        statusBadge = '<span class="club-status-badge club-status-available">Available</span>';
                        actionButton = `<button class="btn-club-primary" onclick="openJoinModal(${club.id})">Join Club</button>`;
                        break;
                    case 'member':
                        statusBadge = '<span class="club-status-badge club-status-member">Member</span>';
                        actionButton = '';
                        break;
                    case 'pending':
                        statusBadge = '<span class="club-status-badge club-status-pending">Request Pending</span>';
                        actionButton = '';
                        break;
                    case 'rejected':
                    {
                        const cooldownText = (club.cooldown_remaining_days != null || club.cooldownremainingdays != null) 
                            ? ` (${(club.cooldown_remaining_days || club.cooldownremainingdays)} days left)` 
                            : '';
                        statusBadge = `<span class="club-status-badge club-status-rejected">Rejected${cooldownText}</span>`;
                        actionButton = '';
                        break;
                    }
                    default:
                        statusBadge = '<span class="club-status-badge club-status-available">Available</span>';
                        actionButton = `<button class="btn-club-primary" onclick="openJoinModal(${club.id})">Join Club</button>`;
                        break;
                }

                // 用字符串拼接（不使用多行反引号模板），避免漏关反引号导致语法错
                const viewDetailsHtml =
                        '<form method="POST" action="/club/select" class="w-100">' +
                        `<input type="hidden" name="_token" value="${csrf}">` +
                        `<input type="hidden" name="club_id" value="${club.id}">` +
                        '<button type="submit" class="btn-club-secondary w-100">View Details</button>' +
                        '</form>';

                const footerHtml = actionButton || viewDetailsHtml;

                return `
      <div class="club-card" onclick="window.location.href='/clubs/${club.id}'">
        <div class="club-card-header">
          <div class="club-logo-wrapper">
            ${logoHtml}
            <i class="bi bi-people" style="display:${logoUrl ? 'none' : 'flex'}"></i>
          </div>
        </div>

        <div class="club-card-body">
          <div class="club-name">
            ${escapeHtml(club.name)}
            ${categoryBadge}
          </div>

          <p class="club-description">${escapeHtml(club.description || 'No description available.')}</p>

                        <div class="club-stats">
                            <div class="club-stat">
                                <div class="club-stat-value">${club.members_count || 0}</div>
                                <div class="club-stat-label">Members</div>
                            </div>
                            <div class="club-stat">
                                <div class="club-stat-value">${club.events_count || 0}</div>
                                <div class="club-stat-label">Events</div>
                            </div>
                        </div>

          ${statusBadge}
        </div>

        <div class="club-card-footer" onclick="event.stopPropagation();">
          ${footerHtml}
        </div>
      </div>
                `;
            }).join('');
    }

    // Update a single club's status in the list
    function updateClubStatus(clubId, newStatus) {
        console.log('Updating club status:', clubId, newStatus);
        // Find the club in allClubs array
        const club = allClubs.find(c => c.id === clubId);
        if (club) {
            console.log('Found club, updating status from', club.join_status, 'to', newStatus);
            // Update the club's join_status
            club.join_status = newStatus;
            // Re-render the clubs grid immediately
            filterAndRenderClubs();
            console.log('Club status updated and rendered');
        } else {
            console.warn('Club not found in allClubs:', clubId);
        }
    }

    // Open join modal - Make sure it's available globally immediately
    window.openJoinModal = function(clubId) {
        console.log('openJoinModal called with clubId:', clubId);
        
        // Check if join modal is available
        if (typeof window.openJoinClubModal === 'function') {
            console.log('Calling window.openJoinClubModal');
            window.openJoinClubModal(clubId, function(joinedClubId) {
                console.log('Join modal callback triggered for club:', joinedClubId);
                // Reload clubs data to refresh button status
                loadClubs();
            });
        } else {
            console.warn('window.openJoinClubModal not available, waiting...');
            // Wait a bit for modal to initialize, then try again
            setTimeout(function() {
                        if (typeof window.openJoinClubModal === 'function') {
                            window.openJoinClubModal(clubId, function(joinedClubId) {
                                console.log('Join modal callback triggered for club:', joinedClubId);
                                if (typeof window.loadClubs === 'function') {
                                    window.loadClubs();
                                } else {
                                    window.location.reload();
                                }
                            });
                } else {
                    console.error('Join modal still not available, redirecting...');
                    // Fallback: redirect to club detail page
                    window.location.href = `/clubs/${clubId}`;
                }
            }, 500);
        }
    };

    // Make other functions available globally
    window.loadClubs = loadClubs;
    window.filterByStatus = filterByStatus;

//        function renderClubs() {
//            const clubsGrid = document.getElementById('clubsGrid');
//            const loadingState = document.getElementById('loadingState');
//            const emptyState = document.getElementById('emptyState');
//            const resultsCount = document.getElementById('resultsCount');
//            const resultsTitle = document.getElementById('resultsTitle');
//
//            loadingState.style.display = 'none';
//
//            if (filteredClubs.length === 0) {
//                clubsGrid.innerHTML = '';
//                emptyState.style.display = 'block';
//                resultsCount.textContent = '0 clubs found';
//                return;
//            }
//
//            emptyState.style.display = 'none';
//            resultsCount.textContent = `${filteredClubs.length} club${filteredClubs.length !== 1 ? 's' : ''} found`;
//
//            const categoryLabels = {
//                'academic': 'Academic',
//                'sports': 'Sports',
//                'cultural': 'Cultural',
//                'social': 'Social',
//                'volunteer': 'Volunteer',
//                'professional': 'Professional',
//                'other': 'Other'
//            };
//
//            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
//
//            const viewDetailsHtml = `
//  <form method="POST" action="/club/select" class="w-100">
//    <input type="hidden" name="_token" value="${csrf}">
//    <input type="hidden" name="club_id" value="${club.id}">
//    <button type="submit" class="btn-club-secondary w-100">View Details</button>
//  </form>
//`;
//
//
//            clubsGrid.innerHTML = filteredClubs.map(club => {
//                const logoUrl = club.logo
//                        ? (club.logo.startsWith('http') ? club.logo : `/storage/${club.logo}`)
//                        : null;
//
//                const logoHtml = logoUrl
//                        ? `<img src="${logoUrl}" alt="${escapeHtml(club.name)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
//                        : '';
//
//                const categoryBadge = club.category
//                        ? `<span class="club-category-badge">${categoryLabels[club.category] || club.category}</span>`
//                        : '';
//
//                let statusBadge = '';
//                let actionButton = '';
//
//                switch (club.join_status) {
//                    case 'available':
//                        statusBadge = '<span class="club-status-badge club-status-available">Available</span>';
//                        actionButton = `<button class="btn-club-primary" onclick="openJoinModal(${club.id})">Join Club</button>`;
//                        break;
//                    case 'member':
//                        statusBadge = '<span class="club-status-badge club-status-member">Member</span>';
//                        actionButton = '';
//                        break;
//                    case 'pending':
//                        statusBadge = '<span class="club-status-badge club-status-pending">Request Pending</span>';
//                        actionButton = '';
//                        break;
//                    case 'rejected':
//                        const cooldownText = club.cooldown_remaining_days !== null
//                                ? ` (${club.cooldown_remaining_days} days left)`
//                                : '';
//                        statusBadge = `<span class="club-status-badge club-status-rejected">Rejected${cooldownText}</span>`;
//                        actionButton = '';
//                        break;
//                    default:
//                        statusBadge = '<span class="club-status-badge club-status-available">Available</span>';
//                        actionButton = `<button class="btn-club-primary" onclick="openJoinModal(${club.id})">Join Club</button>`;
//                }
//
//                return `
//                <div class="club-card" onclick="window.location.href='/clubs/${club.id}'">
//                    <div class="club-card-header">
//                        <div class="club-logo-wrapper">
//                            ${logoHtml}
//                            <i class="bi bi-people" style="display: ${logoUrl ? 'none' : 'flex'}"></i>
//                        </div>
//                    </div>
//                    <div class="club-card-body">
//                        <div class="club-name">
//                            ${escapeHtml(club.name)}
//                            ${categoryBadge}
//                        </div>
//                        <p class="club-description">${escapeHtml(club.description || 'No description available.')}</p>
//                        <div class="club-stats">
//                            <div class="club-stat">
//                                <div class="club-stat-value">${club.members_count || 0}</div>
//                                <div class="club-stat-label">Members</div>
//                            </div>
//                            <div class="club-stat">
//                                <div class="club-stat-value">${club.events_count || 0}</div>
//                                <div class="club-stat-label">Events</div>
//                            </div>
//                        </div>
//                        ${statusBadge}
//                    </div>
////                    <div class="club-card-footer" onclick="event.stopPropagation();">
////                        ${actionButton || `<a href="/clubs/${club.id}" class="btn-club-secondary">View Details</a>`}
////                    </div>
//                      <div class="club-card-footer" onclick="event.stopPropagation();">
//                          ${actionButton || viewDetailsHtml}
//                      </div>
//
//                </div>
//            `;
//            }).join('');
//        }

        // Update statistics
        function updateStats() {
            const totalClubs = allClubs.length;
            const availableClubs = allClubs.filter(c => c.join_status === 'available').length;
            const memberClubs = allClubs.filter(c => c.join_status === 'member').length;

            document.getElementById('totalClubsStat').textContent = totalClubs;
            document.getElementById('availableClubsStat').textContent = availableClubs;
            document.getElementById('memberClubsStat').textContent = memberClubs;
            document.getElementById('totalClubsBadge').textContent = `${totalClubs} Clubs Available`;
        }

        // Open join modal
        function openJoinModal(clubId) {
            if (typeof window.openJoinClubModal === 'function') {
                window.openJoinClubModal(clubId, function (joinedClubId) {
                    // Reload clubs after successful join
                    loadClubs();
                });
            } else {
                // Fallback: redirect to club detail page
                window.location.href = `/clubs/${clubId}`;
            }
        }

        // Make loadClubs available globally
        window.loadClubs = loadClubs;
        window.openJoinModal = openJoinModal;

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function () {
            loadClubs();

            // Search input
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        filterAndRenderClubs();
                    }, 300);
                });
            }

            // Category filter
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function () {
                    loadClubs();
                });
            }

            // Clear search
            const clearSearch = document.getElementById('clearSearch');
            if (clearSearch) {
                clearSearch.addEventListener('click', function () {
                    searchInput.value = '';
                    filterAndRenderClubs();
                });
            }
        });
    })();
</script>
@endpush

