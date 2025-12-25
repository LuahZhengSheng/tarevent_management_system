@extends('layouts.club')

@section('title', 'Edit Club Profile - TAREvent')

@section('content')
<div class="club-profile-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span>Club Profile</span>
                    </div>
                    <h1 class="page-title">Edit Club Profile</h1>
                    <p class="page-description">Update your club information and settings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-form-card">
                    <form action="{{ route('club.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                        @csrf
                        @method('PUT')

                        <!-- Club Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                <i class="bi bi-building me-2"></i>Club Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $club->name) }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter club name">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="bi bi-text-paragraph me-2"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="5" 
                                      placeholder="Enter club description">{{ old('description', $club->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label">
                                <i class="bi bi-tag me-2"></i>Category
                            </label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                <option value="">Select Category</option>
                                <option value="academic" {{ old('category', $club->category) === 'academic' ? 'selected' : '' }}>Academic</option>
                                <option value="sports" {{ old('category', $club->category) === 'sports' ? 'selected' : '' }}>Sports</option>
                                <option value="cultural" {{ old('category', $club->category) === 'cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="social" {{ old('category', $club->category) === 'social' ? 'selected' : '' }}>Social</option>
                                <option value="volunteer" {{ old('category', $club->category) === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                                <option value="professional" {{ old('category', $club->category) === 'professional' ? 'selected' : '' }}>Professional</option>
                                <option value="other" {{ old('category', $club->category) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $club->email) }}" 
                                   maxlength="255"
                                   placeholder="Enter club email">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-2"></i>Phone
                            </label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $club->phone) }}" 
                                   maxlength="50"
                                   placeholder="Enter club phone number">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Logo -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-image me-2"></i>Logo
                            </label>
                            @if($club->logo)
                            <div class="current-image-container mb-3">
                                <img src="{{ asset('storage/' . $club->logo) }}" alt="Current logo" class="current-image">
                                <div class="image-actions mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="removeLogoBtn">
                                        <i class="bi bi-trash me-1"></i>Remove Logo
                                    </button>
                                </div>
                                <input type="hidden" name="remove_logo" id="removeLogo" value="0">
                            </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="logoPreview" class="mt-3" style="display: none;">
                                <img id="previewLogoImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            </div>
                        </div>

                        <!-- Background Image -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-image me-2"></i>Background Image
                            </label>
                            @if($club->background_image)
                            <div class="current-image-container mb-3">
                                <img src="{{ asset('storage/' . $club->background_image) }}" alt="Current background" class="current-image">
                                <div class="image-actions mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="removeBackgroundBtn">
                                        <i class="bi bi-trash me-1"></i>Remove Background
                                    </button>
                                </div>
                                <input type="hidden" name="remove_background_image" id="removeBackground" value="0">
                            </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('background_image') is-invalid @enderror" 
                                   id="background_image" 
                                   name="background_image" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                            @error('background_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="backgroundPreview" class="mt-3" style="display: none;">
                                <img id="previewBackgroundImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('club.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.club-profile-edit-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.profile-form-card {
    background: var(--bg-primary);
    border-radius: 0.75rem;
    box-shadow: var(--shadow-sm);
    padding: 2rem;
}

.form-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 1px solid var(--border-color);
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

.form-text {
    color: var(--text-tertiary);
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.current-image-container {
    margin-bottom: 1rem;
}

.current-image {
    max-width: 200px;
    max-height: 200px;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
}

#logoPreview, #backgroundPreview {
    margin-top: 1rem;
}

#previewLogoImg, #previewBackgroundImg {
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Remove logo button
    $('#removeLogoBtn').on('click', function() {
        if (confirm('Are you sure you want to remove the logo?')) {
            $('#removeLogo').val('1');
            $('.current-image-container').first().hide();
        }
    });

    // Remove background button
    $('#removeBackgroundBtn').on('click', function() {
        if (confirm('Are you sure you want to remove the background image?')) {
            $('#removeBackground').val('1');
            $('.current-image-container').last().hide();
        }
    });

    // Logo preview
    $('#logo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewLogoImg').attr('src', e.target.result);
                $('#logoPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#logoPreview').hide();
        }
    });

    // Background preview
    $('#background_image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewBackgroundImg').attr('src', e.target.result);
                $('#backgroundPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#backgroundPreview').hide();
        }
    });

    // Form validation
    $('#profileForm').on('submit', function(e) {
        const name = $('#name').val().trim();

        if (!name) {
            e.preventDefault();
            alert('Please enter a club name.');
            $('#name').focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection

