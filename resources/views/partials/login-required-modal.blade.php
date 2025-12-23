{{-- Login Required Modal - 通用登录提示弹窗 --}}
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginRequiredModalLabel">
                    <i class="bi bi-person-circle me-2"></i>
                    Login Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="login-required-icon mb-3">
                    <i class="bi bi-lock-fill" style="font-size: 3rem; color: var(--primary);"></i>
                </div>
                <h4 class="mb-3">Please Login to Continue</h4>
                <p class="text-muted mb-4" id="loginRequiredMessage">
                    You need to be logged in to register for this event.
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login Now
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
                <div class="mt-3">
                    <p class="text-muted small mb-0">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="text-decoration-none">
                            <strong>Register here</strong>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-required-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#loginRequiredModal .modal-content {
    border-radius: 1rem;
    border: none;
    box-shadow: var(--shadow-xl);
}

#loginRequiredModal .modal-header {
    padding: 1.5rem;
}

#loginRequiredModal .modal-body {
    padding: 2rem 1.5rem;
}
</style>

<script>
// 通用函数：显示登录提示
window.showLoginRequired = function(message) {
    const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
    
    if (message) {
        document.getElementById('loginRequiredMessage').textContent = message;
    }
    
    modal.show();
};
</script>