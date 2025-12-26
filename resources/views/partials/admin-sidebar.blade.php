<aside class="admin-sidebar">
    <div class="sidebar-content">
        <!-- User Info Section -->
        @auth
        <div class="sidebar-user-info">
            <a href="{{ route('admin.profile.edit') }}" class="user-info-link">
                <div class="user-avatar-container">
                    <img src="{{ auth()->user()->profile_photo_url }}" 
                         alt="{{ auth()->user()->name }}" 
                         class="user-avatar"
                         onerror="this.onerror=null; this.src='{{ asset('images/avatar/default-student-avatar.png') }}';">
                    <div class="online-indicator"></div>
                </div>
                <div class="user-info-content">
                    <h6 class="user-name">{{ auth()->user()->name }}</h6>
                    @if(auth()->user()->isSuperAdmin())
                    <span class="user-badge badge-super">Super Admin</span>
                    @else
                    <span class="user-badge badge-admin">Admin</span>
                    @endif
                </div>
                <i class="bi bi-chevron-right user-arrow"></i>
            </a>
        </div>
        @endauth

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
                <!-- <li class="nav-item">
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
                </li> -->

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
                    <a class="nav-link {{ request()->routeIs('admin.administrators.*') ? 'active' : '' }}" 
                       href="{{ route('admin.administrators.index') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Administrators</span>
                    </a>
                </li>

                <!-- Permission Management (Super Admin Only) -->
                @if(auth()->check() && auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <div class="nav-section-title">Permission Management</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" 
                       href="{{ route('admin.permissions.index') }}">
                        <i class="bi bi-key"></i>
                        <span>Manage Permissions</span>
                    </a>
                </li>
                @endif

                <!-- Forum Management -->
                <li class="nav-item">
                    <div class="nav-section-title">Forum Management</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.forums.posts.*') ? 'active' : '' }}"
                       href="{{ route('admin.forums.posts.index') }}">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Forum Posts</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.forums.tags.*') ? 'active' : '' }}"
                       href="{{ route('admin.forums.tags.index') }}">
                        <i class="bi bi-tags"></i>
                        <span>Forum Tags</span>
                    </a>
                </li>

                <!-- Club Management -->
                <li class="nav-item">
                    <div class="nav-section-title">Club Management</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs.index') ? 'active' : '' }}" 
                       href="{{ route('admin.clubs.index') }}">
                        <i class="bi bi-building"></i>
                        <span>All Clubs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs.create') ? 'active' : '' }}" 
                       href="{{ route('admin.clubs.create') }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Club</span>
                    </a>
                </li>

                <!-- Reports & Analytics -->
                <li class="nav-item">
                    <div class="nav-section-title">Reports & Analytics</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.events') ? 'active' : '' }}" 
                       href="{{ route('home') }}">
                        <i class="bi bi-graph-up"></i>
                        <span>Event Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.registrations') ? 'active' : '' }}" 
                       href="{{ route('home') }}">
                        <i class="bi bi-person-check"></i>
                        <span>Registration Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.payments') ? 'active' : '' }}" 
                       href="{{ route('home') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Payment Reports</span>
                    </a>
                </li>

                <!-- Admin Profile -->
                <li class="nav-item">
                    <div class="nav-section-title">Account</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" 
                       href="{{ route('admin.profile.edit') }}">
                        <i class="bi bi-person"></i>
                        <span>My Profile</span>
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
            <a href="{{ route('home') }}" class="btn btn-outline-light w-100 mb-2" target="_blank">
                <i class="bi bi-box-arrow-up-right me-2"></i>
                View User Site
            </a>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="btn btn-logout w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>

<style>
.admin-sidebar {
    width: 320px;
    min-width: 320px;
    max-width: 320px;
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

/* ========================================
   User Info Section - 简约高级感设计
   ======================================== */
.sidebar-user-info {
    position: relative;
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--bg-primary);
    border-radius: 1rem;
    border: 1px solid transparent;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-user-info::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 1rem;
    padding: 1px;
    background: linear-gradient(135deg, var(--border-color), transparent);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 1;
    transition: opacity 0.3s ease;
}

.sidebar-user-info:hover {
    border-color: rgba(79, 70, 229, 0.1);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
}

.sidebar-user-info:hover::before {
    opacity: 0;
}

.user-info-link {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    text-decoration: none;
    color: inherit;
    position: relative;
}

/* Avatar Container with Online Status */
.user-avatar-container {
    position: relative;
    flex-shrink: 0;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid var(--bg-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.user-info-link:hover .user-avatar {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(79, 70, 229, 0.15);
    border-color: rgba(79, 70, 229, 0.2);
}

/* Online Indicator */
.online-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: 2px solid var(--bg-primary);
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    animation: pulse-online 2s ease-in-out infinite;
}

@keyframes pulse-online {
    0%, 100% {
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    50% {
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }
}

/* User Info Content */
.user-info-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.user-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
    transition: color 0.2s ease;
}

.user-info-link:hover .user-name {
    color: var(--primary);
}

/* Badge Styles */
.user-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.5rem;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    border-radius: 6px;
    width: fit-content;
    transition: all 0.2s ease;
}

.badge-super {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.badge-admin {
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
    color: var(--primary);
    border: 1px solid rgba(79, 70, 229, 0.2);
}

.user-info-link:hover .badge-super {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border-color: #dc3545;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
}

.user-info-link:hover .badge-admin {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
}

/* Arrow Icon */
.user-arrow {
    font-size: 0.875rem;
    color: var(--text-tertiary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
}

.user-info-link:hover .user-arrow {
    opacity: 1;
    transform: translateX(2px);
    color: var(--primary);
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

/* Logout Form Styling */
.logout-form {
    margin: 0;
    padding: 0;
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

/* Logout Button Styling */
.btn-logout {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05)) !important;
    border: 1px solid rgba(220, 53, 69, 0.3) !important;
    color: #dc3545 !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: linear-gradient(135deg, #dc3545, #c82333) !important;
    color: white !important;
    border-color: #dc3545 !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important;
    transform: translateY(-1px) !important;
}

.btn-logout:active {
    transform: translateY(0) !important;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2) !important;
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

    /* ========================================
       User Info Section - 简约高级感设计
       ======================================== */
    .sidebar-user-info {
        position: relative;
        margin-bottom: 2rem;
        padding: 1rem;
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid transparent;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-user-info::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1rem;
        padding: 1px;
        background: linear-gradient(135deg, var(--border-color), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .sidebar-user-info:hover {
        border-color: rgba(79, 70, 229, 0.1);
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
    }

    .sidebar-user-info:hover::before {
        opacity: 0;
    }

    .user-info-link {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        text-decoration: none;
        color: inherit;
        position: relative;
    }

    /* Avatar Container with Online Status */
    .user-avatar-container {
        position: relative;
        flex-shrink: 0;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid var(--bg-secondary);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .user-info-link:hover .user-avatar {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.15);
        border-color: rgba(79, 70, 229, 0.2);
    }

    /* Online Indicator */
    .online-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        background: linear-gradient(135deg, #10b981, #059669);
        border: 2px solid var(--bg-primary);
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        animation: pulse-online 2s ease-in-out infinite;
    }

    @keyframes pulse-online {
        0%, 100% {
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
    }

    /* User Info Content */
    .user-info-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .user-name {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
        transition: color 0.2s ease;
    }

    .user-info-link:hover .user-name {
        color: var(--primary);
    }

    /* Badge Styles */
    .user-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.125rem 0.5rem;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-radius: 6px;
        width: fit-content;
        transition: all 0.2s ease;
    }

    .badge-super {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .badge-admin {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
        color: var(--primary);
        border: 1px solid rgba(79, 70, 229, 0.2);
    }

    .user-info-link:hover .badge-super {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        border-color: #dc3545;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
    }

    .user-info-link:hover .badge-admin {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border-color: var(--primary);
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }

    /* Arrow Icon */
    .user-arrow {
        font-size: 0.875rem;
        color: var(--text-tertiary);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0;
    }

    .user-info-link:hover .user-arrow {
        opacity: 1;
        transform: translateX(2px);
        color: var(--primary);
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
