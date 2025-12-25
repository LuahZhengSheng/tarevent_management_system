@extends('layouts.club')

@section('title', 'Create Announcement - TAREvent')

@section('content')
<div class="club-announcement-create-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-custom">
                        <a href="{{ route('club.dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <a href="{{ route('club.announcements.index') }}">Announcements</a>
                        <span>/</span>
                        <span>Create</span>
                    </div>
                    <h1 class="page-title">Create Announcement</h1>
                    <p class="page-description">Create a new announcement for your club members</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="announcement-form-card">
                    <form action="{{ route('club.announcements.store') }}" method="POST" enctype="multipart/form-data" id="announcementForm">
                        @csrf

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">
                                <i class="bi bi-type me-2"></i>Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter announcement title">
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="bi bi-text-paragraph me-2"></i>Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="10" 
                                      required
                                      placeholder="Enter announcement content">{{ old('content') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image -->
                        <div class="mb-4">
                            <label for="image" class="form-label">
                                <i class="bi bi-image me-2"></i>Image (Optional)
                            </label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="form-label">
                                <i class="bi bi-eye me-2"></i>Status
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            <div class="form-text">Draft announcements are saved but not visible to members. Published announcements are immediately visible.</div>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('club.announcements.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Create Announcement
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
.club-announcement-create-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding-bottom: 4rem;
}

.announcement-form-card {
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

#imagePreview {
    margin-top: 1rem;
}

#previewImg {
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide();
        }
    });

    // Form validation
    $('#announcementForm').on('submit', function(e) {
        const title = $('#title').val().trim();
        const content = $('#content').val().trim();

        if (!title) {
            e.preventDefault();
            alert('Please enter a title.');
            $('#title').focus();
            return false;
        }

        if (!content) {
            e.preventDefault();
            alert('Please enter content.');
            $('#content').focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection

