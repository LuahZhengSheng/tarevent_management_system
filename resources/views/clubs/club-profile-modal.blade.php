{{--
    Club Profile Modal - Stripe-style
    
    USAGE:
    1. Include this partial in your view:
       @include('clubs.club_profile_modal')
    
    2. Call the modal from JavaScript:
       window.openClubProfileModal(clubId);
    
    3. Example from Event Details page:
       <button onclick="window.openClubProfileModal({{ $event->club_id }})">
           View Club Profile
       </button>
    
    FEATURES:
    - Stripe-style modern design
    - Shows club information (name, description, email, phone, logo, created_at)
    - Displays member status badge if user is a member
    - Loading states
    - Error handling
    - UUID-based request tracking
    - Reusable across modules
--}}
<div class="modal fade" id="clubProfileModal" tabindex="-1" aria-labelledby="clubProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content club-profile-modal-content">
            <div class="modal-header club-profile-modal-header">
                <h5 class="modal-title" id="clubProfileModalLabel">
                    <i class="bi bi-building me-2"></i>
                    Club Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body club-profile-modal-body">
                {{-- Loading State --}}
                <div id="clubProfileLoading" class="club-profile-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading club information...</p>
                </div>

                {{-- Error State --}}
                <div id="clubProfileError" class="club-profile-error" style="display: none;">
                    <div class="club-profile-error-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h5>Unable to Load Club</h5>
                    <p id="clubProfileErrorText">An error occurred while loading club information.</p>
                    <button type="button" class="btn btn-primary mt-3" id="clubProfileRetryBtn">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Retry
                    </button>
                </div>

                {{-- Club Profile Content --}}
                <div id="clubProfileContent" style="display: none;">
                    {{-- Club Header with Logo --}}
                    <div class="club-profile-header">
                        <div class="club-profile-logo-container">
                            <img id="clubProfileLogo" src="" alt="Club Logo" class="club-profile-logo">
                        </div>
                        <div class="club-profile-header-info">
                            <h3 id="clubProfileName" class="club-profile-name"></h3>
                            <div id="clubProfileMemberBadge" class="club-profile-member-badge" style="display: none;">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Member
                            </div>
                        </div>
                    </div>

                    {{-- Club Details --}}
                    <div class="club-profile-details">
                        {{-- Description --}}
                        <div class="club-profile-section">
                            <div class="club-profile-section-title">
                                <i class="bi bi-info-circle me-2"></i>
                                About
                            </div>
                            <p id="clubProfileDescription" class="club-profile-description"></p>
                        </div>

                        {{-- Contact Information --}}
                        <div class="club-profile-section">
                            <div class="club-profile-section-title">
                                <i class="bi bi-envelope me-2"></i>
                                Contact Information
                            </div>
                            <div class="club-profile-contact-grid">
                                <div class="club-profile-contact-item">
                                    <div class="club-profile-contact-label">
                                        <i class="bi bi-envelope-fill me-2"></i>
                                        Email
                                    </div>
                                    <a id="clubProfileEmail" href="#" class="club-profile-contact-value"></a>
                                </div>
                                <div class="club-profile-contact-item">
                                    <div class="club-profile-contact-label">
                                        <i class="bi bi-telephone-fill me-2"></i>
                                        Phone
                                    </div>
                                    <a id="clubProfilePhone" href="#" class="club-profile-contact-value"></a>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="club-profile-section">
                            <div class="club-profile-section-title">
                                <i class="bi bi-calendar-event me-2"></i>
                                Club Information
                            </div>
                            <div class="club-profile-info-grid">
                                <div class="club-profile-info-item">
                                    <div class="club-profile-info-label">Category</div>
                                    <div id="clubProfileCategory" class="club-profile-info-value"></div>
                                </div>
                                <div class="club-profile-info-item">
                                    <div class="club-profile-info-label">Established</div>
                                    <div id="clubProfileCreatedAt" class="club-profile-info-value"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer club-profile-modal-footer">
                <button type="button" class="btn club-profile-btn-close" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================
   Club Profile Modal - Stripe Style
   Uses CSS Variables for Light/Dark Mode Support
======================================== */

.club-profile-modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    background: var(--bg-primary);
}

.club-profile-modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
}

.club-profile-modal-header .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    margin: 0;
}

.club-profile-modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.9;
}

.club-profile-modal-header .btn-close:hover {
    opacity: 1;
}

.club-profile-modal-body {
    padding: 2rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    min-height: 300px;
}

/* Loading State */
.club-profile-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;
}

.club-profile-loading p {
    color: var(--text-secondary);
    margin-top: 1rem;
}

/* Error State */
.club-profile-error {
    text-align: center;
    padding: 3rem 1rem;
}

.club-profile-error-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--error) 0%, #dc2626 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.club-profile-error-icon i {
    font-size: 2rem;
    color: white;
}

.club-profile-error h5 {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.club-profile-error p {
    color: var(--text-secondary);
    margin: 0 0 1rem;
}

/* Club Header */
.club-profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 1.5rem;
}

.club-profile-logo-container {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.club-profile-logo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.club-profile-header-info {
    flex: 1;
}

.club-profile-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.club-profile-member-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.875rem;
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Club Details */
.club-profile-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.club-profile-section {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid var(--border-color);
}

.club-profile-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.club-profile-section-title i {
    color: var(--primary);
}

.club-profile-description {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
    white-space: pre-line;
}

/* Contact Grid */
.club-profile-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.club-profile-contact-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.club-profile-contact-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-tertiary);
    display: flex;
    align-items: center;
}

.club-profile-contact-label i {
    color: var(--primary);
}

.club-profile-contact-value {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    word-break: break-all;
}

.club-profile-contact-value:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

/* Info Grid */
.club-profile-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.club-profile-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.club-profile-info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-tertiary);
}

.club-profile-info-value {
    color: var(--text-primary);
    font-weight: 500;
    text-transform: capitalize;
}

/* Modal Footer */
.club-profile-modal-footer {
    border: none;
    padding: 1.5rem 2rem;
    background: var(--bg-secondary);
    justify-content: center;
}

.club-profile-btn-close {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
    padding: 0.625rem 2rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.club-profile-btn-close:hover {
    background: var(--bg-secondary);
    border-color: var(--border-hover);
    color: var(--text-primary);
}

/* Dark Mode Support */
[data-theme="dark"] .club-profile-modal-content {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

[data-theme="dark"] .club-profile-error-icon {
    box-shadow: 0 4px 12px rgba(248, 113, 113, 0.4);
}

[data-theme="dark"] .club-profile-member-badge {
    box-shadow: 0 2px 8px rgba(52, 211, 153, 0.4);
}

/* Modal Animation */
.modal.fade .club-profile-modal-content {
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal.show .club-profile-modal-content {
    transform: scale(1);
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 576px) {
    .club-profile-header {
        flex-direction: column;
        text-align: center;
    }

    .club-profile-logo-container {
        width: 80px;
        height: 80px;
    }

    .club-profile-name {
        font-size: 1.5rem;
    }

    .club-profile-contact-grid,
    .club-profile-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function() {
    'use strict';

    // Wait for Bootstrap to be available
    function initModal() {
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap not loaded yet, retrying...');
            setTimeout(initModal, 100);
            return;
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Modal elements
        const modalElement = document.getElementById('clubProfileModal');
        let modalInstance = null;
        let currentClubId = null;

        if (!modalElement) {
            console.error('Club Profile Modal element not found in DOM');
            return;
        }

        // Initialize modal instance
        try {
            modalInstance = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            console.log('Club Profile Modal initialized successfully');
        } catch (error) {
            console.error('Error initializing Club Profile Modal:', error);
            return;
        }

        // DOM elements
        const loadingEl = document.getElementById('clubProfileLoading');
        const errorEl = document.getElementById('clubProfileError');
        const contentEl = document.getElementById('clubProfileContent');
        const errorTextEl = document.getElementById('clubProfileErrorText');
        const retryBtn = document.getElementById('clubProfileRetryBtn');

        // Content elements
        const logoEl = document.getElementById('clubProfileLogo');
        const nameEl = document.getElementById('clubProfileName');
        const memberBadgeEl = document.getElementById('clubProfileMemberBadge');
        const descriptionEl = document.getElementById('clubProfileDescription');
        const emailEl = document.getElementById('clubProfileEmail');
        const phoneEl = document.getElementById('clubProfilePhone');
        const categoryEl = document.getElementById('clubProfileCategory');
        const createdAtEl = document.getElementById('clubProfileCreatedAt');

        // Retry button handler
        if (retryBtn) {
            retryBtn.addEventListener('click', function() {
                if (currentClubId) {
                    loadClubProfile(currentClubId);
                }
            });
        }

        // Generate UUID using Native Browser API
        function generateRequestId() {
            return crypto.randomUUID();
        }

        // Format date to readable format
        function formatDate(dateString) {
            try {
                const date = new Date(dateString);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return date.toLocaleDateString('en-US', options);
            } catch (error) {
                return dateString;
            }
        }

        // Format phone number for display
        function formatPhone(phone) {
            // Remove all non-digit characters
            const digits = phone.replace(/\D/g, '');
            
            // Format Malaysian numbers (e.g., +60 12-345 6789)
            if (digits.startsWith('60') && digits.length >= 11) {
                const countryCode = digits.substring(0, 2);
                const part1 = digits.substring(2, 4);
                const part2 = digits.substring(4, 7);
                const part3 = digits.substring(7);
                return `+${countryCode} ${part1}-${part2} ${part3}`;
            }
            
            // Default format
            return phone;
        }

        // Show loading state
        function showLoading() {
            if (loadingEl) loadingEl.style.display = 'flex';
            if (errorEl) errorEl.style.display = 'none';
            if (contentEl) contentEl.style.display = 'none';
        }

        // Show error state
        function showError(message) {
            if (loadingEl) loadingEl.style.display = 'none';
            if (errorEl) errorEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';
            if (errorTextEl) errorTextEl.textContent = message;
        }

        // Show content
        function showContent() {
            if (loadingEl) loadingEl.style.display = 'none';
            if (errorEl) errorEl.style.display = 'none';
            if (contentEl) contentEl.style.display = 'block';
        }

        // Populate club data
        function populateClubData(data) {
            // Logo
            if (logoEl && data.logo) {
                logoEl.src = data.logo;
                logoEl.alt = data.name + ' Logo';
            }

            // Name
            if (nameEl) {
                nameEl.textContent = data.name || 'Unknown Club';
            }

            // Member badge
            if (memberBadgeEl) {
                if (data.is_member === true) {
                    memberBadgeEl.style.display = 'inline-flex';
                } else {
                    memberBadgeEl.style.display = 'none';
                }
            }

            // Description
            if (descriptionEl) {
                descriptionEl.textContent = data.description || 'No description available.';
            }

            // Email
            if (emailEl && data.email) {
                emailEl.href = 'mailto:' + data.email;
                emailEl.textContent = data.email;
            }

            // Phone
            if (phoneEl && data.phone) {
                const formattedPhone = formatPhone(data.phone);
                phoneEl.href = 'tel:' + data.phone;
                phoneEl.textContent = formattedPhone;
            }

            // Category
            if (categoryEl) {
                categoryEl.textContent = data.category || 'N/A';
            }

            // Created At
            if (createdAtEl && data.created_at) {
                createdAtEl.textContent = formatDate(data.created_at);
            }
        }

        // Load club profile
        function loadClubProfile(clubId) {
            showLoading();

            const requestId = generateRequestId();

            fetch(`/api/clubs/${clubId}?requestID=${requestId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin'
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                const status = response.status;

                // Success
                if (response.ok && data.success && data.data) {
                    console.log('Club profile loaded:', data);
                    populateClubData(data.data);
                    showContent();
                    return;
                }

                // Error handling
                let errorMessage = 'Unable to load club information.';

                if (status === 404) {
                    errorMessage = data.message || 'Club not found.';
                } else if (status === 400) {
                    errorMessage = data.message || 'Invalid request. Missing required parameters.';
                } else if (status === 401) {
                    errorMessage = 'Please login to view club details.';
                } else if (status === 403) {
                    errorMessage = data.message || 'You do not have permission to view this club.';
                } else if (status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else {
                    errorMessage = data.message || `Error (${status})`;
                }

                showError(errorMessage);
                console.error('Club Profile API Error:', status, data);
            })
            .catch(error => {
                console.error('Network error:', error);
                showError('Network error. Please check your connection and try again.');
            });
        }

        // Reset modal
        function resetModal() {
            currentClubId = null;
            showLoading();
        }

        // Reset on close
        modalElement.addEventListener('hidden.bs.modal', function() {
            resetModal();
        });

        // Expose global function
        window.openClubProfileModal = function(clubId) {
            if (!clubId) {
                console.error('Club ID is required');
                alert('Error: Club ID is required');
                return;
            }

            if (!modalInstance) {
                console.error('Club profile modal not initialized');
                alert('Error: Modal not initialized. Please refresh the page.');
                return;
            }

            console.log('Opening Club Profile Modal for Club ID:', clubId);

            currentClubId = clubId;
            
            try {
                modalInstance.show();
                loadClubProfile(clubId);
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Error showing modal: ' + error.message);
            }
        };
    }

    // Expose a placeholder function immediately to prevent errors
    // Will be replaced when modal is fully initialized
    window.openClubProfileModal = window.openClubProfileModal || function(clubId) {
        console.warn('Club Profile Modal not ready yet, retrying...');
        setTimeout(function() {
            if (window.openClubProfileModal) {
                window.openClubProfileModal(clubId);
            }
        }, 100);
    };

    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModal);
    } else {
        initModal();
    }
})();
</script>