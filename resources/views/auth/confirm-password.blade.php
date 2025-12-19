<!-- Author: Tang Lit Xuan -->
@extends('layouts.app')

@section('title', 'Confirm Password - TAREvent')

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
                <h1 class="auth-title">Confirm password</h1>
                <p class="auth-subtitle">
                    This is a secure area of the application. Please confirm your password before continuing.
                </p>
            </div>


            <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                @csrf

                <div class="auth-form-group">
                    <label for="password" class="auth-label">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="auth-input @error('password') is-invalid @enderror"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-button">
                    Confirm
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
