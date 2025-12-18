<div class="profile-section-header">
    <h2 class="profile-section-title" style="color: var(--error);">Delete Account</h2>
    <p class="profile-section-subtitle">
        Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
    </p>
</div>

<button
    type="button"
    class="auth-button"
    style="background: var(--error); width: auto; padding: 0.75rem 1.75rem;"
    data-bs-toggle="modal"
    data-bs-target="#deleteAccountModal"
>
    Delete Account
</button>

<!-- Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 1rem;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 1.5rem;">
                <h5 class="modal-title" id="deleteAccountModalLabel" style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">
                    Are you sure you want to delete your account?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body" style="padding: 1.5rem;">
                    <p style="font-size: 0.9375rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                        Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                    </p>

                    <div class="mb-3">
                        <label for="password" class="auth-label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="auth-input @error('password', 'userDeletion') is-invalid @enderror"
                        >
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 1.5rem; gap: 0.75rem;">
                    <button type="button" class="auth-button auth-button-secondary" data-bs-dismiss="modal" style="width: auto; padding: 0.75rem 1.5rem;">
                        Cancel
                    </button>
                    <button type="submit" class="auth-button" style="background: var(--error); width: auto; padding: 0.75rem 1.5rem;">
                        Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
        border-color: var(--error);
        box-shadow: 0 0 0 3px var(--error-light);
    }

    .auth-button {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .auth-button:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .auth-button-secondary {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .auth-button-secondary:hover {
        background: var(--bg-secondary);
        border-color: var(--border-hover);
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

    .btn-close {
        filter: var(--text-secondary);
    }

    [data-theme="dark"] .btn-close {
        filter: invert(1);
    }
</style>
