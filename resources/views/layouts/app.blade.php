<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

        <!-- Payment Gateway Keys -->
        <meta name="stripe-key" content="{{ config('services.stripe.key') }}">

        <title>@yield('title', 'TAREvent Management System')</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <!-- Custom Theme CSS -->
        <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

        <!-- Custom Styles -->
        @stack('styles')
        
        @stack('head')
    </head>
    <body class="user-site" data-theme="light">
        <!-- Navigation -->
        @include('partials.navbar')

        <!-- Flash Messages -->
        @include('partials.flash-messages')

        <!-- Login Required Modal (Global) -->
        @include('partials.login-required-modal')

        <!-- Main Content -->
        <main id="main-content">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('partials.footer')

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

        <!-- API Token Management -->
        @if(session('api_token'))
        <script>
            // Store token in localStorage when login is successful
            (function() {
                const token = @json(session('api_token'));
                if (token) {
                    localStorage.setItem('api_token', token);
                    // Clear from session after storing
                    fetch('{{ route("auth.clear-token") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    }).catch(err => console.error('Failed to clear token from session:', err));
                }
            })();
        </script>
        @endif

        @if(session('clear_token'))
        <script>
            // Clear token from localStorage when logout
            localStorage.removeItem('api_token');
        </script>
        @endif

        <!-- Custom Scripts -->
        @stack('scripts')
    </body>
</html>
