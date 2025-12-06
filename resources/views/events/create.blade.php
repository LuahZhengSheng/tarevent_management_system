@extends('layouts.admin')

@section('title', 'Create New Event')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('events.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                    <h2 class="mb-1">Create New Event</h2>
                    <p class="text-muted mb-0">Fill in the details to create a new campus event</p>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alert-container"></div>

            <!-- Event Creation Form -->
            <form id="eventForm" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Event Title -->
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label required">Event Title</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="title" 
                                       name="title" 
                                       placeholder="Enter event title"
                                       required>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback">Looks good!</div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label required">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Visibility -->
                            <div class="col-md-6 mb-3">
                                <label for="is_public" class="form-label required">Event Visibility</label>
                                <select class="form-select" id="is_public" name="is_public" required>
                                    <option value="1" selected>Public (All Students)</option>
                                    <option value="0">Private (Club Members Only)</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label required">Event Description</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="5" 
                                          placeholder="Describe your event in detail..."
                                          required></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Minimum 20 characters</small>
                            </div>

                            <!-- Tags -->
                            <div class="col-md-12 mb-3">
                                <label for="tags-input" class="form-label">Tags</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="tags-input" 
                                       placeholder="Type and press Enter to add tags">
                                <div id="tags-container" class="mt-2"></div>
                                <small class="text-muted">Add relevant tags to help students find your event</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Date & Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Start Time -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label required">Event Start Date & Time</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="start_time" 
                                       name="start_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- End Time -->
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label required">Event End Date & Time</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="end_time" 
                                       name="end_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Registration Start -->
                            <div class="col-md-6 mb-3">
                                <label for="registration_start_time" class="form-label required">Registration Opens</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="registration_start_time" 
                                       name="registration_start_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Registration End -->
                            <div class="col-md-6 mb-3">
                                <label for="registration_end_time" class="form-label required">Registration Closes</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="registration_end_time" 
                                       name="registration_end_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Venue -->
                            <div class="col-md-12 mb-3">
                                <label for="venue" class="form-label required">Venue</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="venue" 
                                       name="venue" 
                                       placeholder="e.g., Main Hall, Sports Complex, Room A101"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Map URL -->
                            <div class="col-md-12 mb-3">
                                <label for="location_map_url" class="form-label">Google Maps Link (Optional)</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="location_map_url" 
                                       name="location_map_url" 
                                       placeholder="https://maps.google.com/...">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Registration & Payment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Is Paid -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Event Type</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="is_paid" 
                                               id="is_paid_free" 
                                               value="0" 
                                               checked>
                                        <label class="form-check-label" for="is_paid_free">
                                            Free Event
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="is_paid" 
                                               id="is_paid_paid" 
                                               value="1">
                                        <label class="form-check-label" for="is_paid_paid">
                                            Paid Event
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Amount -->
                            <div class="col-md-6 mb-3" id="fee_amount_container" style="display: none;">
                                <label for="fee_amount" class="form-label">Fee Amount (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="fee_amount" 
                                           name="fee_amount" 
                                           placeholder="0.00" 
                                           step="0.01"
                                           min="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Max Participants -->
                            <div class="col-md-6 mb-3">
                                <label for="max_participants" class="form-label">Maximum Participants</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="max_participants" 
                                       name="max_participants" 
                                       placeholder="Leave empty for unlimited"
                                       min="1">
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Leave empty for unlimited capacity</small>
                            </div>

                            <!-- Refund Available -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Refund Policy</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="refund_available" 
                                           name="refund_available" 
                                           value="1">
                                    <label class="form-check-label" for="refund_available">
                                        Allow refunds for cancellations
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-telephone me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Contact Email -->
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label required">Contact Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="contact_email" 
                                       name="contact_email" 
                                       placeholder="event@tarc.edu.my"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Contact Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="contact_phone" 
                                       name="contact_phone" 
                                       placeholder="012-3456789">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Event Poster</h5>
                    </div>
                    <div class="card-body">
                        <!-- Poster Upload -->
                        <div class="mb-3">
                            <label for="poster" class="form-label">Upload Event Poster</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="poster" 
                                   name="poster" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Accepted formats: JPEG, PNG, JPG, WEBP (Max: 5MB)</small>
                        </div>

                        <!-- Poster Preview -->
                        <div id="poster-preview" class="mt-3" style="display: none;">
                            <p class="mb-2"><strong>Preview:</strong></p>
                            <img id="poster-preview-img" src="" alt="Poster Preview" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                            <div>
                                <button type="submit" name="status" value="draft" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                                    <i class="bi bi-save me-2"></i>Save as Draft
                                </button>
                                <button type="submit" name="status" value="published" class="btn btn-primary" id="publishBtn">
                                    <i class="bi bi-send me-2"></i>Publish Event
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
}

.card {
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg) !important;
}

.tag-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    margin: 0.25rem;
    background-color: var(--primary-light);
    color: var(--primary);
    border-radius: 1rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tag-badge:hover {
    background-color: var(--primary);
    color: white;
}

.tag-badge i {
    margin-left: 0.5rem;
    cursor: pointer;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem var(--primary-light);
}

.is-invalid {
    border-color: var(--error) !important;
}

.is-valid {
    border-color: var(--success) !important;
}

.btn-primary, .btn-secondary {
    transition: all 0.3s ease;
}

.btn-primary:hover, .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}
</style>

@push('scripts')
@vite('resources/js/events/event-form-validation.js')
@endpush
@endsection