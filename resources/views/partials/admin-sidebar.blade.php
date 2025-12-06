<aside class="admin-sidebar">
    <div class="sidebar-content">
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Event Management -->
                <li class="nav-item">
                    <div class="nav-section-title">Event Management</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.events.index') ? 'active' : '' }}" 
                       href="{{ route('admin.events.index') }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>All Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.events.pending') ? 'active' : '' }}" 
                       href="{{ route('admin.events.index', ['status' => 'pending']) }}">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Pending Approval</span>
                        <span class="badge bg-warning text-dark ms-auto">3</span>
                    </a>
                </li>

                <!-- User Management -->
                <li class="nav-item">
                    <div class="nav-section-title">User Management</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>All Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" 
                       href="{{ route('admin.admins.index') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Administrators</span>
                    </a>
                </li>

                <!-- Club Management -->
                <li class="nav-item">
                    <div class="nav-section-title">Club Management</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}" 
                       href="{{ route('admin.clubs.index') }}">
                        <i class="bi bi-building"></i>
                        <span>All Clubs</span>
                    </a>
                </li>

                <!-- Reports & Analytics -->
                <li class="nav-item">
                    <div class="nav-section-title">Reports & Analytics</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.events') ? 'active' : '' }}" 
                       href="{{ route('admin.reports.events') }}">
                        <i class="bi bi-graph-up"></i>
                        <span>Event Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.registrations') ? 'active' : '' }}" 
                       href="{{ route('admin.reports.registrations') }}">
                        <i class="bi bi-person-check"></i>
                        <span>Registration Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.payments') ? 'active' : '' }}" 
                       href="{{ route('admin.reports.payments') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Payment Reports</span>
                    </a>
                </li>

                <!-- System Settings -->
                <li class="nav-item">
                    <div class="nav-section-title">System</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>System Logs</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Quick Stats -->
        <div class="sidebar-stats mt-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">24</div>
                    <div class="stat-label">Active Events</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">1,432</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>

        <!-- Back to User Site -->
        <div class="sidebar-footer">
            <a href="{{ route('home') }}" class="btn btn-outline-light w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-2"></i>
                View User Site
            </a>
        </div>
    </div>
</aside>

<style>
.admin-sidebar {
    width: 280px;
    min-height: calc(100vh - 70px);
    background-color: var(--bg-primary);
    border-right: 1px solid var(--border-color);
    position: sticky;
    top: 70px;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.sidebar-content {
    padding: 1.5rem 1rem;
}

.sidebar-nav .nav-link {
    color: var(--text-secondary);
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.sidebar-nav .nav-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

.sidebar-nav .nav-link:hover {
    background-color: var(--primary-light);
    color: var(--primary);
}

.sidebar-nav .nav-link.active {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
    color: white;
    font-weight: 600;
    box-shadow: var(--shadow-md);
}

.nav-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-tertiary);
    padding: 1rem 1rem 0.5rem;
    margin-top: 1rem;
    letter-spacing: 0.5px;
}

.nav-section-title:first-child {
    margin-top: 0;
}

.sidebar-stats {
    padding: 1rem 0;
    border-top: 1px solid var(--border-color);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background-color: var(--bg-secondary);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-sm);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    margin-top: 0.25rem;
}

.sidebar-footer {
    padding: 1rem 0;
    border-top: 1px solid var(--border-color);
    margin-top: 1rem;
}

.sidebar-footer .btn {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}

.sidebar-footer .btn:hover {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
    color: white;
    border-color: var(--admin-primary);
}

/* Scrollbar Styling */
.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--admin-primary);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        width: 100%;
        position: relative;
        top: 0;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
    }
}

/* Dark Mode */
[data-theme="dark"] .admin-sidebar {
    background-color: var(--bg-secondary);
}
</style>