<!-- Author: Tang Lit Xuan -->
@extends('layouts.app')

@section('title', 'Login - TAREvent')

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
        max-width: 420px;
    }

    .auth-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        padding: 3rem 2.5rem;
        transition: box-shadow 0.2s ease;
    }

    .auth-card:hover {
        box-shadow: var(--shadow-md);
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .auth-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        letter-spacing: -0.01em;
    }

    .auth-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .auth-form-group {
        margin-bottom: 1.5rem;
    }

    .auth-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .auth-input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .auth-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .auth-input::placeholder {
        color: var(--text-tertiary);
    }

    .auth-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        font-size: 0.875rem;
    }

    .auth-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .auth-checkbox input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .auth-checkbox label {
        color: var(--text-secondary);
        cursor: pointer;
        user-select: none;
    }

    .auth-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .auth-link:hover {
        color: var(--primary-hover);
    }

    .auth-button {
        width: 100%;
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .auth-button:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .auth-button:active {
        transform: translateY(0);
    }

    .auth-divider {
        display: flex;
        align-items: center;
        margin: 2rem 0;
        color: var(--text-tertiary);
        font-size: 0.875rem;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .auth-divider span {
        padding: 0 1rem;
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .auth-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        margin-left: 0.25rem;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert-danger {
        background: var(--error-light);
        color: var(--error);
    }

    .alert-success {
        background: var(--success-light);
        color: var(--success);
    }

    .alert-icon {
        font-size: 1.125rem;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }

    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        padding: 0.75rem 1rem;
        background: var(--error-light);
        color: var(--error);
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .invalid-feedback ul {
        margin: 0.25rem 0 0 0;
        padding-left: 1.25rem;
    }

    .invalid-feedback li {
        margin-bottom: 0.25rem;
    }

    .invalid-feedback li:last-child {
        margin-bottom: 0;
    }

    .is-invalid {
        border-color: var(--error);
    }

    .is-invalid:focus {
        border-color: var(--error);
        box-shadow: 0 0 0 3px var(--error-light);
    }
</style>
@endpush

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in to your TAREvent account</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle alert-icon"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="auth-input @error('email') is-invalid @enderror"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label for="password" class="auth-label">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="auth-input @error('password') is-invalid @enderror"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-options">
                    <div class="auth-checkbox">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                        >
                        <label for="remember">Remember me</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-button">
                    Sign in
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account?
                <a href="{{ route('register') }}">Sign up</a>
            </div>
        </div>
    </div>
</div>
@endsection
