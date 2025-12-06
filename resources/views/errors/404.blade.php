<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found | TAREvent</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
        <style>
            .error-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 2rem;
                position: relative;
                overflow: hidden;
            }

            .floating-elements {
                position: absolute;
                width: 100%;
                height: 100%;
                pointer-events: none;
            }

            .floating-icon {
                position: absolute;
                font-size: 3rem;
                color: rgba(255, 255, 255, 0.1);
                animation: float 6s ease-in-out infinite;
            }

            .floating-icon:nth-child(1) {
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }

            .floating-icon:nth-child(2) {
                top: 20%;
                right: 15%;
                animation-delay: 1s;
            }

            .floating-icon:nth-child(3) {
                bottom: 15%;
                left: 20%;
                animation-delay: 2s;
            }

            .floating-icon:nth-child(4) {
                bottom: 20%;
                right: 10%;
                animation-delay: 3s;
            }

            @keyframes float {
                0%, 100% {
                    transform: translateY(0) rotate(0deg);
                }
                50% {
                    transform: translateY(-20px) rotate(10deg);
                }
            }

            .error-container {
                background: var(--bg-primary);
                border-radius: 2rem;
                padding: 3rem;
                max-width: 700px;
                width: 100%;
                box-shadow: var(--shadow-xl);
                text-align: center;
                position: relative;
                z-index: 1;
            }

            .error-illustration {
                position: relative;
                margin-bottom: 2rem;
            }

            .error-code {
                font-size: 10rem;
                font-weight: 700;
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1;
                margin-bottom: 1rem;
                animation: pulse 2s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }

            .error-icon {
                position: absolute;
                font-size: 4rem;
                animation: bounce 1s ease-in-out infinite;
            }

            .error-icon-left {
                left: 20%;
                top: 50%;
                transform: translateY(-50%);
                color: var(--primary);
            }

            .error-icon-right {
                right: 20%;
                top: 50%;
                transform: translateY(-50%);
                color: var(--secondary);
            }

            @keyframes bounce {
                0%, 100% {
                    transform: translateY(-50%) scale(1);
                }
                50% {
                    transform: translateY(-60%) scale(1.1);
                }
            }

            .error-title {
                font-size: 2.5rem;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }

            .error-description {
                font-size: 1.2rem;
                color: var(--text-secondary);
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .search-box {
                max-width: 500px;
                margin: 0 auto 2rem;
            }

            .search-input-group {
                position: relative;
            }

            .search-input-group input {
                padding: 1rem 1.5rem;
                padding-right: 3rem;
                border-radius: 1rem;
                border: 2px solid var(--border-color);
                width: 100%;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .search-input-group input:focus {
                outline: none;
                border-color: var(--primary);
                box-shadow: 0 0 0 4px var(--primary-light);
            }

            .search-icon {
                position: absolute;
                right: 1.5rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-tertiary);
                font-size: 1.2rem;
            }

            .suggestions {
                background: var(--bg-secondary);
                border-radius: 1rem;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }

            .suggestions h6 {
                color: var(--text-primary);
                margin-bottom: 1rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .suggestion-links {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .suggestion-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem;
                background: var(--bg-primary);
                border-radius: 0.75rem;
                text-decoration: none;
                color: var(--text-primary);
                transition: all 0.3s ease;
                border: 1px solid var(--border-color);
            }

            .suggestion-link:hover {
                background: var(--primary);
                color: white;
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }

            .suggestion-link i {
                font-size: 1.5rem;
            }

            .btn-group-custom {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn-custom {
                padding: 0.75rem 2rem;
                border-radius: 0.75rem;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary), var(--primary-hover));
                color: white;
                border: none;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }

            .btn-outline-custom {
                border: 2px solid var(--border-color);
                color: var(--text-primary);
            }

            .btn-outline-custom:hover {
                background: var(--primary);
                border-color: var(--primary);
                color: white;
                transform: translateY(-2px);
            }

            @media (max-width: 768px) {
                .error-code {
                    font-size: 6rem;
                }

                .error-title {
                    font-size: 1.75rem;
                }

                .error-icon {
                    display: none;
                }

                .suggestion-links {
                    grid-template-columns: 1fr;
                }

                .btn-group-custom {
                    flex-direction: column;
                }

                .btn-custom {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-page">
            <!-- Floating Background Elements -->
            <div class="floating-elements">
                <i class="bi bi-calendar-x floating-icon"></i>
                <i class="bi bi-question-circle floating-icon"></i>
                <i class="bi bi-exclamation-triangle floating-icon"></i>
                <i class="bi bi-search floating-icon"></i>
            </div>

            <div class="error-container">
                <div class="error-illustration">
                    <i class="bi bi-emoji-frown error-icon error-icon-left"></i>
                    <div class="error-code">404</div>
                    <i class="bi bi-compass error-icon error-icon-right"></i>
                </div>

                <h1 class="error-title">Oops! Page Not Found</h1>
                <p class="error-description">
                    The page you're looking for seems to have wandered off. 
                    It might have been moved, deleted, or perhaps it never existed.
                </p>

                <div class="search-box">
                    <form action="{{ route('events.index') }}" method="GET">
                        <div class="search-input-group">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Try searching for events..." 
                                   class="search-input">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                    </form>
                </div>

                <div class="suggestions">
                    <h6>
                        <i class="bi bi-lightbulb"></i>
                        Here's what you might be looking for:
                    </h6>
                    <div class="suggestion-links">
                        <a href="{{ route('events.index') }}" class="suggestion-link">
                            <i class="bi bi-calendar-event"></i>
                            <span>Browse Events</span>
                        </a>
                        <a href="{{ url('/') }}" class="suggestion-link">
                            <i class="bi bi-house"></i>
                            <span>Homepage</span>
                        </a>
                        <a href="{{ route('clubs.index') }}" class="suggestion-link">
                            <i class="bi bi-people"></i>
                            <span>View Clubs</span>
                        </a>
                        <a href="{{ route('forum.index') }}" class="suggestion-link">
                            <i class="bi bi-chat-dots"></i>
                            <span>Forum</span>
                        </a>
                    </div>
                </div>

                <div class="btn-group-custom">
                    <a href="{{ url('/') }}" class="btn-custom btn-primary-custom">
                        <i class="bi bi-house"></i>
                        Take Me Home
                    </a>
                    <button onclick="window.history.back()" class="btn-custom btn-outline-custom">
                        <i class="bi bi-arrow-left"></i>
                        Go Back
                    </button>
                </div>
            </div>
        </div>

        <script>
            $(function () {
                // Auto dark mode detection
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    $('html').attr('data-theme', 'dark');
                }

                // Auto-focus search input
                const $searchInput = $('.search-input');
                if ($searchInput.length) {
                    setTimeout(() => {
                        $searchInput.focus();
                    }, 500);
                }
            });
        </script>
    </body>
</html>