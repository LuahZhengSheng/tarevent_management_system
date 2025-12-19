<!-- Author: Tang Lit Xuan -->
@extends('layouts.app')

@section('title', 'Reset Password - TAREvent')

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

    .password-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input-wrapper .auth-input {
        padding-right: 2.75rem;
    }

    .password-hint-trigger {
        position: absolute;
        right: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: help;
        color: var(--text-tertiary);
        transition: color 0.2s ease;
        z-index: 1;
    }

    .password-hint-trigger:hover {
        color: var(--info);
    }

    .password-hint-trigger i {
        font-size: 1.125rem;
    }

    .password-hint-tooltip {
        position: absolute;
        bottom: calc(100% + 0.75rem);
        right: 0;
        width: 280px;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-lg);
        padding: 1rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(0.5rem);
        transition: all 0.2s ease;
        pointer-events: none;
        z-index: 1000;
    }

    .password-hint-trigger:hover .password-hint-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }

    .tooltip-content {
        font-size: 0.8125rem;
        color: var(--text-primary);
        line-height: 1.5;
    }

    .tooltip-content strong {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        font-weight: 600;
    }

    .tooltip-content ul {
        margin: 0;
        padding-left: 1.25rem;
        list-style: none;
    }

    .tooltip-content ul li {
        position: relative;
        padding-left: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .tooltip-content ul li:before {
        content: '•';
        position: absolute;
        left: -0.75rem;
        color: var(--info);
        font-weight: bold;
    }

    .tooltip-content ul li:last-child {
        margin-bottom: 0;
    }

    /* Tooltip arrow */
    .password-hint-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        right: 1.5rem;
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid var(--bg-primary);
    }

    .password-hint-tooltip::before {
        content: '';
        position: absolute;
        top: 100%;
        right: 1.5rem;
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 7px solid var(--border-color);
        margin-top: 1px;
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
                <h1 class="auth-title">Reset password</h1>
                <p class="auth-subtitle">Enter your new password below</p>
            </div>


            <form method="POST" action="{{ route('password.store') }}" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
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
                    <label for="password" class="auth-label">New Password</label>
                    <div class="password-input-wrapper">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="auth-input @error('password') is-invalid @enderror"
                        >
                        <div class="password-hint-trigger">
                            <i class="bi bi-question-circle"></i>
                            <div class="password-hint-tooltip">
                                <div class="tooltip-content">
                                    <strong>Password Requirements:</strong>
                                    <ul>
                                        <li>At least 8 characters</li>
                                        <li>Uppercase letter (A-Z)</li>
                                        <li>Lowercase letter (a-z)</li>
                                        <li>Symbol (!@#$%^&*)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label for="password_confirmation" class="auth-label">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="auth-input @error('password_confirmation') is-invalid @enderror"
                    >
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-button">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
