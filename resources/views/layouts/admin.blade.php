<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - TAREvent</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    
    <!-- Custom Styles -->
    @stack('styles')
</head>
<body class="admin-site" data-theme="light">
    <!-- Admin Navigation -->
    @include('partials.admin-navbar')

    <div class="admin-container">
        <!-- Sidebar -->
        @include('partials.admin-sidebar')

        <!-- Main Content Area -->
        <main class="admin-main-content">
            <!-- Flash Messages -->
            @include('partials.flash-messages')

            @yield('content')
        </main>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
        <i class="bi bi-moon-stars" id="darkModeIcon"></i>
    </button>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Common JS -->
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    
    <!-- Custom Scripts -->
    @stack('scripts')

    <style>
        .admin-container {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .admin-main-content {
            flex: 1;
            padding: 2rem;
            background-color: var(--bg-secondary);
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>