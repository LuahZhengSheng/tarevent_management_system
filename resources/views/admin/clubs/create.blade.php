@extends('layouts.admin')

@section('title', 'Create New Club')

@section('content')
<div class="create-club-page-modern">
    <!-- Hero Section -->
    <div class="hero-section-modern">
        <div class="hero-background">
            <div class="hero-pattern"></div>
            <div class="hero-gradient"></div>
        </div>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="hero-content">
                        <!-- <div class="hero-badge">
                            <i class="bi bi-shield-check me-2"></i>
                            <span>Admin Only - Testing/Admin Use</span>
                        </div> -->
                        <h1 class="hero-title">Create New Club</h1>
                        <p class="hero-description">
                            Create a club and club account. This page is for testing/admin use only.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-modern">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Combined Form -->
                <div class="form-card">
                    <h3 class="form-section-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Create Club & Club Account
                    </h3>
                    
                    <form id="createClubForm" enctype="multipart/form-data">
                        <!-- Club Information Section -->
                        <div class="form-subsection mb-4">
                            <h4 class="form-subsection-title">
                                <i class="bi bi-building me-2"></i>
                                Club Information
                            </h4>
                            
                            <div class="mb-4">
                                <label for="clubName" class="form-label">
                                    Club Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-modern" 
                                       id="clubName" 
                                       name="name" 
                                       placeholder="Enter club name"
                                       maxlength="255"
                                       required>
                                <div class="form-text">The official name of the club (max 255 characters)</div>
                                <div class="invalid-feedback" id="clubNameError"></div>
                            </div>

                            <div class="mb-4">
                                <label for="clubDescription" class="form-label">
                                    Description
                                </label>
                                <textarea class="form-control form-control-modern" 
                                          id="clubDescription" 
                                          name="description" 
                                          rows="4"
                                          placeholder="Enter club description..."></textarea>
                                <div class="form-text">Brief description of the club's purpose and activities</div>
                            </div>

                            <div class="mb-4">
                                <label for="clubCategory" class="form-label">
                                    Category
                                </label>
                                <select class="form-select form-control-modern" 
                                        id="clubCategory" 
                                        name="category">
                                    <option value="">Select a category (optional)</option>
                                    <option value="academic">Academic</option>
                                    <option value="sports">Sports</option>
                                    <option value="cultural">Cultural</option>
                                    <option value="social">Social</option>
                                    <option value="volunteer">Volunteer</option>
                                    <option value="professional">Professional</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="form-text">Select the category that best describes the club</div>
                                <div class="invalid-feedback" id="clubCategoryError"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="clubEmail" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-modern" 
                                           id="clubEmail" 
                                           name="email" 
                                           placeholder="club@example.com"
                                           maxlength="255"
                                           required>
                                    <div class="form-text">Email address (shared with club account)</div>
                                    <div class="invalid-feedback" id="clubEmailError"></div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="clubPhone" class="form-label">
                                        Phone <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-modern" 
                                           id="clubPhone" 
                                           name="phone" 
                                           placeholder="0123456789"
                                           maxlength="50"
                                           pattern="^01\d{8,9}$"
                                           required>
                                    <div class="form-text">Contact phone (shared with club account, format: 01XXXXXXXX)</div>
                                    <div class="invalid-feedback" id="clubPhoneError"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="clubLogo" class="form-label">
                                    Logo
                                </label>
                                <input type="file" 
                                       class="form-control form-control-modern" 
                                       id="clubLogo" 
                                       name="logo" 
                                       accept="image/*">
                                <div class="form-text">Upload club logo image (optional, max 2MB)</div>
                                <div id="logoPreview" class="mt-2" style="display: none;">
                                    <img id="logoPreviewImg" src="" alt="Logo preview" style="max-width: 200px; max-height: 200px; border-radius: 4px; border: 1px solid var(--border-color);">
                                </div>
                            </div>
                        </div>

                        <!-- Club Account Information Section -->
                        <div class="form-subsection mb-4">
                            <h4 class="form-subsection-title">
                                <i class="bi bi-person-plus me-2"></i>
                                Club Account Information
                            </h4>
                            
                            <div class="mb-4">
                                <label for="accountName" class="form-label">
                                    Account Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-modern" 
                                       id="accountName" 
                                       name="account_name" 
                                       placeholder="Enter club account name"
                                       maxlength="255"
                                       required>
                                <div class="form-text">Name for the club account (same as club name)</div>
                                <div class="invalid-feedback" id="accountNameError"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="accountPassword" class="form-label">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control form-control-modern" 
                                           id="accountPassword" 
                                           name="account_password" 
                                           placeholder="Enter password"
                                           minlength="8"
                                           required>
                                    <div class="form-text">Must be at least 8 characters with uppercase, lowercase, numbers, and special characters</div>
                                    <div class="invalid-feedback" id="accountPasswordError"></div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="accountPasswordConfirmation" class="form-label">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control form-control-modern" 
                                           id="accountPasswordConfirmation" 
                                           name="account_password_confirmation" 
                                           placeholder="Confirm password"
                                           minlength="8"
                                           required>
                                    <div class="form-text">Must match password</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="clubId" class="form-label">
                                        Club ID <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-modern" 
                                           id="clubId" 
                                           name="club_id" 
                                           placeholder="24PMD10293"
                                           maxlength="50"
                                           pattern="^\d{2}[A-Za-z]{3}\d{5}$"
                                           required>
                                    <div class="form-text">Format: 2 digits, 3 letters, 5 digits (e.g., 24PMD10293)</div>
                                    <div class="invalid-feedback" id="clubIdError"></div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="accountProgram" class="form-label">
                                        Program
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-modern" 
                                           id="accountProgram" 
                                           name="account_program" 
                                           placeholder="N/A"
                                           maxlength="255">
                                    <div class="form-text">Program name (optional, max 255 characters)</div>
                                </div>
                            </div>

                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-modern">
                                <i class="bi bi-check-circle me-2"></i>
                                Create Club & Account
                            </button>
                        </div>
                    </form>

                    <div id="formResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/clubs/admin-create-club.css')
<style>
    .form-control-modern[type="file"] {
        padding: 0.5rem;
    }
    #logoPreview {
        border: 1px solid var(--border-color);
        padding: 10px;
        border-radius: 4px;
        background: var(--bg-secondary);
    }
    .form-subsection {
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    .form-subsection-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    .form-control-modern.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8-.4-.4-.4.4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .form-control-modern.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.98-.98-.98-.98L1.32 4.77l.98.98zm2.85-5.15L2.3 6.73l1.85 1.85 4.9-4.9L6.15.63z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
    .valid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #198754;
    }
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }
    .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }
    .alert-warning {
        color: #664d03;
        background-color: #fff3cd;
        border-color: #ffecb5;
    }
    .alert-info {
        color: #055160;
        background-color: #cff4fc;
        border-color: #b6effb;
    }
    .alert ul {
        margin-top: 0.5rem;
        margin-bottom: 0;
        padding-left: 1.5rem;
    }
    .alert pre {
        margin-top: 0.75rem;
        margin-bottom: 0;
        background: rgba(0, 0, 0, 0.05);
        padding: 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
</style>
@endpush

@push('scripts')
<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Store club ID after creation
    let clubId = null;

    // Helper function to show field error
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + 'Error');
        if (field && errorDiv) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    // Helper function to clear field error
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + 'Error');
        if (field && errorDiv) {
            field.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    }

    // Helper function to show field success
    function showFieldSuccess(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        }
    }

    // Clear all field errors
    function clearAllFieldErrors() {
        ['clubName', 'clubEmail', 'clubPhone', 'accountName', 'clubId', 'accountPassword', 'accountPasswordConfirmation'].forEach(id => {
            clearFieldError(id);
        });
    }

    // Sync Club Name to Account Name
    document.getElementById('clubName').addEventListener('input', function() {
        document.getElementById('accountName').value = this.value;
        clearFieldError('clubName');
        clearFieldError('accountName');
    });

    // Logo preview handler
    document.getElementById('clubLogo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreviewImg').src = e.target.result;
                document.getElementById('logoPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('logoPreview').style.display = 'none';
        }
    });

    // Password validation (must have uppercase, lowercase, numbers, and special characters)
    function validatePassword(password) {
        const errors = [];
        
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters');
        }
        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }
        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }
        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number');
        }
        if (!/[^a-zA-Z0-9]/.test(password)) {
            errors.push('Password must contain at least one special character');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    // Real-time password validation
    document.getElementById('accountPassword').addEventListener('input', function() {
        const password = this.value;
        const validation = validatePassword(password);
        const errorDiv = document.getElementById('accountPasswordError');
        
        if (password.length > 0) {
            if (validation.isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                clearFieldError('accountPassword');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                if (errorDiv && validation.errors.length > 0) {
                    errorDiv.textContent = validation.errors[0];
                    errorDiv.style.display = 'block';
                }
            }
        } else {
            this.classList.remove('is-invalid', 'is-valid');
            clearFieldError('accountPassword');
        }
    });

    // Password confirmation validation
    document.getElementById('accountPasswordConfirmation').addEventListener('input', function() {
        const password = document.getElementById('accountPassword').value;
        const confirmation = this.value;
        if (confirmation && password !== confirmation) {
            this.setCustomValidity('Passwords do not match');
            showFieldError('accountPasswordConfirmation', 'Passwords do not match');
        } else {
            this.setCustomValidity('');
            clearFieldError('accountPasswordConfirmation');
            if (confirmation && password === confirmation && password.length > 0) {
                showFieldSuccess('accountPasswordConfirmation');
            }
        }
    });

    // Club ID format validation
    document.getElementById('clubId').addEventListener('input', function() {
        const value = this.value.toUpperCase();
        this.value = value;
        const pattern = /^\d{2}[A-Za-z]{3}\d{5}$/;
        if (value && !pattern.test(value)) {
            this.setCustomValidity('Format: 2 digits, 3 letters, 5 digits (e.g., 24PMD10293)');
            showFieldError('clubId', 'Format: 2 digits, 3 letters, 5 digits (e.g., 24PMD10293)');
        } else {
            this.setCustomValidity('');
            clearFieldError('clubId');
            if (value && pattern.test(value)) {
                showFieldSuccess('clubId');
            }
        }
    });

    // Phone format validation
    function validatePhone(fieldId) {
        const field = document.getElementById(fieldId);
        const value = field.value.replace(/\D/g, '');
        if (value && !value.startsWith('01')) {
            field.setCustomValidity('Must start with 01 (Malaysian mobile format)');
            showFieldError(fieldId, 'Must start with 01 (Malaysian mobile format)');
        } else if (value && (value.length < 10 || value.length > 11)) {
            field.setCustomValidity('Must be 10-11 digits');
            showFieldError(fieldId, 'Must be 10-11 digits');
        } else {
            field.setCustomValidity('');
            clearFieldError(fieldId);
            if (value && value.length >= 10 && value.length <= 11 && value.startsWith('01')) {
                showFieldSuccess(fieldId);
            }
        }
    }

    document.getElementById('clubPhone').addEventListener('input', function() {
        validatePhone('clubPhone');
    });

    // Email validation
    function validateEmail(fieldId) {
        const field = document.getElementById(fieldId);
        const value = field.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (value && !emailPattern.test(value)) {
            showFieldError(fieldId, 'Please enter a valid email address');
            return false;
        } else {
            clearFieldError(fieldId);
            if (value && emailPattern.test(value)) {
                showFieldSuccess(fieldId);
            }
            return true;
        }
    }

    // Check if user email already exists
    let emailCheckTimeout = null;
    async function checkUserEmailExists(email) {
        if (!email || !email.includes('@')) {
            return;
        }

        try {
            const response = await fetch(`/admin/clubs/check-email?email=${encodeURIComponent(email)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (data.exists) {
                showFieldError('clubEmail', 'This email is already registered to a user. Please use a different email.');
            } else {
                // Only show success if email format is valid
                if (validateEmail('clubEmail')) {
                    clearFieldError('clubEmail');
                }
            }
        } catch (error) {
            console.error('Error checking email:', error);
            // Don't show error to user if check fails - let backend handle it
        }
    }

    document.getElementById('clubEmail').addEventListener('blur', function() {
        const email = this.value.trim();
        if (validateEmail('clubEmail') && email) {
            checkUserEmailExists(email);
        }
    });

    // Real-time validation with debounce
    document.getElementById('clubEmail').addEventListener('input', function() {
        const email = this.value.trim();
        
        // Clear previous timeout
        if (emailCheckTimeout) {
            clearTimeout(emailCheckTimeout);
        }

        // Basic format validation
        if (email) {
            validateEmail('clubEmail');
        } else {
            clearFieldError('clubEmail');
        }

        // Debounce email existence check (wait 500ms after user stops typing)
        if (email && email.includes('@')) {
            emailCheckTimeout = setTimeout(() => {
                if (validateEmail('clubEmail')) {
                    checkUserEmailExists(email);
                }
            }, 500);
        }
    });

    // Generate timestamp in IFA format (YYYY-MM-DD HH:MM:SS)
    function generateTimestamp() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    // Combined Form Handler
    document.getElementById('createClubForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Clear all previous errors
        clearAllFieldErrors();
        
        const responseDiv = document.getElementById('formResponse');
        responseDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i><strong>Creating club and account...</strong><br>Please wait while we process your request.</div>';

        try {
            // Step 1: Create Club
            const clubFormData = new FormData();
            // Add timestamp for IFA standard
            clubFormData.append('timestamp', generateTimestamp());
            clubFormData.append('name', document.getElementById('clubName').value.trim());
            clubFormData.append('description', document.getElementById('clubDescription').value.trim());
            const categoryValue = document.getElementById('clubCategory').value.trim();
            if (categoryValue) {
                clubFormData.append('category', categoryValue);
            }
            clubFormData.append('email', document.getElementById('clubEmail').value.trim());
            clubFormData.append('phone', document.getElementById('clubPhone').value.trim());
            
            const logoFile = document.getElementById('clubLogo').files[0];
            if (logoFile) {
                clubFormData.append('logo', logoFile);
            }

            const token = localStorage.getItem('api_token') || '';
            
            const clubResponse = await fetch('/api/clubs', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'same-origin',
                body: clubFormData,
            });

            const clubData = await clubResponse.json();

            if (!clubResponse.ok || !clubData.success) {
                // Show field-level errors
                if (clubData.errors) {
                    for (const [field, messages] of Object.entries(clubData.errors)) {
                        const fieldId = field === 'name' ? 'clubName' : 
                                       field === 'email' ? 'clubEmail' : 
                                       field === 'phone' ? 'clubPhone' : field;
                        if (messages && messages.length > 0) {
                            showFieldError(fieldId, messages[0]);
                        }
                    }
                }

                let errorHtml = '<div class="alert alert-danger">';
                errorHtml += '<div style="display: flex; align-items: center; margin-bottom: 0.75rem;">';
                errorHtml += '<i class="bi bi-x-circle me-2" style="font-size: 1.25rem;"></i>';
                errorHtml += '<strong>Error creating club</strong>';
                errorHtml += '</div>';
                
                if (clubData.errors) {
                    errorHtml += '<ul style="margin-top: 0.5rem; margin-bottom: 0;">';
                    for (const [field, messages] of Object.entries(clubData.errors)) {
                        messages.forEach(msg => {
                            errorHtml += `<li><strong>${field}:</strong> ${msg}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                } else {
                    errorHtml += `<p style="margin-top: 0.5rem; margin-bottom: 0;">${clubData.message || 'Unknown error occurred'}</p>`;
                }
                errorHtml += '</div>';
                responseDiv.innerHTML = errorHtml;
                return;
            }

            // Get club ID from response
            clubId = clubData.data.id;

            // Step 2: Create Club Account with club_id
            // Use club email and phone for account (same values)
            
            // Format timestamp as YYYY-MM-DD HH:MM:SS (IFA standard)
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

            const accountFormData = {
                name: document.getElementById('accountName').value.trim(),
                email: document.getElementById('clubEmail').value.trim(), // Use club email
                password: document.getElementById('accountPassword').value,
                password_confirmation: document.getElementById('accountPasswordConfirmation').value,
                student_id: document.getElementById('clubId').value.toUpperCase().trim(),
                phone: document.getElementById('clubPhone').value.trim(), // Use club phone
                program: document.getElementById('accountProgram').value.trim() || 'N/A',
                status: 'active', // Always active, hidden field
                club_id: clubId, // Link to the created club
                timestamp: timestamp, // IFA standard format: YYYY-MM-DD HH:MM:SS
            };

            const accountResponse = await fetch('/api/v1/club-users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(accountFormData),
            });

            const accountData = await accountResponse.json();

            if (!accountResponse.ok || !accountData.success) {
                // Show field-level errors
                if (accountData.errors) {
                    for (const [field, messages] of Object.entries(accountData.errors)) {
                        // Map account errors to club fields (email and phone are shared)
                        const fieldId = field === 'name' ? 'accountName' : 
                                       field === 'email' ? 'clubEmail' : // Account email error shows on club email field
                                       field === 'phone' ? 'clubPhone' : // Account phone error shows on club phone field
                                       field === 'student_id' ? 'clubId' : field;
                        if (messages && messages.length > 0) {
                            showFieldError(fieldId, messages[0]);
                        }
                    }
                }

                // Club was created but account creation failed
                let errorHtml = '<div class="alert alert-warning">';
                errorHtml += '<div style="display: flex; align-items: center; margin-bottom: 0.75rem;">';
                errorHtml += '<i class="bi bi-exclamation-triangle me-2" style="font-size: 1.25rem;"></i>';
                errorHtml += '<strong>Club created but account creation failed</strong>';
                errorHtml += '</div>';
                errorHtml += `<p style="margin-top: 0.5rem; margin-bottom: 0.75rem;"><strong>Club ID:</strong> ${clubId}</p>`;
                errorHtml += '<p style="margin-bottom: 0.75rem; color: #664d03;">Please fix the errors below and try creating the account again, or delete the created club if needed.</p>';
                
                if (accountData.errors) {
                    errorHtml += '<ul style="margin-top: 0.5rem; margin-bottom: 0;">';
                    for (const [field, messages] of Object.entries(accountData.errors)) {
                        messages.forEach(msg => {
                            errorHtml += `<li><strong>${field}:</strong> ${msg}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                } else {
                    errorHtml += `<p style="margin-top: 0.5rem; margin-bottom: 0;">${accountData.message || 'Unknown error occurred'}</p>`;
                }
                errorHtml += '</div>';
                responseDiv.innerHTML = errorHtml;
                return;
            }

            // Step 3: Update club with club_user_id
            const updateClubData = {
                timestamp: generateTimestamp(), // Add timestamp for IFA standard
                club_user_id: accountData.data.id,
            };

            const token = localStorage.getItem('api_token') || '';
            
            const updateResponse = await fetch(`/api/clubs/${clubId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'same-origin',
                body: JSON.stringify(updateClubData),
            });

            const updateData = await updateResponse.json();

            // Success - show results
            responseDiv.innerHTML = `
                <div class="alert alert-success">
                    <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                        <i class="bi bi-check-circle me-2" style="font-size: 1.5rem;"></i>
                        <strong style="font-size: 1.1rem;">Club and account created successfully!</strong>
                    </div>
                    <div style="background: rgba(15, 81, 50, 0.1); padding: 1rem; border-radius: 0.375rem; margin-top: 0.75rem;">
                        <p style="margin-bottom: 0.5rem;"><strong>Club ID:</strong> ${clubId}</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Club Name:</strong> ${clubData.data.name}</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Club User ID:</strong> ${accountData.data.id}</p>
                        <p style="margin-bottom: 0;"><strong>Club User Email:</strong> ${accountData.data.email}</p>
                    </div>
                    <details style="margin-top: 1rem;">
                        <summary style="cursor: pointer; font-weight: 600; color: #0f5132;">View Response Details</summary>
                        <div style="margin-top: 0.75rem;">
                            <h5 style="font-size: 0.95rem; margin-bottom: 0.5rem;">Club Response:</h5>
                            <pre style="background: rgba(0, 0, 0, 0.05); padding: 0.75rem; border-radius: 0.25rem; overflow-x: auto; font-size: 0.875rem; line-height: 1.5;">${JSON.stringify(clubData, null, 2)}</pre>
                            <h5 style="font-size: 0.95rem; margin-top: 1rem; margin-bottom: 0.5rem;">Account Response:</h5>
                            <pre style="background: rgba(0, 0, 0, 0.05); padding: 0.75rem; border-radius: 0.25rem; overflow-x: auto; font-size: 0.875rem; line-height: 1.5;">${JSON.stringify(accountData, null, 2)}</pre>
                        </div>
                    </details>
                </div>
            `;

            // Clear form
            document.getElementById('createClubForm').reset();
            clearAllFieldErrors();
            document.getElementById('logoPreview').style.display = 'none';

        } catch (error) {
            responseDiv.innerHTML = `
                <div class="alert alert-danger">
                    <div style="display: flex; align-items: center; margin-bottom: 0.75rem;">
                        <i class="bi bi-x-circle me-2" style="font-size: 1.25rem;"></i>
                        <strong>Unexpected Error</strong>
                    </div>
                    <p style="margin-top: 0.5rem; margin-bottom: 0;">${error.message}</p>
                </div>
            `;
        }
    });
</script>
@endpush

@endsection
