{{--
    Join Club Request Modal - Stripe-style
    
    USAGE:
    1. Include this partial in your view:
       @include('clubs.join_modal')
    
    2. Call the modal from JavaScript:
       window.openJoinClubModal(clubId, callback);
    
    3. Example from Event page:
       <button onclick="window.openJoinClubModal({{ $event->club_id }}, function(clubId) {
           console.log('Join request submitted for club:', clubId);
           // Optional: Refresh page or update UI
       })">
           Join Club
       </button>
    
    FEATURES:
    - Stripe-style modern design
    - Form validation
    - Loading states
    - Success/error messages
    - No page redirect
    - Reusable across modules
    
    DOCUMENTATION:
    See JOIN_CLUB_MODAL_USAGE.md for complete usage guide and examples
--}}
<div class="modal fade" id="joinClubModal" tabindex="-1" aria-labelledby="joinClubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content join-club-modal-content">
            <div class="modal-header join-club-modal-header">
                <h5 class="modal-title" id="joinClubModalLabel">
                    <i class="bi bi-people-fill me-2"></i>
                    Join Club Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body join-club-modal-body">
                {{-- Success Message (hidden by default) --}}
                <div id="joinClubSuccessMessage" class="join-club-success-message" style="display: none;">
                    <div class="join-club-success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5>Request Submitted</h5>
                    <p>Join request submitted. Please wait for approval.</p>
                </div>

                {{-- Form (shown by default) --}}
                <div id="joinClubFormContainer">
                    <div class="join-club-message">
                        <i class="bi bi-info-circle me-2"></i>
                        This event is for club members only.
                    </div>

                    <form id="joinClubForm">
                        {{-- Reason Textarea --}}
                        <div class="mb-4">
                            <label for="joinReason" class="form-label">
                                Reason (Optional)
                            </label>
                            <textarea 
                                class="form-control join-club-textarea" 
                                id="joinReason" 
                                name="reason" 
                                rows="4"
                                placeholder="Tell us why you'd like to join this club..."
                                maxlength="500"></textarea>
                            <div class="form-text">
                                <span id="reasonCharCount">0</span>/500 characters
                            </div>
                        </div>

                        {{-- Agree to Rules Checkbox --}}
                        <div class="form-check join-club-checkbox mb-4">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="agreeToRules" 
                                name="agree_to_rules" 
                                required>
                            <label class="form-check-label" for="agreeToRules">
                                I agree to follow the club rules and guidelines
                                <span class="text-danger">*</span>
                            </label>
                        </div>

                        {{-- Error Message --}}
                        <div id="joinClubErrorMessage" class="join-club-error-message" style="display: none;">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <span id="joinClubErrorText"></span>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer join-club-modal-footer">
                <button type="button" class="btn join-club-btn-cancel" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn join-club-btn-submit" id="joinClubSubmitBtn">
                    <span class="join-club-btn-text">
                        <i class="bi bi-send me-2"></i>
                        Request to Join
                    </span>
                    <span class="join-club-btn-spinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================
   Join Club Modal - Stripe Style
   Uses CSS Variables for Light/Dark Mode Support
======================================== */

.join-club-modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    background: var(--bg-primary);
}

.join-club-modal-header {
    background: linear-gradient(135deg, var(--user-primary) 0%, var(--admin-primary) 100%);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
}

.join-club-modal-header .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    margin: 0;
}

.join-club-modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.9;
}

.join-club-modal-header .btn-close:hover {
    opacity: 1;
}

.join-club-modal-body {
    padding: 2rem;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.join-club-message {
    background: var(--bg-secondary);
    border-left: 4px solid var(--user-primary);
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.join-club-textarea {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    resize: vertical;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.join-club-textarea:focus {
    border-color: var(--user-primary);
    box-shadow: 0 0 0 3px var(--user-primary-light);
    outline: none;
}

.join-club-checkbox {
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    border: 2px solid var(--border-color);
    transition: all 0.2s ease;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin: 0;
    width: 100%;
    box-sizing: border-box;
}

.join-club-checkbox:hover {
    background: var(--bg-tertiary);
    border-color: var(--border-hover);
}

.join-club-checkbox .form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-top: 0.125rem;
    margin-left: 0;
    margin-right: 0;
    cursor: pointer;
    border: 2px solid var(--border-color);
    flex-shrink: 0;
    background-color: var(--bg-primary);
}

.join-club-checkbox .form-check-input:checked {
    background-color: var(--user-primary);
    border-color: var(--user-primary);
}

.join-club-checkbox .form-check-input:focus {
    box-shadow: 0 0 0 3px var(--user-primary-light);
}

.join-club-checkbox .form-check-label {
    cursor: pointer;
    color: var(--text-primary);
    font-size: 0.95rem;
    line-height: 1.5;
    flex: 1;
    margin: 0;
    padding: 0;
}

.join-club-error-message {
    background: var(--error-light);
    border: 1px solid var(--error);
    color: var(--error);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
}

.join-club-success-message {
    text-align: center;
    padding: 2rem 1rem;
}

.join-club-success-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--success) 0%, var(--success-hover) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.join-club-success-icon i {
    font-size: 2rem;
    color: white;
}

.join-club-success-message h5 {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.join-club-success-message p {
    color: var(--text-secondary);
    margin: 0;
}

.join-club-modal-footer {
    border: none;
    padding: 1.5rem 2rem;
    background: var(--bg-secondary);
    gap: 0.75rem;
}

.join-club-btn-cancel {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.join-club-btn-cancel:hover {
    background: var(--bg-secondary);
    border-color: var(--border-hover);
    color: var(--text-primary);
}

.join-club-btn-submit {
    background: linear-gradient(135deg, var(--user-primary) 0%, var(--admin-primary) 100%);
    color: white;
    border: none;
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    min-width: 160px;
}

.join-club-btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, var(--user-primary-hover) 0%, var(--admin-primary-hover) 100%);
}

.join-club-btn-submit:active:not(:disabled) {
    transform: translateY(0);
}

.join-club-btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

.join-club-btn-spinner .spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}

/* Dark Mode Support - Uses CSS Variables Automatically */
[data-theme="dark"] .join-club-modal-content {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

[data-theme="dark"] .join-club-modal-header {
    background: linear-gradient(135deg, var(--user-primary) 0%, var(--admin-primary) 100%);
}

[data-theme="dark"] .join-club-success-icon {
    box-shadow: 0 4px 12px rgba(52, 211, 153, 0.4);
}

/* Modal Animation */
.modal.fade .join-club-modal-content {
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal.show .join-club-modal-content {
    transform: scale(1);
    opacity: 1;
}

/* Backdrop */
.modal-backdrop.show {
    opacity: 0.5;
    backdrop-filter: blur(4px);
    background: var(--overlay-bg);
}

[data-theme="dark"] .modal-backdrop.show {
    opacity: 0.7;
    background: var(--overlay-bg);
}
</style>

<script>
(function() {
    'use strict';

    // Wait for Bootstrap to be available
    function initModal() {
        // Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap not loaded yet, retrying...');
            setTimeout(initModal, 100);
            return;
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Modal elements
        const modalElement = document.getElementById('joinClubModal');
        let modalInstance = null;
        let currentClubId = null;
        let successCallback = null;

        if (!modalElement) {
            console.error('Join Club Modal element not found in DOM');
            return;
        }

        // Initialize modal instance
        try {
            modalInstance = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            console.log('Join Club Modal initialized successfully');
        } catch (error) {
            console.error('Error initializing Join Club Modal:', error);
            return;
        }

        // Reset modal on close
        modalElement.addEventListener('hidden.bs.modal', function() {
            resetModal();
        });

        // Character counter for reason textarea
        const reasonTextarea = document.getElementById('joinReason');
        const reasonCharCount = document.getElementById('reasonCharCount');
    
    if (reasonTextarea && reasonCharCount) {
        reasonTextarea.addEventListener('input', function() {
            const count = this.value.length;
            reasonCharCount.textContent = count;
            if (count > 450) {
                reasonCharCount.style.color = '#dc3545';
            } else {
                reasonCharCount.style.color = '#6c757d';
            }
        });
    }

    // Form submission
    const submitBtn = document.getElementById('joinClubSubmitBtn');
    const form = document.getElementById('joinClubForm');
    
    if (submitBtn && form) {
        submitBtn.addEventListener('click', handleSubmit);
        
        // Also handle Enter key in form
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSubmit();
        });
    }

    function handleSubmit() {
        if (!currentClubId) {
            showError('Club ID is missing. Please try again.');
            return;
        }

        // Validate form
        const agreeCheckbox = document.getElementById('agreeToRules');
        if (!agreeCheckbox || !agreeCheckbox.checked) {
            showError('Please agree to the club rules and guidelines.');
            agreeCheckbox?.focus();
            return;
        }

        // Get form data
        const reason = reasonTextarea?.value.trim() || '';
        const agree = agreeCheckbox?.checked || false;

        // Show loading state
        setLoadingState(true);
        hideError();
        hideSuccess();

        // Submit request
        fetch(`/api/clubs/${currentClubId}/join`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                reason: reason,
                agree: agree
            })
        })
        .then(async response => {
            const data = await response.json();
            
            // Check HTTP status
            if (!response.ok) {
                // HTTP error (400, 500, etc.)
                const errorMessage = data.message || data.error || 
                                   (data.errors ? Object.values(data.errors).flat().join(', ') : '') ||
                                   `Server error (${response.status}). Please try again.`;
                showError(errorMessage);
                console.error('API Error:', response.status, data);
                return;
            }
            
            // Check success flag
            if (data.success === false || data.errors) {
                // Business logic error
                const errorMessage = data.message || data.error || 
                                   (data.errors ? Object.values(data.errors).flat().join(', ') : '') ||
                                   'Failed to submit join request. Please try again.';
                showError(errorMessage);
                console.error('Business Logic Error:', data);
                return;
            }
            
            // Success
            console.log('Join request successful:', data);
            showSuccess();
            
            // Close modal after 2 seconds
            setTimeout(() => {
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                // Trigger callback if provided
                if (typeof successCallback === 'function') {
                    successCallback(currentClubId);
                }
            }, 2000);
        })
        .catch(error => {
            console.error('Join club request error:', error);
            showError('Network error. Please check your connection and try again.');
        })
        .finally(() => {
            setLoadingState(false);
        });
    }

    function setLoadingState(loading) {
        if (!submitBtn) return;
        
        const btnText = submitBtn.querySelector('.join-club-btn-text');
        const btnSpinner = submitBtn.querySelector('.join-club-btn-spinner');
        
        if (loading) {
            submitBtn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnSpinner) btnSpinner.style.display = 'inline-block';
        } else {
            submitBtn.disabled = false;
            if (btnText) btnText.style.display = 'inline-block';
            if (btnSpinner) btnSpinner.style.display = 'none';
        }
    }

    function showError(message) {
        const errorDiv = document.getElementById('joinClubErrorMessage');
        const errorText = document.getElementById('joinClubErrorText');
        
        if (errorDiv && errorText) {
            errorText.textContent = message;
            errorDiv.style.display = 'flex';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function hideError() {
        const errorDiv = document.getElementById('joinClubErrorMessage');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    function showSuccess() {
        const successDiv = document.getElementById('joinClubSuccessMessage');
        const formContainer = document.getElementById('joinClubFormContainer');
        const footer = document.querySelector('.join-club-modal-footer');
        
        if (successDiv && formContainer) {
            formContainer.style.display = 'none';
            successDiv.style.display = 'block';
            
            // Hide footer buttons
            if (footer) {
                footer.style.display = 'none';
            }
        }
    }

    function hideSuccess() {
        const successDiv = document.getElementById('joinClubSuccessMessage');
        const formContainer = document.getElementById('joinClubFormContainer');
        const footer = document.querySelector('.join-club-modal-footer');
        
        if (successDiv && formContainer) {
            successDiv.style.display = 'none';
            formContainer.style.display = 'block';
            
            // Show footer buttons
            if (footer) {
                footer.style.display = 'flex';
            }
        }
    }

    function resetModal() {
        // Reset form
        if (form) {
            form.reset();
        }
        
        // Reset UI state
        hideError();
        hideSuccess();
        setLoadingState(false);
        
        // Reset character counter
        if (reasonCharCount) {
            reasonCharCount.textContent = '0';
            reasonCharCount.style.color = '#6c757d';
        }
        
        // Clear club ID and callback (only when modal is closed)
        currentClubId = null;
        successCallback = null;
    }

        // Expose global function
        window.openJoinClubModal = function(clubId, callback) {
            if (!clubId) {
                console.error('Club ID is required');
                alert('Error: Club ID is required');
                return;
            }

            if (!modalInstance) {
                console.error('Join club modal not initialized');
                alert('Error: Join club modal not initialized. Please refresh the page.');
                return;
            }

            if (!modalElement) {
                console.error('Join club modal element not found');
                alert('Error: Join club modal element not found. Please refresh the page.');
                return;
            }

            console.log('Opening Join Club Modal for Club ID:', clubId);

            // Store club ID and callback FIRST (before reset)
            currentClubId = clubId;
            successCallback = callback || null;

            // Reset form UI (but keep currentClubId)
            if (form) {
                form.reset();
            }
            hideError();
            hideSuccess();
            setLoadingState(false);
            
            // Reset character counter
            if (reasonCharCount) {
                reasonCharCount.textContent = '0';
                reasonCharCount.style.color = '#6c757d';
            }
            
            try {
                modalInstance.show();
                console.log('Modal shown successfully with Club ID:', currentClubId);
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Error showing modal: ' + error.message);
            }
        };

        // Make variables accessible to other functions
        window._joinClubModalData = {
            modalInstance: modalInstance,
            currentClubId: currentClubId,
            successCallback: successCallback,
            csrfToken: csrfToken,
            modalElement: modalElement
        };
    }

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModal);
    } else {
        // DOM is already ready, but wait for Bootstrap
        initModal();
    }
})();
</script>

