<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="admin-user-edit-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.users.show', $user) }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to User Details
        </a>
    </div>

    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <h1 class="admin-page-title">Edit User</h1>
        <p class="admin-page-subtitle">Update user information</p>
    </div>

    <!-- Edit Form -->
    <div class="admin-edit-card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Basic Information -->
                <div class="col-lg-8">
                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>
                        
                        <!-- Avatar Upload -->
                        <div class="mb-4">
                            <label class="form-label">Profile Avatar</label>
                            <div class="admin-avatar-upload">
                                <div class="admin-avatar-preview">
                                    <label for="avatar" class="admin-avatar-wrapper">
                                        <img
                                            src="{{ $user->profile_photo_url }}"
                                            alt="Avatar"
                                            class="admin-avatar-image"
                                            id="avatarPreview"
                                            onerror="this.src='{{ asset('images/avatar/default-student-avatar.png') }}'"
                                        >
                                        <div class="admin-avatar-overlay">
                                            <i class="bi bi-camera-fill"></i>
                                        </div>
                                    </label>
                                    <label for="avatar" class="admin-avatar-upload-btn">
                                        <i class="bi bi-upload me-2"></i>
                                        <span>Change Photo</span>
                                    </label>
                                </div>
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    accept="image/*"
                                    class="admin-avatar-input @error('avatar') is-invalid @enderror"
                                >
                                <p class="admin-avatar-hint">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Recommended: Square image, JPG or PNG, max 2MB
                                </p>
                                @error('avatar')
                                <div class="invalid-feedback" style="margin-top: 0.5rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control-modern @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control-modern @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                readonly
                                style="background-color: var(--bg-secondary); cursor: not-allowed;"
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input 
                                type="text" 
                                id="student_id" 
                                name="student_id" 
                                class="form-control-modern @error('student_id') is-invalid @enderror"
                                value="{{ old('student_id', $user->student_id) }}"
                                readonly
                                style="background-color: var(--bg-secondary); cursor: not-allowed;"
                            >
                            @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input 
                                type="text" 
                                id="phone" 
                                name="phone" 
                                class="form-control-modern @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                            >
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="program" class="form-label">Program</label>
                            <select 
                                id="program" 
                                name="program" 
                                class="form-control-modern @error('program') is-invalid @enderror"
                            >
                                <option value="">-- Select Program --</option>
                                @foreach($programOptions as $code => $name)
                                <option value="{{ $code }}" {{ old('program', $user->program) === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                            @error('program')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="col-lg-4">
                    <div class="form-section">
                        <h3 class="form-section-title">Account Status</h3>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select 
                                id="status" 
                                name="status" 
                                class="form-control-modern @error('status') is-invalid @enderror"
                                required
                            >
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="info-box">
                            <div class="info-box-title">
                                <i class="bi bi-info-circle me-2"></i>Status Information
                            </div>
                            <div class="info-box-content">
                                <ul class="info-list">
                                    <li><strong>Active:</strong> User can access the system</li>
                                    <li><strong>Inactive:</strong> User account is disabled</li>
                                    <li><strong>Suspended:</strong> User account is temporarily suspended</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.users.show', $user) }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .admin-user-edit-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.9375rem;
        font-weight: 500;
    }

    .btn-back:hover {
        color: var(--primary);
        background: var(--bg-secondary);
    }

    .admin-edit-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .form-control-modern.is-invalid {
        border-color: var(--error);
    }

    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: var(--error);
    }

    .info-box {
        margin-top: 1.5rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }

    .info-box-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }

    .info-list {
        margin: 0;
        padding-left: 1.25rem;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }

    .info-list li {
        margin-bottom: 0.5rem;
    }

    .info-list li:last-child {
        margin-bottom: 0;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .btn-cancel {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-cancel:hover {
        background: var(--bg-secondary);
        border-color: var(--border-hover);
    }

    .btn-save {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        background: var(--primary);
        color: white;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-save:hover {
        background: var(--primary-hover);
    }

    .admin-avatar-upload {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .admin-avatar-preview {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .admin-avatar-wrapper {
        position: relative;
        width: 96px;
        height: 96px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border-color);
        background: var(--bg-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
    }

    .admin-avatar-wrapper:hover {
        border-color: var(--primary);
        transform: scale(1.05);
        box-shadow: 0 0 0 4px var(--primary-light);
    }

    .admin-avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .admin-avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .admin-avatar-wrapper:hover .admin-avatar-overlay {
        opacity: 1;
    }

    .admin-avatar-upload-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--text-primary);
        background: var(--bg-primary);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .admin-avatar-upload-btn:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .admin-avatar-input {
        display: none;
    }

    .admin-avatar-hint {
        margin: 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
    }

    .form-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }
</style>
@endpush

@push('scripts')
<script>
(function($) {
    'use strict';

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
})(jQuery);
</script>
@endpush

@endsection

