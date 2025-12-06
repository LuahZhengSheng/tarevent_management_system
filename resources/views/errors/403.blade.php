<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 - Access Forbidden | TAREvent</title>
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
            }

            .error-container {
                background: var(--bg-primary);
                border-radius: 2rem;
                padding: 3rem;
                max-width: 600px;
                width: 100%;
                box-shadow: var(--shadow-xl);
                text-align: center;
            }

            .error-icon {
                font-size: 8rem;
                color: #dc3545;
                margin-bottom: 2rem;
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% {
                    transform: translateX(0);
                }
                25% {
                    transform: translateX(-10px);
                }
                75% {
                    transform: translateX(10px);
                }
            }

            .error-code {
                font-size: 6rem;
                font-weight: 700;
                color: var(--primary);
                line-height: 1;
                margin-bottom: 1rem;
            }

            .error-title {
                font-size: 2rem;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }

            .error-description {
                font-size: 1.1rem;
                color: var(--text-secondary);
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .error-reasons {
                background: var(--bg-secondary);
                border-radius: 1rem;
                padding: 1.5rem;
                margin-bottom: 2rem;
                text-align: left;
            }

            .error-reasons h6 {
                color: var(--text-primary);
                margin-bottom: 1rem;
                font-weight: 600;
            }

            .error-reasons ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .error-reasons li {
                padding: 0.5rem 0;
                color: var(--text-secondary);
                display: flex;
                align-items: start;
            }

            .error-reasons li i {
                color: var(--primary);
                margin-right: 0.75rem;
                margin-top: 0.25rem;
                font-size: 1.1rem;
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

            .support-link {
                margin-top: 2rem;
                font-size: 0.9rem;
                color: var(--text-tertiary);
            }

            .support-link a {
                color: var(--primary);
                text-decoration: none;
                font-weight: 600;
            }

            .support-link a:hover {
                text-decoration: underline;
            }

            @media (max-width: 576px) {
                .error-code {
                    font-size: 4rem;
                }

                .error-title {
                    font-size: 1.5rem;
                }

                .error-icon {
                    font-size: 5rem;
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
            <div class="error-container">
                <div class="error-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <div class="error-code">403</div>
                <h1 class="error-title">Access Forbidden</h1>
                <p class="error-description">
                    Sorry, you don't have permission to access this resource. 
                    This area is restricted to authorized users only.
                </p>

                <div class="error-reasons">
                    <h6><i class="bi bi-info-circle me-2"></i>Why am I seeing this?</h6>
                    <ul>
                        <li>
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>You don't have the required role or permissions for this action</span>
                        </li>
                        <li>
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>This feature is only available to club administrators</span>
                        </li>
                        <li>
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>You're trying to access content that doesn't belong to you</span>
                        </li>
                        <li>
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>Your account may need additional verification</span>
                        </li>
                    </ul>
                </div>

                <div class="btn-group-custom">
                    <a href="{{ url('/') }}" class="btn-custom btn-primary-custom">
                        <i class="bi bi-house"></i>
                        Go to Homepage
                    </a>
                    <button onclick="window.history.back()" class="btn-custom btn-outline-custom">
                        <i class="bi bi-arrow-left"></i>
                        Go Back
                    </button>
                </div>

                <div class="support-link">
                    Need help? <a href="mailto:support@tarc.edu.my">Contact Support</a>
                </div>
            </div>
        </div>

        <script>
            $(function () {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    $('html').attr('data-theme', 'dark');
                }
            });
        </script>
    </body>
</html>