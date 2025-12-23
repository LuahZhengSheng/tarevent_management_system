<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <div class="brand-logo">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <span class="brand-text ms-2">
                <strong>TAR</strong>Event
            </span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>

                <!-- Events -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                        <i class="bi bi-calendar-event me-1"></i>Events
                    </a>
                </li>

                <!-- Clubs -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clubs.*') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-people me-1"></i>Clubs
                    </a>
                </li>

                <!-- Forum -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('forums.*') ? 'active' : '' }}" 
                       href="{{ route('forums.index') }}">
                        <i class="bi bi-chat-dots me-1"></i>Forum
                    </a>
                </li>

                @auth
                <!-- My Events (for authenticated users) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('events.my') ? 'active' : '' }}" href="{{ route('events.my') }}">
                        <i class="bi bi-bookmark me-1"></i>My Events
                    </a>
                </li>
                
                <!-- My Posts (for authenticated users) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('forums.my-posts') ? 'active' : '' }}" href="{{ route('forums.my-posts') }}">
                        <i class="bi bi-bookmark me-1"></i>My Posts
                    </a>
                </li>

                <!-- Create Event (for club admins only) -->
                @if(auth()->user()->hasRole('club'))
                <li class="nav-item">
                    <a class="nav-link text-primary fw-semibold" href="{{ route('events.create') }}">
                        <i class="bi bi-plus-circle me-1"></i>Create Event
                    </a>
                </li>
                @endif

                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="notification-badge">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                        <li class="dropdown-header">
                            <strong>Notifications</strong>
                            @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                            <span class="badge bg-primary ms-2">{{ $unreadNotificationsCount }} new</span>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>

                        @if(auth()->user()->recent_unread_notifications->isEmpty())
                        <li>
                            <div class="dropdown-item text-center text-muted py-3">
                                <i class="bi bi-bell-slash"></i><br>
                                No new notifications
                            </div>
                        </li>
                        @else
                        @foreach(auth()->user()->recent_unread_notifications as $notification)
                        <li>
                            <a class="dropdown-item notification-item-dropdown" 
                               href="{{ route('notifications.show', $notification) }}">
                                <div class="notification-icon-small {{ $notification->type }}">
                                    <i class="bi {{ $notification->icon }}"></i>
                                </div>
                                <div class="notification-content-small">
                                    <div class="notification-title-small">{{ $notification->title }}</div>
                                    <div class="notification-message-small">{{ Str::limit($notification->message, 60) }}</div>
                                    <div class="notification-time-small">{{ $notification->time_ago }}</div>
                                </div>
                            </a>
                        </li>
                        @endforeach
                        @endif

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                                <strong>View all notifications</strong>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(auth()->check())
                        <img src="{{ auth()->user()->profile_photo_url }}" 
                             alt="Profile" 
                             class="rounded-circle me-2" 
                             width="32" 
                             height="32"
                             onerror="this.onerror=null; this.src='{{ asset('images/avatar/default-student-avatar.png') }}';">
                        <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                        </li>
                        @if(auth()->user()->hasRole('admin'))
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                            </a>
                        </li>
                        @endif
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
                </li>
                @else
                <!-- Guest Links -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-lg-2" href="{{ route('register') }}">
                        <i class="bi bi-person-plus me-1"></i>Register
                    </a>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 0.75rem 0;
        transition: all 0.3s ease;
    }

    .brand-logo {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .brand-text {
        font-size: 1.25rem;
        color: var(--text-primary);
    }

    .brand-text strong {
        color: var(--primary);
    }

    .navbar-brand:hover .brand-logo {
        transform: rotate(15deg);
        transition: transform 0.3s ease;
    }

    .nav-link {
        color: var(--text-secondary);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link:hover {
        color: var(--primary);
        background-color: var(--primary-light);
    }

    .nav-link.active {
        color: var(--primary);
        font-weight: 600;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 1rem;
        right: 1rem;
        height: 2px;
        background-color: var(--primary);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: var(--error);
        color: white;
        font-size: 0.625rem;
        font-weight: 700;
        padding: 0.125rem 0.375rem;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }

    .notification-dropdown {
        width: 380px;
        max-height: 500px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .notification-item-dropdown {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
        overflow: hidden;
    }

    .notification-item-dropdown:hover {
        background: var(--primary-light);
    }

    .notification-icon-small {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .notification-icon-small.event_updated {
        background: var(--info-light);
        color: var(--info);
    }

    .notification-icon-small.event_cancelled {
        background: var(--error-light);
        color: var(--error);
    }

    .notification-icon-small.event_time_changed,
    .notification-icon-small.event_venue_changed {
        background: var(--warning-light);
        color: var(--warning);
    }

    .notification-icon-small.registration_confirmed,
    .notification-icon-small.payment_confirmed {
        background: var(--success-light);
        color: var(--success);
    }

    .notification-content-small {
        flex: 1;
        min-width: 0;
    }

    .notification-title-small {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .notification-message-small {
        font-size: 0.8rem;
        color: var(--text-secondary);
        line-height: 1.3;
        margin-bottom: 0.25rem;
    }

    .notification-message-small,
    .notification-title-small {
        display: -webkit-box;              /* 必须 */
        -webkit-box-orient: vertical;      /* 必须 */
        -webkit-line-clamp: 2;             /* 限制为 2 行 */
        overflow: hidden;                  /* 必须 */
        text-overflow: ellipsis;           /* 一起用 */
        max-height: calc(1.3em * 2);       /* 可选：和 line-height 对齐，帮助布局 */
        word-break: break-word;            /* 防长单词/URL 撑开 */
    }

    .notification-time-small {
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    .dropdown-menu {
        border: none;
        box-shadow: var(--shadow-lg);
        border-radius: 0.75rem;
        margin-top: 0.5rem;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: var(--primary-light);
        color: var(--primary);
    }

    .dropdown-header {
        padding: 0.75rem 1rem;
        background-color: var(--bg-secondary);
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .nav-link.active::after {
            display: none;
        }

        .navbar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 0;
        }
    }

    @media (max-width: 576px) {
        .notification-dropdown {
            width: 320px;
        }
    }

    /* Dark Mode */
    [data-theme="dark"] .navbar {
        background-color: var(--bg-primary) !important;
    }

    [data-theme="dark"] .navbar-light .navbar-toggler-icon {
        filter: invert(1);
    }
</style>
