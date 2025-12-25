{{--
    Select Club Modal - For joining clubs from other modules
    
    USAGE:
    1. Include this partial in your view:
       @include('clubs.select_club_modal')
    
    2. Call the modal from JavaScript:
       window.openSelectClubModal(callback);
    
    3. Example:
       <button onclick="window.openSelectClubModal(function(clubId) {
           console.log('Join request submitted for club:', clubId);
           // Optional: Refresh page or update UI
       })">
           Join a Club
       </button>
    
    FEATURES:
    - List all clubs with join status
    - Search functionality
    - Status badges (available, member, pending, rejected)
    - 3-day cooldown for rejected requests
    - Form view after club selection
    - Uses var(--primary) CSS variables for theming
--}}
<div class="modal fade" id="selectClubModal" tabindex="-1" aria-labelledby="selectClubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content select-club-modal-content">
            <div class="modal-header select-club-modal-header">
                <h5 class="modal-title" id="selectClubModalLabel">
                    <i class="bi bi-people-fill me-2"></i>
                    <span id="modalTitleText">Select a Club to Join</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body select-club-modal-body">
                {{-- Success Message (hidden by default) --}}
                <div id="selectClubSuccessMessage" class="select-club-success-message" style="display: none;">
                    <div class="select-club-success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5>Request Submitted</h5>
                    <p>Join request submitted. Please wait for approval.</p>
                </div>

                {{-- Club List View (default) --}}
                <div id="clubListView">
                    {{-- Search Box --}}
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control select-club-search-input" 
                                id="clubSearchInput"
                                placeholder="Search clubs by name or description..."
                                autocomplete="off">
                        </div>
                    </div>

                    {{-- Loading State --}}
                    <div id="clubsLoading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading clubs...</p>
                    </div>

                    {{-- Error State --}}
                    <div id="clubsError" class="select-club-error-message" style="display: none;">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <span id="clubsErrorText"></span>
                    </div>

                    {{-- Clubs List --}}
                    <div id="clubsList" class="clubs-list-container">
                        {{-- Clubs will be dynamically inserted here --}}
                    </div>

                    {{-- Empty State --}}
                    <div id="clubsEmpty" class="text-center py-5" style="display: none;">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-tertiary);"></i>
                        <p class="mt-3 text-muted">No clubs found.</p>
                    </div>
                </div>

                {{-- Form View (after club selection) --}}
                <div id="clubFormView" style="display: none;">
                    {{-- Selected Club Info --}}
                    <div id="selectedClubInfo" class="selected-club-info mb-4">
                        {{-- Will be populated dynamically --}}
                    </div>

                    {{-- Form --}}
                    <form id="selectClubForm">
                        {{-- Reason Textarea --}}
                        <div class="mb-4">
                            <label for="selectClubReason" class="form-label">
                                Reason (Optional)
                            </label>
                            <textarea 
                                class="form-control select-club-textarea" 
                                id="selectClubReason" 
                                name="reason" 
                                rows="4"
                                placeholder="Tell us why you'd like to join this club..."
                                maxlength="500"></textarea>
                            <div class="form-text">
                                <span id="selectClubReasonCharCount">0</span>/500 characters
                            </div>
                        </div>

                        {{-- Agree to Rules Checkbox --}}
                        <div class="form-check select-club-checkbox mb-4">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="selectClubAgreeToRules" 
                                name="agree_to_rules" 
                                required>
                            <label class="form-check-label" for="selectClubAgreeToRules">
                                I agree to follow the club rules and guidelines
                                <span class="text-danger">*</span>
                            </label>
                        </div>

                        {{-- Error Message --}}
                        <div id="selectClubErrorMessage" class="select-club-error-message" style="display: none;">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <span id="selectClubErrorText"></span>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer select-club-modal-footer">
                <button type="button" class="btn select-club-btn-cancel" data-bs-dismiss="modal" id="selectClubCancelBtn">
                    Cancel
                </button>
                <button type="button" class="btn select-club-btn-back" id="selectClubBackBtn" style="display: none;">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to List
                </button>
                <button type="button" class="btn select-club-btn-submit" id="selectClubSubmitBtn">
                    <span class="select-club-btn-text">
                        <i class="bi bi-send me-2"></i>
                        Request to Join
                    </span>
                    <span class="select-club-btn-spinner" style="display: none;">
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
   Select Club Modal - Modern Design
   Uses CSS Variables for Light/Dark Mode Support
   Uses var(--primary) to match events page
======================================== */

.select-club-modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    background: var(--bg-primary);
}

.select-club-modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
}

.select-club-modal-header .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    margin: 0;
}

.select-club-modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.9;
}

.select-club-modal-header .btn-close:hover {
    opacity: 1;
}

.select-club-modal-body {
    padding: 2rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    max-height: 60vh;
    overflow-y: auto;
}

.select-club-search-input {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.select-club-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
    outline: none;
}

.input-group-text {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-right: none;
    color: var(--text-secondary);
}

.clubs-list-container {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
}

.club-card {
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.club-card:hover:not(.club-card-disabled) {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.club-card-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: var(--bg-secondary);
}

.club-card-selected {
    border-color: var(--primary);
    background: var(--primary-light);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.club-logo {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-tertiary);
    font-size: 1.5rem;
}

.club-info {
    flex: 1;
    min-width: 0;
}

.club-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.club-description {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.club-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.club-status-available {
    background: var(--success-light);
    color: var(--success);
}

.club-status-member {
    background: var(--info-light);
    color: var(--info);
}

.club-status-pending {
    background: var(--warning-light);
    color: var(--warning);
}

.club-status-rejected {
    background: var(--error-light);
    color: var(--error);
}

.club-status-rejected-cooldown {
    background: var(--success-light);
    color: var(--success);
}

.club-status-removed {
    background: var(--error-light);
    color: var(--error);
}

.club-status-removed-cooldown {
    background: var(--success-light);
    color: var(--success);
}

.club-status-blacklisted {
    background: var(--error-light);
    color: var(--error);
}

.selected-club-info {
    background: var(--bg-secondary);
    border: 2px solid var(--primary);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.selected-club-info .club-logo {
    width: 80px;
    height: 80px;
}

.selected-club-info .club-name {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.select-club-textarea {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    resize: vertical;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.select-club-textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
    outline: none;
}

.select-club-checkbox {
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

.select-club-checkbox:hover {
    background: var(--bg-tertiary);
    border-color: var(--border-hover);
}

.select-club-checkbox .form-check-input {
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

.select-club-checkbox .form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}

.select-club-checkbox .form-check-input:focus {
    box-shadow: 0 0 0 3px var(--primary-light);
}

.select-club-checkbox .form-check-label {
    cursor: pointer;
    color: var(--text-primary);
    font-size: 0.95rem;
    line-height: 1.5;
    flex: 1;
    margin: 0;
    padding: 0;
}

.select-club-error-message {
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

.select-club-success-message {
    text-align: center;
    padding: 2rem 1rem;
}

.select-club-success-icon {
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

.select-club-success-icon i {
    font-size: 2rem;
    color: white;
}

.select-club-success-message h5 {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.select-club-success-message p {
    color: var(--text-secondary);
    margin: 0;
}

.select-club-modal-footer {
    border: none;
    padding: 1.5rem 2rem;
    background: var(--bg-secondary);
    gap: 0.75rem;
}

.select-club-btn-cancel,
.select-club-btn-back {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 2px solid var(--border-color);
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.select-club-btn-cancel:hover,
.select-club-btn-back:hover {
    background: var(--bg-secondary);
    border-color: var(--border-hover);
    color: var(--text-primary);
}

.select-club-btn-submit {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    min-width: 160px;
}

.select-club-btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, var(--primary-hover) 0%, var(--secondary-hover) 100%);
}

.select-club-btn-submit:active:not(:disabled) {
    transform: translateY(0);
}

.select-club-btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

.select-club-btn-spinner .spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}

/* Dark Mode Support */
[data-theme="dark"] .select-club-modal-content {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

[data-theme="dark"] .select-club-modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

[data-theme="dark"] .select-club-success-icon {
    box-shadow: 0 4px 12px rgba(52, 211, 153, 0.4);
}

/* Modal Animation */
.modal.fade .select-club-modal-content {
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal.show .select-club-modal-content {
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
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap not loaded yet, retrying...');
            setTimeout(initModal, 100);
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const modalElement = document.getElementById('selectClubModal');
        let modalInstance = null;
        let currentView = 'list'; // 'list' or 'form'
        let selectedClubId = null;
        let selectedClubData = null;
        let clubsData = [];
        let filteredClubs = [];
        let successCallback = null;

        if (!modalElement) {
            console.error('Select Club Modal element not found in DOM');
            return;
        }

        try {
            modalInstance = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            console.log('Select Club Modal initialized successfully');
        } catch (error) {
            console.error('Error initializing Select Club Modal:', error);
            return;
        }

        // Reset modal on close
        modalElement.addEventListener('hidden.bs.modal', function() {
            resetModal();
        });

        // Elements
        const clubListView = document.getElementById('clubListView');
        const clubFormView = document.getElementById('clubFormView');
        const clubsList = document.getElementById('clubsList');
        const clubsLoading = document.getElementById('clubsLoading');
        const clubsError = document.getElementById('clubsError');
        const clubsEmpty = document.getElementById('clubsEmpty');
        const searchInput = document.getElementById('clubSearchInput');
        const backBtn = document.getElementById('selectClubBackBtn');
        const submitBtn = document.getElementById('selectClubSubmitBtn');
        const cancelBtn = document.getElementById('selectClubCancelBtn');
        const form = document.getElementById('selectClubForm');
        const reasonTextarea = document.getElementById('selectClubReason');
        const reasonCharCount = document.getElementById('selectClubReasonCharCount');
        const agreeCheckbox = document.getElementById('selectClubAgreeToRules');
        const selectedClubInfo = document.getElementById('selectedClubInfo');
        const modalTitleText = document.getElementById('modalTitleText');

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterClubs(this.value.trim());
            });
        }

        // Character counter
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

        // Back button
        if (backBtn) {
            backBtn.addEventListener('click', function() {
                showListView();
            });
        }

        // Form submission
        if (submitBtn && form) {
            submitBtn.addEventListener('click', handleSubmit);
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                handleSubmit();
            });
        }

        function loadClubs() {
            showLoading();
            hideListError();
            hideEmpty();

            fetch('/api/clubs/available', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            })
            .then(async response => {
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to load clubs');
                }

                if (data.success && data.data && data.data.clubs) {
                    clubsData = data.data.clubs;
                    filteredClubs = clubsData;
                    renderClubs();
                } else {
                    throw new Error('Invalid response format');
                }
            })
            .catch(error => {
                console.error('Error loading clubs:', error);
                showListError(error.message || 'Failed to load clubs. Please try again.');
            })
            .finally(() => {
                hideLoading();
            });
        }

        function filterClubs(searchTerm) {
            if (!searchTerm) {
                filteredClubs = clubsData;
            } else {
                const term = searchTerm.toLowerCase();
                filteredClubs = clubsData.filter(club => {
                    return club.name.toLowerCase().includes(term) ||
                           (club.description && club.description.toLowerCase().includes(term));
                });
            }
            renderClubs();
        }

        function renderClubs() {
            if (!clubsList) return;

            if (filteredClubs.length === 0) {
                showEmpty();
                clubsList.innerHTML = '';
                return;
            }

            hideEmpty();
            clubsList.innerHTML = filteredClubs.map(club => {
                const isDisabled = ['member', 'pending', 'rejected', 'removed', 'blacklisted'].includes(club.join_status);
                const statusClass = `club-status-${club.join_status}`;
                let statusText = '';
                let cooldownText = '';

                switch (club.join_status) {
                    case 'available':
                        statusText = 'Available';
                        break;
                    case 'member':
                        statusText = 'Already a Member';
                        break;
                    case 'pending':
                        statusText = 'Request Pending';
                        break;
                    case 'rejected':
                        statusText = 'Rejected';
                        if (club.cooldown_remaining_days !== null) {
                            cooldownText = ` (Wait ${club.cooldown_remaining_days} more day${club.cooldown_remaining_days > 1 ? 's' : ''})`;
                        }
                        break;
                    case 'rejected_cooldown':
                        statusText = 'Can Retry';
                        break;
                    case 'removed':
                        statusText = 'Removed';
                        if (club.cooldown_remaining_days !== null) {
                            cooldownText = ` (Wait ${club.cooldown_remaining_days} more day${club.cooldown_remaining_days > 1 ? 's' : ''})`;
                        }
                        break;
                    case 'removed_cooldown':
                        statusText = 'Can Retry';
                        break;
                    case 'blacklisted':
                        statusText = 'Blacklisted';
                        if (club.blacklist_reason) {
                            cooldownText = ` (${club.blacklist_reason})`;
                        }
                        break;
                }

                const logoHtml = club.logo 
                    ? `<img src="${club.logo}" alt="${club.name}" class="club-logo">`
                    : `<div class="club-logo"><i class="bi bi-people"></i></div>`;

                return `
                    <div class="club-card ${isDisabled ? 'club-card-disabled' : ''}" 
                         data-club-id="${club.id}"
                         ${!isDisabled ? 'onclick="window._selectClubModalData.selectClub(' + club.id + ')"' : ''}>
                        ${logoHtml}
                        <div class="club-info">
                            <div class="club-name">${escapeHtml(club.name)}</div>
                            <p class="club-description">${escapeHtml(club.description || 'No description available.')}</p>
                            <span class="club-status-badge ${statusClass}">
                                ${statusText}${cooldownText}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function selectClub(clubId) {
            const club = clubsData.find(c => c.id === clubId);
            if (!club) {
                console.error('Club not found:', clubId);
                return;
            }

            // Check if club can be selected
            if (['member', 'pending', 'rejected', 'removed', 'blacklisted'].includes(club.join_status)) {
                return; // Cannot select disabled clubs
            }

            selectedClubId = clubId;
            selectedClubData = club;
            showFormView();
        }

        function showFormView() {
            currentView = 'form';
            
            if (clubListView) clubListView.style.display = 'none';
            if (clubFormView) clubFormView.style.display = 'block';
            if (backBtn) backBtn.style.display = 'inline-block';
            if (cancelBtn) cancelBtn.style.display = 'none';
            if (modalTitleText) modalTitleText.textContent = 'Join Club Request';
            
            // Ensure footer is visible
            const footer = document.querySelector('.select-club-modal-footer');
            const successDiv = document.getElementById('selectClubSuccessMessage');
            if (footer) footer.style.display = 'flex';
            if (successDiv) successDiv.style.display = 'none';

            // Render selected club info
            if (selectedClubInfo && selectedClubData) {
                const logoHtml = selectedClubData.logo 
                    ? `<img src="${selectedClubData.logo}" alt="${selectedClubData.name}" class="club-logo">`
                    : `<div class="club-logo"><i class="bi bi-people"></i></div>`;

                selectedClubInfo.innerHTML = `
                    ${logoHtml}
                    <div class="club-info">
                        <div class="club-name">${escapeHtml(selectedClubData.name)}</div>
                        <p class="club-description">${escapeHtml(selectedClubData.description || 'No description available.')}</p>
                    </div>
                `;
            }

            // Reset form
            if (form) form.reset();
            if (reasonCharCount) {
                reasonCharCount.textContent = '0';
                reasonCharCount.style.color = '#6c757d';
            }
            hideError();
        }

        function showListView() {
            currentView = 'list';
            
            if (clubListView) clubListView.style.display = 'block';
            if (clubFormView) clubFormView.style.display = 'none';
            if (backBtn) backBtn.style.display = 'none';
            if (cancelBtn) cancelBtn.style.display = 'inline-block';
            if (modalTitleText) modalTitleText.textContent = 'Select a Club to Join';

            // Ensure footer is visible
            const footer = document.querySelector('.select-club-modal-footer');
            const successDiv = document.getElementById('selectClubSuccessMessage');
            if (footer) footer.style.display = 'flex';
            if (successDiv) successDiv.style.display = 'none';

            selectedClubId = null;
            selectedClubData = null;
        }

        function handleSubmit() {
            if (!selectedClubId) {
                showFormError('Please select a club first.');
                return;
            }

            if (!agreeCheckbox || !agreeCheckbox.checked) {
                showFormError('Please agree to the club rules and guidelines.');
                agreeCheckbox?.focus();
                return;
            }

            const reason = reasonTextarea?.value.trim() || '';
            const agree = agreeCheckbox?.checked || false;

            setLoadingState(true);
            hideFormError();

            fetch(`/api/clubs/${selectedClubId}/join`, {
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
                
                if (!response.ok) {
                    const errorMessage = data.message || data.error || 
                                       (data.errors ? Object.values(data.errors).flat().join(', ') : '') ||
                                       `Server error (${response.status}). Please try again.`;
                    showFormError(errorMessage);
                    return;
                }
                
                if (data.success === false || data.errors) {
                    const errorMessage = data.message || data.error || 
                                       (data.errors ? Object.values(data.errors).flat().join(', ') : '') ||
                                       'Failed to submit join request. Please try again.';
                    showFormError(errorMessage);
                    return;
                }
                
                console.log('Join request successful:', data);
                showSuccess();
                
                setTimeout(() => {
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    if (typeof successCallback === 'function') {
                        successCallback(selectedClubId);
                    }
                }, 2000);
            })
            .catch(error => {
                console.error('Join club request error:', error);
                showFormError('Network error. Please check your connection and try again.');
            })
            .finally(() => {
                setLoadingState(false);
            });
        }

        function setLoadingState(loading) {
            if (!submitBtn) return;
            
            const btnText = submitBtn.querySelector('.select-club-btn-text');
            const btnSpinner = submitBtn.querySelector('.select-club-btn-spinner');
            
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

        function showFormError(message) {
            const errorDiv = document.getElementById('selectClubErrorMessage');
            const errorText = document.getElementById('selectClubErrorText');
            
            if (errorDiv && errorText) {
                errorText.textContent = message;
                errorDiv.style.display = 'flex';
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function hideFormError() {
            const errorDiv = document.getElementById('selectClubErrorMessage');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }

        function showListError(message) {
            const errorDiv = document.getElementById('clubsError');
            const errorText = document.getElementById('clubsErrorText');
            
            if (errorDiv && errorText) {
                errorText.textContent = message;
                errorDiv.style.display = 'flex';
            }
        }

        function hideListError() {
            const errorDiv = document.getElementById('clubsError');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }

        function showSuccess() {
            const successDiv = document.getElementById('selectClubSuccessMessage');
            const formContainer = document.getElementById('clubFormView');
            const listView = document.getElementById('clubListView');
            const footer = document.querySelector('.select-club-modal-footer');
            
            if (successDiv) {
                // Hide all views and show success message
                if (formContainer) formContainer.style.display = 'none';
                if (listView) listView.style.display = 'none';
                successDiv.style.display = 'block';
                
                // Hide footer during success message
                if (footer) {
                    footer.style.display = 'none';
                }
            }
        }

        function showLoading() {
            if (clubsLoading) clubsLoading.style.display = 'block';
            if (clubsList) clubsList.style.display = 'none';
        }

        function hideLoading() {
            if (clubsLoading) clubsLoading.style.display = 'none';
            if (clubsList) clubsList.style.display = 'block';
        }

        function showEmpty() {
            if (clubsEmpty) clubsEmpty.style.display = 'block';
        }

        function hideEmpty() {
            if (clubsEmpty) clubsEmpty.style.display = 'none';
        }

        function resetModal() {
            showListView();
            if (form) form.reset();
            if (searchInput) searchInput.value = '';
            hideFormError();
            hideListError();
            setLoadingState(false);
            if (reasonCharCount) {
                reasonCharCount.textContent = '0';
                reasonCharCount.style.color = '#6c757d';
            }
            selectedClubId = null;
            selectedClubData = null;
            successCallback = null;
            clubsData = [];
            filteredClubs = [];
            
            // Reset success message and footer visibility
            const successDiv = document.getElementById('selectClubSuccessMessage');
            const footer = document.querySelector('.select-club-modal-footer');
            if (successDiv) {
                successDiv.style.display = 'none';
            }
            if (footer) {
                footer.style.display = 'flex'; // Restore footer display
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Expose global function
        window.openSelectClubModal = function(callback) {
            if (!modalInstance) {
                console.error('Select club modal not initialized');
                alert('Error: Select club modal not initialized. Please refresh the page.');
                return;
            }

            successCallback = callback || null;
            resetModal();
            loadClubs();
            
            try {
                modalInstance.show();
                console.log('Select Club Modal shown successfully');
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Error showing modal: ' + error.message);
            }
        };

        // Expose data for internal use
        window._selectClubModalData = {
            selectClub: selectClub,
            modalInstance: modalInstance,
            csrfToken: csrfToken,
        };
    }

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModal);
    } else {
        initModal();
    }
})();
</script>

