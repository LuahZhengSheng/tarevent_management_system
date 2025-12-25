<nav class="navbar navbar-expand-lg navbar-light bg-gradient-admin shadow-sm sticky-top">
    <div class="container-fluid">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
            <div class="admin-brand-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <span class="brand-text ms-2">
                <strong>TAR</strong>Event <span class="badge bg-light text-dark ms-1">Admin</span>
            </span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <!-- Events Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" 
                       href="{{ route('admin.events.index') }}">
                        <i class="bi bi-calendar-check me-1"></i>Events
                    </a>
                </li>

                <!-- Users Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                       href="{{ route('home') }}">
                        <i class="bi bi-people me-1"></i>Users
                    </a>
                </li>

                <!-- Clubs Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}" 
                       href="{{ route('admin.clubs.index') }}">
                        <i class="bi bi-building me-1"></i>Clubs
                    </a>
                </li>

                <!-- Reports -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-graph-up me-1"></i>Reports
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bi bi-calendar-event me-2"></i>Events Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bi bi-person-check me-2"></i>Registrations
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bi bi-cash-coin me-2"></i>Payments
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Quick Actions -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="actionsDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-lightning-charge me-1"></i>Quick Actions
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bi bi-person-plus me-2"></i>Add Admin
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.clubs.create') }}">
                                <i class="bi bi-building-add me-2"></i>Create Club
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-2"></i>View User Site
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="adminNotificationDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        {{-- 
                        @if(auth()->user()->unread_notifications_count > 0)
                        <span class="notification-badge-admin">{{ auth()->user()->unread_notifications_count }}</span>
                        @endif
                        --}}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown-admin">
                        <li class="dropdown-header">
                            <strong>Admin Notifications</strong>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center small" href="#">
                                View all notifications
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Admin Profile -->
                {{--
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                       id="adminUserDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->profile_photo_url }}" 
                             alt="Admin" 
                             class="rounded-circle me-2 admin-avatar" 
                             width="32" 
                             height="32">
                        <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <small class="text-muted">Administrator</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li> --}}
            </ul>
        </div>
    </div>
</nav>

<style>
.bg-gradient-admin {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
}

.admin-brand-logo {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    backdrop-filter: blur(10px);
}

.navbar-dark .nav-link {
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.navbar-dark .nav-link:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.1);
}

.navbar-dark .nav-link.active {
    color: white;
    background-color: rgba(255, 255, 255, 0.15);
    font-weight: 600;
}

.admin-avatar {
    border: 2px solid white;
}

.notification-badge-admin {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--admin-secondary);
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.125rem 0.375rem;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

.notification-dropdown-admin {
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
}

.dropdown-menu {
    border: none;
    box-shadow: var(--shadow-lg);
    border-radius: 0.75rem;
    margin-top: 0.5rem;
}

/* Mobile Responsive */
@media (max-width: 991px) {
    .navbar-nav {
        padding: 1rem 0;
    }

    .nav-item {
        margin: 0.25rem 0;
    }
}
</style>