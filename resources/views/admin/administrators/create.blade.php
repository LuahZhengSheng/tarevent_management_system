<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'Add Administrator')

@section('content')
<div class="admin-admin-create-page">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.administrators.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Administrators
        </a>
    </div>

    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <h1 class="admin-page-title">Add New Administrator</h1>
        <p class="admin-page-subtitle">Create a new administrator account</p>
    </div>

    <!-- Create Form -->
    <div class="admin-edit-card">
        <form method="POST" action="{{ route('admin.administrators.store') }}">
            @csrf

            <div class="row g-4">
                <!-- Basic Information -->
                <div class="col-lg-8">
                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control-modern @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control-modern @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                class="form-control-modern @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                required
                                placeholder="e.g., +60 12-345 6789"
                            >
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="col-lg-4">
                    <div class="form-section">
                        <h3 class="form-section-title">Account Settings</h3>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select 
                                id="status" 
                                name="status" 
                                class="form-control-modern @error('status') is-invalid @enderror"
                                required
                            >
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="info-box">
                            <div class="info-box-title">
                                <i class="bi bi-info-circle me-2"></i>Password Information
                            </div>
                            <div class="info-box-content">
                                <p style="margin: 0; font-size: 0.8125rem; color: var(--text-secondary);">
                                    A random password will be generated automatically and sent to the administrator's email address upon account creation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.administrators.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Create Administrator</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .admin-admin-create-page {
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

    .admin-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .admin-page-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
        margin: 0.5rem 0 0 0;
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

    .form-control-modern,
    select.form-control-modern {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }

    .form-control-modern:focus,
    select.form-control-modern:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .form-control-modern.is-invalid,
    select.form-control-modern.is-invalid {
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

    .info-box-content {
        font-size: 0.8125rem;
        color: var(--text-secondary);
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
</style>
@endpush

@endsection

