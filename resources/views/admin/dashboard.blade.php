@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="admin-dashboard">
    <div class="admin-dashboard-header mb-4">
        <h1 class="admin-dashboard-title">Admin Dashboard</h1>
        <p class="admin-dashboard-subtitle">
            Overview of events, users, and system activity in TAREvent.
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-primary">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Active Events</div>
                    <div class="admin-stat-value">24</div>
                    <div class="admin-stat-meta text-muted">Including pending approvals</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Total Users</div>
                    <div class="admin-stat-value">1,432</div>
                    <div class="admin-stat-meta text-muted">Students & club admins</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div class="admin-stat-icon bg-warning">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">Registrations Today</div>
                    <div class="admin-stat-value">56</div>
                    <div class="admin-stat-meta text-muted">Across all events</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-panel-card">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Recent Events</h2>
                    <a href="{{ route('admin.events.index') }}" class="admin-panel-link">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Event</th>
                                <th scope="col">Organizer</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Registrations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Tech Talk: AI in Education</div>
                                    <div class="text-muted small">Tomorrow, 3:00 PM</div>
                                </td>
                                <td class="text-muted">Computer Science Club</td>
                                <td>
                                    <span class="badge rounded-pill bg-success-subtle text-success">Published</span>
                                </td>
                                <td class="text-end fw-semibold">84</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Leadership Workshop</div>
                                    <div class="text-muted small">In 3 days</div>
                                </td>
                                <td class="text-muted">Student Council</td>
                                <td>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning">Pending</span>
                                </td>
                                <td class="text-end fw-semibold">32</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Charity Run 2025</div>
                                    <div class="text-muted small">Next week</div>
                                </td>
                                <td class="text-muted">Sports Club</td>
                                <td>
                                    <span class="badge rounded-pill bg-primary-subtle text-primary">Draft</span>
                                </td>
                                <td class="text-end fw-semibold">–</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-panel-card">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Quick Actions</h2>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('events.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create New Event
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-funnel me-2"></i>Review Pending Events
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-2"></i>View User Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-dashboard-title {
        font-size: 2rem;
        font-weight: 600;
        color: var(--text-primary);
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }

    .admin-dashboard-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
    }

    .admin-stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 1rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .admin-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
    }

    .admin-stat-content {
        flex: 1;
    }

    .admin-stat-label {
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        margin-bottom: 0.25rem;
    }

    .admin-stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .admin-stat-meta {
        font-size: 0.8125rem;
    }

    .admin-panel-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem 1.75rem;
        height: 100%;
    }

    .admin-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .admin-panel-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .admin-panel-link {
        font-size: 0.875rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    .admin-panel-link:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }

    .admin-panel-card table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-tertiary);
        border-bottom-color: var(--border-color);
    }

    .admin-panel-card table tbody td {
        font-size: 0.875rem;
        color: var(--text-secondary);
        border-bottom-color: var(--border-color);
        vertical-align: middle;
    }

    .badge.bg-success-subtle {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .badge.bg-warning-subtle {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .badge.bg-primary-subtle {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info);
    }
</style>
@endpush

@endsection





