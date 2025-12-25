<!-- Author: Tang Lit Xuan -->
@extends('layouts.app')

@section('title', 'Verify Email - TAREvent')

@push('styles')
    <style>
        .auth-page {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 1.5rem;
            background: var(--bg-secondary);
        }

        .auth-container {
            width: 100%;
            max-width: 520px;
        }

        .auth-card {
            background: var(--bg-primary);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            padding: 3rem 2.5rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .auth-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .auth-icon-wrapper {
            margin: 0 auto 2rem;
            position: relative;
            width: fit-content;
        }

        .auth-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary-light), var(--bg-secondary));
            border: 3px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            position: relative;
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 0 0 0 var(--primary-light);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 var(--primary-light);
            }
            50% {
                box-shadow: 0 0 0 15px transparent;
            }
            100% {
                box-shadow: 0 0 0 0 transparent;
            }
        }

        .auth-icon::after {
            content: '';
            position: absolute;
            inset: -12px;
            border: 2px dashed var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .auth-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .auth-subtitle {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 2rem;
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }

        .auth-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--border-color), transparent);
            margin: 2rem 0;
        }

        .auth-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .auth-actions form {
            width: 100%;
        }

        .auth-button {
            width: 100%;
            padding: 1rem 2rem;
            font-size: 0.9375rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .auth-button:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        .auth-button-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 2px solid var(--border-color);
            box-shadow: none;
        }

        .auth-button-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--border-hover);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }

        .alert {
            padding: 1.125rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .alert-icon {
            font-size: 1.25rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .alert-content {
            line-height: 1.6;
            flex: 1;
        }

        /* Info Section */
        .info-section {
            background: var(--bg-secondary);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 2rem 0;
            border: 1px solid var(--border-color);
        }

        .info-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-title i {
            color: var(--info);
        }

        .info-text {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            line-height: 1.6;
            text-align: left;
        }

        @media (max-width: 640px) {
            .auth-card {
                padding: 2.5rem 1.5rem;
            }

            .auth-icon {
                width: 70px;
                height: 70px;
                font-size: 1.75rem;
            }

            .auth-title {
                font-size: 1.5rem;
            }

            .auth-subtitle {
                font-size: 0.875rem;
            }

            .auth-button {
                padding: 0.875rem 1.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-icon-wrapper">
                    <div class="auth-icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                </div>

                <h1 class="auth-title">Verify Your Email</h1>
                <p class="auth-subtitle">
                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill alert-icon"></i>
                        <div class="alert-content">
                            A new verification link has been sent to the email address you provided during registration.
                        </div>
                    </div>
                @endif

                <div class="info-section">
                    <div class="info-title">
                        <i class="bi bi-lightbulb"></i>
                        What to do next?
                    </div>
                    <div class="info-text">
                        Check your email inbox (and spam folder) for a verification email from TAREvent. Click the verification link in that email to activate your account.
                    </div>
                </div>

                <div class="auth-divider"></div>

                <div class="auth-actions">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="auth-button">
                            <i class="bi bi-arrow-clockwise"></i>
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="auth-button auth-button-secondary">
                            <i class="bi bi-box-arrow-right"></i>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
