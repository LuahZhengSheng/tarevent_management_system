@extends('layouts.club')

@section('title', 'Create New Event')

@section('content')
<div class="create-event-wrapper">
    <div class="container-fluid px-lg-5">
        <!-- Modern Header with Progress -->
        <div class="event-header mb-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('events.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="page-title mb-1">Create New Event</h1>
                        <p class="page-subtitle mb-0">Let's bring your event to life ✨</p>
                    </div>
                </div>
                <div class="header-actions d-none d-lg-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </button>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="form-progress">
                <div class="progress-steps">
                    <div class="progress-step active" data-section="basic">
                        <div class="step-circle">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <span class="step-label">Basic Info</span>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-section="datetime">
                        <div class="step-circle">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <span class="step-label">Date & Time</span>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-section="location">
                        <div class="step-circle">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <span class="step-label">Location</span>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-section="registration">
                        <div class="step-circle">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <span class="step-label">Registration</span>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-section="media">
                        <div class="step-circle">
                            <i class="bi bi-image"></i>
                        </div>
                        <span class="step-label">Media</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container"></div>

        <!-- Main Form -->
        <form id="eventForm" action="{{ route('events.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="row g-4">
                <!-- Left Column - Form Fields -->
                <div class="col-lg-8">

                    <!-- Basic Information Section -->
                    <div class="form-section" id="section-basic">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Basic Information</h3>
                                <p class="section-subtitle">Tell us about your event</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <!-- Event Title -->
                            <div class="form-group-modern">
                                <label for="title" class="modern-label required">
                                    <i class="bi bi-type me-2"></i>Event Title
                                </label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       id="title" 
                                       name="title" 
                                       placeholder="e.g., Tech Innovation Summit 2024"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="row g-3 two-column">
                                <!-- Category -->
                                <div class="col-md-6">
                                    <label for="category" class="modern-label required">
                                        <i class="bi bi-bookmark me-2"></i>Category
                                    </label>
                                    <select class="form-select-modern" id="category" name="category" required>
                                        <option value="">Select a category</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Visibility -->
                                <div class="col-md-6">
                                    <label for="is_public" class="modern-label required">
                                        <i class="bi bi-eye me-2"></i>Visibility
                                    </label>
                                    <select class="form-select-modern" id="is_public" name="is_public" required>
                                        <option value="1" selected>🌍 Public - All Students</option>
                                        <option value="0">🔒 Private - Club Members Only</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group-modern">
                                <label for="description" class="modern-label required">
                                    <i class="bi bi-text-paragraph me-2"></i>Event Description
                                </label>
                                <textarea class="form-control-modern" 
                                          id="description" 
                                          name="description" 
                                          rows="6" 
                                          placeholder="Describe your event in detail. What makes it special? What will attendees experience?"
                                          required></textarea>
                                <div class="char-counter">
                                    <span id="char-count">0</span> / 500 characters (min. 20)
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Tags -->
                            <div class="form-group-modern">
                                <label for="tags-input" class="modern-label">
                                    <i class="bi bi-tags me-2"></i>Tags
                                </label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       id="tags-input" 
                                       placeholder="Type and press Enter to add tags">
                                <div id="tags-container" class="tags-display mt-3"></div>
                                <small class="form-text">Add relevant keywords to help students discover your event</small>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="form-section" id="section-datetime">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Date & Time</h3>
                                <p class="section-subtitle">When will your event take place?</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <div class="datetime-grid">
                                <!-- Event Start -->
                                <div class="datetime-card">
                                    <div class="datetime-header">
                                        <i class="bi bi-play-circle text-success"></i>
                                        <span>Event Starts</span>
                                    </div>
                                    <input type="datetime-local" 
                                           class="form-control-modern" 
                                           id="start_time" 
                                           name="start_time" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Event End -->
                                <div class="datetime-card">
                                    <div class="datetime-header">
                                        <i class="bi bi-stop-circle text-danger"></i>
                                        <span>Event Ends</span>
                                    </div>
                                    <input type="datetime-local" 
                                           class="form-control-modern" 
                                           id="end_time" 
                                           name="end_time" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Registration Opens -->
                                <div class="datetime-card">
                                    <div class="datetime-header">
                                        <i class="bi bi-door-open text-primary"></i>
                                        <span>Registration Opens</span>
                                    </div>
                                    <input type="datetime-local" 
                                           class="form-control-modern" 
                                           id="registration_start_time" 
                                           name="registration_start_time" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Registration Closes -->
                                <div class="datetime-card">
                                    <div class="datetime-header">
                                        <i class="bi bi-door-closed text-warning"></i>
                                        <span>Registration Closes</span>
                                    </div>
                                    <input type="datetime-local" 
                                           class="form-control-modern" 
                                           id="registration_end_time" 
                                           name="registration_end_time" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="form-section" id="section-location">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Location</h3>
                                <p class="section-subtitle">Where will it happen?</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <!-- Venue -->
                            <div class="form-group-modern">
                                <label for="venue" class="modern-label required">
                                    <i class="bi bi-building me-2"></i>Venue
                                </label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       id="venue" 
                                       name="venue" 
                                       placeholder="e.g., Main Auditorium, Block A, Room 301"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Map URL -->
                            <div class="form-group-modern">
                                <label for="location_map_url" class="modern-label">
                                    <i class="bi bi-map me-2"></i>Google Maps Link
                                </label>
                                <div class="input-with-icon">
                                    <i class="bi bi-link-45deg input-icon"></i>
                                    <input type="url" 
                                           class="form-control-modern ps-5" 
                                           id="location_map_url" 
                                           name="location_map_url" 
                                           placeholder="https://maps.google.com/...">
                                </div>
                                <small class="form-text">Optional: Help attendees find the venue</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration & Payment Section -->
                    <div class="form-section" id="section-registration">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Registration & Payment</h3>
                                <p class="section-subtitle">Set up registration details</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <!-- Event Type Toggle -->
                            <div class="toggle-card-group mb-4">
                                <div class="toggle-card">
                                    <input type="radio" 
                                           name="is_paid" 
                                           id="is_paid_free" 
                                           value="0" 
                                           checked 
                                           class="toggle-radio">
                                    <label for="is_paid_free" class="toggle-label">
                                        <div class="toggle-icon">🎉</div>
                                        <div class="toggle-content">
                                            <strong>Free Event</strong>
                                            <small>No registration fee required</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="toggle-card">
                                    <input type="radio" 
                                           name="is_paid" 
                                           id="is_paid_paid" 
                                           value="1" 
                                           class="toggle-radio">
                                    <label for="is_paid_paid" class="toggle-label">
                                        <div class="toggle-icon">💳</div>
                                        <div class="toggle-content">
                                            <strong>Paid Event</strong>
                                            <small>Charge a registration fee</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Fee Amount (Hidden by default) -->
                            <div id="fee_amount_container" class="fee-container" style="display: none;">
                                <label for="fee_amount" class="modern-label required">
                                    <i class="bi bi-cash-coin me-2"></i>Registration Fee
                                </label>
                                <div class="input-with-currency">
                                    <span class="currency-symbol">RM</span>
                                    <input type="number" 
                                           class="form-control-modern ps-5" 
                                           id="fee_amount" 
                                           name="fee_amount" 
                                           placeholder="0.00" 
                                           step="0.01"
                                           min="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="row g-3 two-column">
                                <!-- Max Participants -->
                                <div class="col-md-6">
                                    <label for="max_participants" class="modern-label">
                                        <i class="bi bi-people me-2"></i>Maximum Participants
                                    </label>
                                    <input type="number" 
                                           class="form-control-modern" 
                                           id="max_participants" 
                                           name="max_participants" 
                                           placeholder="Unlimited"
                                           min="1">
                                    <small class="form-text">Leave empty for unlimited capacity</small>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Refund Policy -->
                                <div class="col-md-6">
                                    <label class="modern-label">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Refund Policy
                                    </label>
                                    <div class="switch-container">
                                        <label class="switch-modern">
                                            <input type="checkbox" 
                                                   id="refund_available" 
                                                   name="refund_available" 
                                                   value="1">
                                            <span class="switch-slider"></span>
                                        </label>
                                        <span class="switch-label">Allow cancellation refunds</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row g-3 mt-3 two-column">
                                <div class="col-md-6">
                                    <label for="contact_email" class="modern-label required">
                                        <i class="bi bi-envelope me-2"></i>Contact Email
                                    </label>
                                    <input type="email" 
                                           class="form-control-modern" 
                                           id="contact_email" 
                                           name="contact_email" 
                                           placeholder="events@tarc.edu.my"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_phone" class="modern-label">
                                        <i class="bi bi-telephone me-2"></i>Contact Phone
                                    </label>
                                    <input type="tel" 
                                           class="form-control-modern" 
                                           id="contact_phone" 
                                           name="contact_phone" 
                                           placeholder="012-3456789">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form Configuration -->
                    <div class="form-section" id="section-registration-config">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-sliders"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Registration Form Settings</h3>
                                <p class="section-subtitle">Customize what information you need from participants</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <!-- Standard Field Requirements -->
                            <div class="form-group-modern">
                                <label class="modern-label">
                                    <i class="bi bi-list-check me-2"></i>Required Information
                                </label>

                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" id="require_emergency_contact" name="require_emergency_contact" value="1" checked>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Require Emergency Contact</span>
                                </div>

                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" id="require_dietary_info" name="require_dietary_info" value="1">
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Require Dietary Requirements</span>
                                </div>

                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" id="require_special_requirements" name="require_special_requirements" value="1">
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Require Special Requirements/Accommodations</span>
                                </div>
                            </div>

                            <!-- Cancellation Policy -->
                            <div class="form-group-modern">
                                <label class="modern-label">
                                    <i class="bi bi-x-circle me-2"></i>Cancellation Policy
                                </label>
                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" id="allow_cancellation" name="allow_cancellation" value="1" checked>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Allow participants to cancel their registration</span>
                                </div>
                                <small class="form-text">If disabled, users cannot cancel once registered</small>
                            </div>

                            <!-- Custom Registration Instructions -->
                            <div class="form-group-modern">
                                <label for="registration_instructions" class="modern-label">
                                    <i class="bi bi-info-square me-2"></i>Registration Instructions
                                </label>
                                <textarea class="form-control-modern" 
                                          id="registration_instructions" 
                                          name="registration_instructions" 
                                          rows="4" 
                                          placeholder="Add any special instructions or requirements for registrants..."></textarea>
                                <small class="form-text">This will be displayed at the top of the registration form</small>
                            </div>

                            <!-- Custom Fields Builder -->
                            <div class="form-group-modern">
                                <label class="modern-label">
                                    <i class="bi bi-plus-square me-2"></i>Custom Registration Fields
                                </label>
                                <div class="custom-fields-builder">
                                    <div id="customFieldsList" class="custom-fields-list"></div>
                                    <button type="button" class="btn-add-field" id="addCustomFieldBtn">
                                        <i class="bi bi-plus-circle me-2"></i>Add Custom Field
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Section -->
                    <div class="form-section" id="section-media">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-image"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Event Poster</h3>
                                <p class="section-subtitle">Make it visually appealing</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <!-- Image Upload -->
                            <div class="upload-area">
                                <label for="poster" id="uploadArea" class="upload-label">
                                    <input type="file"
                                           id="poster"
                                           name="poster"
                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                           class="d-none">

                                    <div class="upload-placeholder" id="uploadPlaceholder">
                                        <div class="upload-icon">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                        </div>
                                        <h4>Drop your poster here</h4>
                                        <p>or <span class="text-primary">click to browse</span></p>
                                        <small class="text-muted">PNG, JPG, WEBP up to 5MB • Min. 1280x720px recommended</small>
                                    </div>

                                    <div id="poster-preview" class="poster-preview-inline" style="display:none; margin-top:0.75rem; position:relative;">
                                        <img id="poster-preview-img"
                                             src=""
                                             alt="Poster preview"
                                             class="poster-preview-img">
                                        <button type="button" class="btn btn-sm btn-light poster-preview-remove" id="posterInlineRemove">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                </label>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                </div>

                <!-- Right Column - Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar-sticky">

                        <!-- Preview Card -->
                        <div class="preview-card">
                            <div class="preview-header">
                                <i class="bi bi-eye me-2"></i>
                                <span>Live Preview</span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-poster" id="previewPoster">
                                    <div class="preview-poster-placeholder">
                                        <i class="bi bi-image"></i>
                                        <span>No poster yet</span>
                                    </div>
                                </div>
                                <div class="preview-details">
                                    <h4 id="previewTitle" class="preview-title">Event Title</h4>
                                    <div class="preview-meta">
                                        <div class="preview-meta-item">
                                            <i class="bi bi-calendar-event"></i>
                                            <span id="previewDate">Date not set</span>
                                        </div>
                                        <div class="preview-meta-item">
                                            <i class="bi bi-geo-alt"></i>
                                            <span id="previewVenue">Venue not set</span>
                                        </div>
                                        <div class="preview-meta-item">
                                            <i class="bi bi-bookmark"></i>
                                            <span id="previewCategory">Category</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class="tips-card">
                            <div class="tips-header">
                                <i class="bi bi-lightbulb me-2"></i>
                                <span>Quick Tips</span>
                            </div>
                            <ul class="tips-list">
                                <li>Use a high-quality, eye-catching poster</li>
                                <li>Write a clear, detailed description</li>
                                <li>Set realistic registration deadlines</li>
                                <li>Double-check date and time</li>
                                <li>Add relevant tags for discoverability</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-card">
                            <button type="submit" 
                                    name="status" 
                                    value="draft" 
                                    class="btn-action btn-action-secondary w-100 mb-3" 
                                    id="saveDraftBtn">
                                <i class="bi bi-save me-2"></i>
                                Save as Draft
                            </button>
                            <button type="submit" 
                                    name="status" 
                                    value="published" 
                                    class="btn-action btn-action-primary w-100" 
                                    id="publishBtn">
                                <i class="bi bi-send me-2"></i>
                                Publish Event
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
@vite('resources/css/events/event-form-modern.css')
@endpush

@push('scripts')
<script>
    window.eventsIndexUrl = "{{ route('events.index') }}";
</script>
@vite('resources/js/validation/phone-validator.js')
@vite('resources/js/events/event-form-preview.js')
@vite('resources/js/events/event-form-validation.js')
@endpush

@endsection