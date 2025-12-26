<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('club.dashboard') }}">
            @php
            $user = auth()->user();
            $clubNav = null;
            if ($user && $user->role === 'club' && $user->club_id) {
            $clubNav = \App\Models\Club::where('club_user_id', $user->id)->first();
            }
            @endphp
            <span class="brand-text">
                <strong>{{ $clubNav->name ?? 'Club' }}</strong>
            </span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.dashboard') ? 'active' : '' }}" href="{{ route('club.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <!-- Members -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.members.*') ? 'active' : '' }}" href="{{ route('club.members.index') }}">
                        <i class="bi bi-people me-1"></i>Members
                    </a>
                </li>

                <!-- Announcements -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.announcements.*') ? 'active' : '' }}" href="{{ route('club.announcements.index') }}">
                        <i class="bi bi-megaphone me-1"></i>Announcements
                    </a>
                </li>

                <!-- Join Requests -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.join-requests.*') ? 'active' : '' }}" href="{{ route('club.join-requests.index') }}">
                        <i class="bi bi-person-plus me-1"></i>Join Requests
                    </a>
                </li>

                <!-- Events (Reserved) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.events.*') ? 'active' : '' }}"
                       href="{{ route('club.events.index') }}">
                        <i class="bi bi-calendar-event me-1"></i>Events
                    </a>
                </li>

                <!-- Forum (Reserved) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('club.forum.*') ? 'active' : '' }}" href="{{ route('club.forum.index') }}">
                        <i class="bi bi-chat-dots me-1"></i>Forum
                    </a>
                </li>

                <!-- User Profile Dropdown -->
                @auth
                @php
                $user = auth()->user();
                $clubNav = null;
                if ($user && $user->role === 'club' && $user->club_id) {
                $clubNav = \App\Models\Club::where('club_user_id', $user->id)->first();
                }
                // For club account, prefer club logo over user profile photo
                $avatarUrl = null;
                if ($clubNav && $clubNav->logo) {
                $avatarUrl = asset('storage/' . $clubNav->logo);
                } else {
                $avatarUrl = $user->profile_photo_url;
                }
                @endphp
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ $avatarUrl }}" 
                             alt="Profile" 
                             class="rounded-circle me-2" 
                             width="32" 
                             height="32"
                             onerror="this.onerror=null; this.src='{{ asset('images/avatar/default-student-avatar.png') }}';">
                        <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
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
                            <a class="dropdown-item" href="{{ route('club.profile.edit') }}">
                                <i class="bi bi-gear me-2"></i>Club Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('club.logs.index') }}">
                                <i class="bi bi-clock-history me-2"></i>Activity Logs
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

    .brand-text {
        font-size: 1.25rem;
        color: var(--text-primary);
    }

    .brand-text strong {
        color: var(--primary);
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

    /* Dark Mode */
    [data-theme="dark"] .navbar {
        background-color: var(--bg-primary) !important;
    }

    [data-theme="dark"] .navbar-light .navbar-toggler-icon {
        filter: invert(1);
    }
</style>


