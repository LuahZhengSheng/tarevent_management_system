@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
<div class="flash-messages-container">
    <div class="container">
        <!-- Success Message -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Success!</strong>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Error!</strong>
                    <p class="mb-0">{{ session('error') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Warning Message -->
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Warning!</strong>
                    <p class="mb-0">{{ session('warning') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Info Message -->
        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Info</strong>
                    <p class="mb-0">{{ session('info') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Validation Error{{ $errors->count() > 1 ? 's' : '' }}!</strong>
                    <p class="mb-1">Please fix the following issues:</p>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Custom Status Message (for specific statuses) -->
        @if(session('status'))
        <div class="alert alert-info alert-dismissible fade show flash-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="flash-icon">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-0">{{ session('status') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

<style>
.flash-messages-container {
    position: fixed;
    top: 80px;
    left: 0;
    right: 0;
    z-index: 9999;
    pointer-events: none;
    padding: 0 1rem;
}

.flash-messages-container .container {
    max-width: 800px;
    margin: 0 auto;
}

.flash-alert {
    pointer-events: auto;
    border: none;
    border-radius: 1rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-xl);
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.flash-alert.fade:not(.show) {
    animation: slideOutUp 0.3s ease-in;
}

@keyframes slideOutUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-100%);
    }
}

.flash-icon {
    font-size: 1.75rem;
    margin-right: 1rem;
    margin-top: 0.125rem;
}

.alert-success .flash-icon {
    color: var(--success);
}

.alert-danger .flash-icon {
    color: var(--error);
}

.alert-warning .flash-icon {
    color: var(--warning);
}

.alert-info .flash-icon {
    color: var(--info);
}

.flash-alert strong {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
}

.flash-alert p {
    line-height: 1.5;
}

.flash-alert ul {
    margin-top: 0.5rem;
}

.flash-alert ul li {
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.flash-alert .btn-close {
    padding: 0.75rem;
}

/* Success Alert */
.alert-success {
    background-color: var(--success-light);
    color: var(--success);
    border-left: 4px solid var(--success);
}

/* Danger Alert */
.alert-danger {
    background-color: var(--error-light);
    color: var(--error);
    border-left: 4px solid var(--error);
}

/* Warning Alert */
.alert-warning {
    background-color: var(--warning-light);
    color: #856404;
    border-left: 4px solid var(--warning);
}

/* Info Alert */
.alert-info {
    background-color: var(--info-light);
    color: var(--info);
    border-left: 4px solid var(--info);
}

/* Dark Mode Adjustments */
[data-theme="dark"] .alert-success {
    background-color: rgba(16, 185, 129, 0.15);
    color: var(--success);
}

[data-theme="dark"] .alert-danger {
    background-color: rgba(239, 68, 68, 0.15);
    color: var(--error);
}

[data-theme="dark"] .alert-warning {
    background-color: rgba(245, 158, 11, 0.15);
    color: var(--warning);
}

[data-theme="dark"] .alert-info {
    background-color: rgba(59, 130, 246, 0.15);
    color: var(--info);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .flash-messages-container {
        top: 70px;
    }

    .flash-alert {
        padding: 1rem;
        font-size: 0.9rem;
    }

    .flash-icon {
        font-size: 1.5rem;
        margin-right: 0.75rem;
    }

    .flash-alert strong {
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss flash messages after 5 seconds
    const alerts = document.querySelectorAll('.flash-alert');
    
    alerts.forEach(function(alert) {
        // Don't auto-dismiss error messages (let user manually close)
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }
    });

    // Add smooth scroll to first error if validation fails
    if (document.querySelector('.alert-danger')) {
        setTimeout(function() {
            const firstError = document.querySelector('.is-invalid, .alert-danger');
            if (firstError) {
                firstError.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 100);
    }
});
</script>