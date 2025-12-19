@extends('layouts.app')

@section('title', 'Register - TAREvent')

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
        max-width: 900px;
    }

    .auth-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        padding: 3rem 2.5rem;
        transition: all 0.3s ease;
    }

    .auth-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .auth-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .auth-form-row {
            grid-template-columns: 1fr;
        }
        .auth-container {
            max-width: 100%;
        }
        .auth-card {
            padding: 2rem 1.5rem;
        }
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2.5rem;
        position: relative;
        padding-bottom: 1.5rem;
    }

    .auth-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 2px;
    }

    .auth-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .auth-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .auth-form-group {
        margin-bottom: 1.5rem;
    }

    .auth-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .auth-label-optional {
        font-weight: 400;
        color: var(--text-secondary);
        font-size: 0.8125rem;
    }

    .auth-input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        background: var(--bg-primary);
        border: 1.5px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        transition: all 0.3s ease;
    }

    .auth-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
        background: var(--bg-primary);
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
        width: 1.75rem;
        height: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: help;
        color: var(--text-tertiary);
        transition: all 0.3s ease;
        z-index: 1;
        background: var(--bg-secondary);
        border-radius: 50%;
    }

    .password-hint-trigger:hover {
        color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-50%) scale(1.1);
    }

    .password-hint-trigger i {
        font-size: 1rem;
    }

    .password-hint-tooltip {
        position: absolute;
        bottom: calc(100% + 0.75rem);
        right: 0;
        width: 300px;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-xl);
        padding: 1.25rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(0.5rem);
        transition: all 0.3s ease;
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
        line-height: 1.6;
    }

    .tooltip-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        color: var(--primary);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .tooltip-content ul {
        margin: 0;
        padding-left: 1.5rem;
        list-style: none;
    }

    .tooltip-content ul li {
        position: relative;
        padding-left: 0.5rem;
        margin-bottom: 0.5rem;
        color: var(--text-secondary);
    }

    .tooltip-content ul li:before {
        content: '✓';
        position: absolute;
        left: -1.25rem;
        color: var(--success);
        font-weight: bold;
    }

    .tooltip-content ul li:last-child {
        margin-bottom: 0;
    }

    .password-hint-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        right: 1.5rem;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid var(--bg-primary);
    }

    .password-hint-tooltip::before {
        content: '';
        position: absolute;
        top: 100%;
        right: 1.5rem;
        width: 0;
        height: 0;
        border-left: 9px solid transparent;
        border-right: 9px solid transparent;
        border-top: 9px solid var(--border-color);
        margin-top: 1px;
    }

    .auth-button {
        width: 100%;
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 600;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        box-shadow: var(--shadow-sm);
    }

    .auth-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .auth-button:active {
        transform: translateY(0);
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .auth-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        margin-left: 0.25rem;
        transition: color 0.3s ease;
    }

    .auth-footer a:hover {
        color: var(--primary-hover);
        text-decoration: underline;
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
        border: 1px solid var(--error);
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
        border: 1px solid var(--error);
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
        border-color: var(--error) !important;
    }

    .is-invalid:focus {
        border-color: var(--error) !important;
        box-shadow: 0 0 0 3px var(--error-light) !important;
    }

    /* Avatar Section */
    .register-avatar-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .register-avatar-container {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 1.5rem;
        background: var(--bg-secondary);
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .register-avatar-container:hover {
        border-color: var(--primary);
    }

    .register-avatar-wrapper {
        position: relative;
        width: 96px;
        height: 96px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border-color);
        background: var(--bg-primary);
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .register-avatar-wrapper:hover {
        border-color: var(--primary);
        transform: scale(1.05);
    }

    .register-avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .register-avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        opacity: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .register-avatar-wrapper:hover .register-avatar-overlay {
        opacity: 1;
    }

    .register-avatar-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .register-avatar-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .register-avatar-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .register-avatar-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        border: 1.5px solid var(--primary);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--primary);
        background: var(--bg-primary);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .register-avatar-upload-btn:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .register-avatar-upload-btn i {
        font-size: 1rem;
    }

    .register-avatar-input {
        display: none;
    }

    .register-avatar-hint {
        margin: 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .register-avatar-hint i {
        color: var(--text-tertiary);
    }

    @media (max-width: 640px) {
        .register-avatar-container {
            flex-direction: column;
            text-align: center;
        }

        .register-avatar-actions {
            justify-content: center;
        }
    }

    .auth-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .auth-select:focus {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232563eb' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    }

    /* Form Section Headers */
    .form-section-header {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-header i {
        color: var(--primary);
    }
</style>
@endpush

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome to TAREvent</h1>
                <p class="auth-subtitle">Create your account to get started</p>
            </div>

            <form method="POST" action="{{ route('register') }}" novalidate enctype="multipart/form-data">
                @csrf

                <!-- Avatar Upload -->
                <div class="register-avatar-section">
                    <label class="auth-label">
                        Profile Avatar 
                        <span class="auth-label-optional">(Optional)</span>
                    </label>
                    <div class="register-avatar-container">
                        <label for="avatar" class="register-avatar-wrapper">
                            <img
                                src="{{ asset('images/avatar/default-student-avatar.png') }}"
                                alt="Avatar"
                                class="register-avatar-image"
                                id="avatarPreview"
                            >
                            <div class="register-avatar-overlay">
                                <i class="bi bi-camera"></i>
                            </div>
                        </label>
                        
                        <div class="register-avatar-content">
                            <h4 class="register-avatar-title">Upload your profile picture</h4>
                            <div class="register-avatar-actions">
                                <label for="avatar" class="register-avatar-upload-btn">
                                    <i class="bi bi-upload"></i>
                                    <span>Choose Photo</span>
                                </label>
                                <p class="register-avatar-hint">
                                    <i class="bi bi-info-circle"></i>
                                    <span>JPG or PNG, max 2MB</span>
                                </p>
                            </div>
                        </div>
                        
                        <input
                            id="avatar"
                            name="avatar"
                            type="file"
                            accept="image/*"
                            class="register-avatar-input @error('avatar') is-invalid @enderror"
                        >
                    </div>
                    @error('avatar')
                        <div class="invalid-feedback" style="margin-top: 0.75rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Personal Information -->
                <h3 class="form-section-header">
                    <i class="bi bi-person-circle"></i>
                    Personal Information
                </h3>

                <div class="auth-form-row">
                    <div class="auth-form-group">
                        <label for="name" class="auth-label">Full Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="auth-input @error('name') is-invalid @enderror"
                            placeholder="Enter your full name"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-form-group">
                        <label for="student_id" class="auth-label">Student ID</label>
                        <input
                            id="student_id"
                            type="text"
                            name="student_id"
                            value="{{ old('student_id') }}"
                            required
                            autocomplete="off"
                            class="auth-input @error('student_id') is-invalid @enderror"
                            placeholder="e.g., 22WMR12345"
                        >
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        class="auth-input @error('email') is-invalid @enderror"
                        placeholder="your.email@student.tarc.edu.my"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Security Information -->
                <h3 class="form-section-header">
                    <i class="bi bi-shield-lock"></i>
                    Security Information
                </h3>

                <div class="auth-form-row">
                    <div class="auth-form-group">
                        <label for="password" class="auth-label">Password</label>
                        <div class="password-input-wrapper">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="auth-input @error('password') is-invalid @enderror"
                                placeholder="Create a strong password"
                            >
                            <div class="password-hint-trigger">
                                <i class="bi bi-question-circle"></i>
                                <div class="password-hint-tooltip">
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">
                                            <i class="bi bi-shield-check"></i>
                                            Password Requirements
                                        </div>
                                        <ul>
                                            <li>At least 8 characters</li>
                                            <li>One uppercase letter (A-Z)</li>
                                            <li>One lowercase letter (a-z)</li>
                                            <li>One number (0-9)</li>
                                            <li>One symbol (!@#$%^&*)</li>
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
                            placeholder="Re-enter your password"
                        >
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <h3 class="form-section-header">
                    <i class="bi bi-info-circle"></i>
                    Additional Information
                </h3>

                <div class="auth-form-row">
                    <div class="auth-form-group">
                        <label for="phone" class="auth-label">Phone Number</label>
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            required
                            autocomplete="tel"
                            class="auth-input @error('phone') is-invalid @enderror"
                            placeholder="+60 12-345 6789"
                        >
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-form-group">
                        <label for="program" class="auth-label">Program</label>
                        <select
                            id="program"
                            name="program"
                            required
                            class="auth-input auth-select @error('program') is-invalid @enderror"
                        >
                            <option value="">Select your program</option>
                            @foreach($programOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('program') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('program')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="auth-button">
                    <i class="bi bi-person-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account?
                <a href="{{ route('login') }}">Sign in here</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const defaultAvatar = avatarPreview.src;

        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        avatarInput.value = '';
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        avatarInput.value = '';
                        return;
                    }

                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Reset to default if no file selected
                    avatarPreview.src = defaultAvatar;
                }
            });
        }
    });
</script>
@endpush
@endsection