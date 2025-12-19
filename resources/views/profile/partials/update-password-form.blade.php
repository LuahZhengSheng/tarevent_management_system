<!-- Author: Tang Lit Xuan -->
<div class="profile-section-header">
    <h2 class="profile-section-title">Update Password</h2>
    <p class="profile-section-subtitle">Ensure your account is using a long, complex password to stay secure.</p>
</div>

<form method="post" action="{{ route('password.update') }}" novalidate>
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="update_password_current_password" class="auth-label">Current Password</label>
        <input
            id="update_password_current_password"
            name="current_password"
            type="password"
            autocomplete="current-password"
            class="auth-input @error('current_password', 'updatePassword') is-invalid @enderror"
        >
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="auth-label">New Password</label>
        <div class="password-input-wrapper">
            <input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                class="auth-input @error('password', 'updatePassword') is-invalid @enderror"
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
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="auth-label">Confirm Password</label>
        <input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            class="auth-input @error('password_confirmation', 'updatePassword') is-invalid @enderror"
        >
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <button type="submit" class="auth-button" style="width: auto; padding: 0.75rem 1.75rem;">
            Save Changes
        </button>

        @if (session('status') === 'password-updated')
            <p style="margin: 0; font-size: 0.875rem; color: var(--success); font-weight: 500;">
                Saved.
            </p>
        @endif
    </div>
</form>

<style>
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

    .auth-button {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .auth-button:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
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
</style>
