@extends('layouts.admin')

@section('title', 'Edit Event - ' . $event->title)

@section('content')
<div class="create-event-wrapper">
    <div class="container-fluid px-lg-5">
        <!-- Modern Header -->
        <div class="event-header mb-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('events.show', $event) }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="page-title mb-1">Edit Event</h1>
                        <p class="page-subtitle mb-0">Make changes to "{{ $event->title }}"</p>
                    </div>
                </div>
                <div class="header-actions d-none d-lg-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </button>
                </div>
            </div>

            <!-- Event Stage Indicator -->
            <div class="event-stage-indicator">
                @php
                $now = now();
                $stage = 'draft';
                $stageLabel = 'Draft';
                $stageIcon = 'file-earmark';
                $stageColor = 'secondary';

                if ($event->status === 'draft') {
                $stage = 'draft';
                $stageLabel = 'Draft';
                $stageIcon = 'file-earmark';
                $stageColor = 'secondary';
                } elseif ($event->status === 'cancelled') {
                $stage = 'cancelled';
                $stageLabel = 'Cancelled';
                $stageIcon = 'x-circle';
                $stageColor = 'danger';
                } elseif ($event->end_time < $now) {
                $stage = 'past';
                $stageLabel = 'Past Event';
                $stageIcon = 'check-circle';
                $stageColor = 'success';
                } elseif ($event->start_time <= $now && $event->end_time >= $now) {
                $stage = 'ongoing';
                $stageLabel = 'Event Ongoing';
                $stageIcon = 'play-circle';
                $stageColor = 'primary';
                } elseif ($event->registration_start_time <= $now && $event->registration_end_time >= $now) {
                $stage = 'registration';
                $stageLabel = 'Registration Open';
                $stageIcon = 'door-open';
                $stageColor = 'info';
                } elseif ($event->registration_start_time > $now) {
                $stage = 'before-registration';
                $stageLabel = 'Before Registration';
                $stageIcon = 'clock';
                $stageColor = 'warning';
                } elseif ($event->status === 'published') {
                $stage = 'published';
                $stageLabel = 'Published';
                $stageIcon = 'check2-circle';
                $stageColor = 'success';
                }
                @endphp

                <div class="stage-badge stage-{{ $stageColor }}">
                    <i class="bi bi-{{ $stageIcon }} me-2"></i>
                    <span>{{ $stageLabel }}</span>
                </div>

                @if($stage === 'registration' || $stage === 'ongoing')
                <div class="stage-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span>
                        @if($stage === 'registration')
                        Limited editing: Registration is open. Some fields are locked.
                        @else
                        Very limited editing: Event is in progress.
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container"></div>

        <!-- Main Form -->
        <form id="eventForm" 
              action="{{ route('events.update', $event) }}" 
              method="POST"
              enctype="multipart/form-data" 
              novalidate
              data-event-id="{{ $event->id }}"
              data-event-stage="{{ $stage }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="event_id" value="{{ $event->id }}">

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
                                <p class="section-subtitle">Event details</p>
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
                                       value="{{ old('title', $event->title) }}"
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
                                    <select class="form-select-modern" 
                                            id="category" 
                                            name="category" 
                                            required>
                                        <option value="">Select a category</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category', $event->category) === $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <!--                                    @if(in_array($stage, ['registration', 'ongoing', 'past']))
                                                                            <input type="hidden" name="category" value="{{ $event->category }}">
                                                                        @endif-->
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Visibility -->
                                <div class="col-md-6">
                                    <label for="is_public" class="modern-label required">
                                        <i class="bi bi-eye me-2"></i>Visibility
                                    </label>
                                    <select class="form-select-modern" 
                                            id="is_public" 
                                            name="is_public" 
                                            required
                                            {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'disabled' : '' }}>
                                        <option value="1" {{ old('is_public', $event->is_public) == 1 ? 'selected' : '' }}>
                                            🌍 Public - All Students
                                        </option>
                                        <option value="0" {{ old('is_public', $event->is_public) == 0 ? 'selected' : '' }}>
                                            🔒 Private - Club Members Only
                                        </option>
                                    </select>
                                    @if(in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']))
                                    <input type="hidden" name="is_public" value="{{ $event->is_public }}">
                                    @endif
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
                                          placeholder="Describe your event in detail"
                                          required>{{ old('description', $event->description) }}</textarea>
                                <div class="char-counter">
                                    <span id="char-count">{{ strlen(old('description', $event->description)) }}</span> / 5000 characters
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
                                <p class="section-subtitle">Event schedule</p>
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
                                           value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}"
                                           required
                                           {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                    @if(in_array($stage, ['registration', 'ongoing', 'past']))
                                    <small class="text-muted">🔒 Locked during/after registration</small>
                                    @endif
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
                                           value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}"
                                           required
                                           {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                    @if(in_array($stage, ['registration', 'ongoing', 'past']))
                                    <small class="text-muted">🔒 Locked during/after registration</small>
                                    @endif
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
                                           value="{{ old('registration_start_time', $event->registration_start_time->format('Y-m-d\TH:i')) }}"
                                           required
                                           {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                    @if(in_array($stage, ['registration', 'ongoing', 'past']))
                                    <small class="text-muted">🔒 Cannot change after registration starts</small>
                                    @endif
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
                                           value="{{ old('registration_end_time', $event->registration_end_time->format('Y-m-d\TH:i')) }}"
                                           required
                                           {{ in_array($stage, ['ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                    @if($stage === 'registration')
                                    <small class="text-info">✓ Can extend deadline during registration</small>
                                    @elseif(in_array($stage, ['ongoing', 'past']))
                                    <small class="text-muted">🔒 Locked</small>
                                    @endif
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
                                       value="{{ old('venue', $event->venue) }}"
                                       placeholder="e.g., Main Auditorium, Block A, Room 301"
                                       required
                                       {{ in_array($stage, ['ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                <div class="invalid-feedback"></div>
                                <!--                                @if($stage === 'registration')
                                                                    <small class="text-warning">⚠️ Only minor changes allowed during registration</small>
                                                                @endif-->
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
                                           value="{{ old('location_map_url', $event->location_map_url) }}"
                                           placeholder="https://maps.google.com/..."
                                           {{ in_array($stage, ['ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
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
                                <p class="section-subtitle">Registration settings</p>
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
                                           {{ old('is_paid', $event->is_paid) == 0 ? 'checked' : '' }}
                                    class="toggle-radio"
                                    {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'disabled' : '' }}>
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
                                           {{ old('is_paid', $event->is_paid) == 1 ? 'checked' : '' }}
                                    class="toggle-radio"
                                    {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'disabled' : '' }}>
                                    <label for="is_paid_paid" class="toggle-label">
                                        <div class="toggle-icon">💳</div>
                                        <div class="toggle-content">
                                            <strong>Paid Event</strong>
                                            <small>Charge a registration fee</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            @if(in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']))
                            <input type="hidden" name="is_paid" value="{{ $event->is_paid }}">
                            <div class="alert alert-info">
                                <i class="bi bi-lock me-2"></i>
                                Payment settings are locked after registration starts.
                            </div>
                            @endif

                            <!-- Fee Amount -->
                            <div id="fee_amount_container" class="fee-container" style="display: {{ old('is_paid', $event->is_paid) ? 'block' : 'none' }};">
                                <label for="fee_amount" class="modern-label required">
                                    <i class="bi bi-cash-coin me-2"></i>Registration Fee
                                </label>
                                <div class="input-with-currency">
                                    <span class="currency-symbol">RM</span>
                                    <input type="number" 
                                           class="form-control-modern ps-5" 
                                           id="fee_amount" 
                                           name="fee_amount" 
                                           value="{{ old('fee_amount', $event->fee_amount) }}"
                                           placeholder="0.00" 
                                           step="0.01"
                                           min="0"
                                           {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="row g-3 two-column mt-3">
                                <!-- Max Participants -->
                                <div class="col-md-6">
                                    <label for="max_participants" class="modern-label">
                                        <i class="bi bi-people me-2"></i>Maximum Participants
                                    </label>
                                    <input type="number" 
                                           class="form-control-modern" 
                                           id="max_participants" 
                                           name="max_participants" 
                                           value="{{ old('max_participants', $event->max_participants) }}"
                                           placeholder="Unlimited"
                                           min="{{ $event->registrations()->where('status', 'confirmed')->count() }}"
                                           {{ in_array($stage, ['ongoing', 'past', 'cancelled']) ? 'readonly' : '' }}>
                                    <small class="form-text">
                                        @if($stage === 'registration')
                                        Current: {{ $event->registrations()->where('status', 'confirmed')->count() }} registered
                                        (Can only increase)
                                        @else
                                        Leave empty for unlimited capacity
                                        @endif
                                    </small>
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
                                                   value="1"
                                                   {{ old('refund_available', $event->refund_available) ? 'checked' : '' }}
                                            {{ in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']) ? 'disabled' : '' }}>
                                            <span class="switch-slider"></span>
                                        </label>
                                        <span class="switch-label">Allow cancellation refunds</span>
                                    </div>
                                    @if(in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']))
                                    <input type="hidden" name="refund_available" value="{{ $event->refund_available }}">
                                    <small class="text-muted">🔒 Locked after registration</small>
                                    @endif
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
                                           value="{{ old('contact_email', $event->contact_email) }}"
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
                                           value="{{ old('contact_phone', $event->contact_phone ? \App\Support\PhoneHelper::formatForDisplay($event->contact_phone) : '') }}"
                                           placeholder="012-3456789">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form Configuration -->
                    @if(!in_array($stage, ['registration', 'ongoing', 'past', 'cancelled']))
                    <div class="form-section" id="section-registration-config">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-sliders"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Registration Form Settings</h3>
                                <p class="section-subtitle">Customize registration requirements</p>
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
                                        <input type="checkbox" 
                                               id="require_emergency_contact" 
                                               name="require_emergency_contact" 
                                               value="1"
                                               {{ old('require_emergency_contact', $event->require_emergency_contact) ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Require Emergency Contact</span>
                                </div>

                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" 
                                               id="require_dietary_info" 
                                               name="require_dietary_info" 
                                               value="1"
                                               {{ old('require_dietary_info', $event->require_dietary_info) ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Require Dietary Requirements</span>
                                </div>

                                <div class="switch-container">
                                    <label class="switch-modern">
                                        <input type="checkbox" 
                                               id="require_special_requirements" 
                                               name="require_special_requirements" 
                                               value="1"
                                               {{ old('require_special_requirements', $event->require_special_requirements) ? 'checked' : '' }}>
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
                                        <input type="checkbox" 
                                               id="allow_cancellation" 
                                               name="allow_cancellation" 
                                               value="1"
                                               {{ old('allow_cancellation', $event->allow_cancellation) ? 'checked' : '' }}>
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
                                          placeholder="Add any special instructions or requirements for registrants...">{{ old('registration_instructions', $event->registration_instructions) }}</textarea>
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
                    @else
                    <div class="form-section">
                        <div class="alert alert-warning">
                            <i class="bi bi-lock me-2"></i>
                            <strong>Registration Form Settings Locked</strong>
                            <p class="mb-0 mt-2">Custom registration fields and form settings cannot be modified after registration has started to maintain data consistency.</p>
                        </div>
                    </div>
                    @endif

                    <!-- Media Section -->
                    <div class="form-section" id="section-media">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-image"></i>
                            </div>
                            <div>
                                <h3 class="section-title">Event Poster</h3>
                                <p class="section-subtitle">Update event image</p>
                            </div>
                        </div>

                        <div class="section-content">
                            <input type="hidden" 
                                   id="existing-poster-path" 
                                   value="{{ $event->poster_path ?? '' }}">

                            <!-- Current Poster -->
                            @if($event->poster_path)
                            <div class="current-poster-display mb-4">
                                <label class="modern-label">
                                    <i class="bi bi-image me-2"></i>Current Poster
                                </label>
                                <div class="current-poster-card">
                                    <img src="{{ asset('storage/event-posters/' . $event->poster_path) }}" 
                                         alt="Current Event Poster"
                                         class="current-poster-image">
                                    <div class="current-poster-overlay">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Current
                                        </span>
                                    </div>
                                </div>
                                <small class="form-text">Upload a new image below to replace the current poster</small>
                            </div>
                            @endif

                            <!-- Upload New Poster -->
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
                                        <h4>{{ $event->poster_path ? 'Upload new poster' : 'Drop your poster here' }}</h4>
                                        <p>or <span class="text-primary">click to browse</span></p>
                                        <small class="text-muted">PNG, JPG, WEBP up to 5MB • Min. 1280x720px recommended</small>
                                    </div>

                                    <div id="poster-preview" class="poster-preview-inline" style="display:none; margin-top:0.75rem; position:relative;">
                                        <img id="poster-preview-img"
                                             src=""
                                             alt="New poster preview"
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
                                    @if($event->poster_path)
                                    <img src="{{ asset('storage/event-posters/' . $event->poster_path) }}" alt="Event Poster">
                                    @else
                                    <div class="preview-poster-placeholder">
                                        <i class="bi bi-image"></i>
                                        <span>No poster yet</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="preview-details">
                                    <h4 id="previewTitle" class="preview-title">{{ $event->title }}</h4>
                                    <div class="preview-meta">
                                        <div class="preview-meta-item">
                                            <i class="bi bi-calendar-event"></i>
                                            <span id="previewDate">{{ $event->start_time->format('M d, Y h:i A') }}</span>
                                        </div>
                                        <div class="preview-meta-item">
                                            <i class="bi bi-geo-alt"></i>
                                            <span id="previewVenue">{{ $event->venue }}</span>
                                        </div>
                                        <div class="preview-meta-item">
                                            <i class="bi bi-bookmark"></i>
                                            <span id="previewCategory">{{ $event->category }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Info Card -->
                        <div class="tips-card">
                            <div class="tips-header">
                                <i class="bi bi-info-circle me-2"></i>
                                <span>Event Information</span>
                            </div>
                            <ul class="tips-list">
                                <li>Created: {{ $event->created_at->format('M d, Y') }}</li>
                                <li>Last Updated: {{ $event->updated_at->diffForHumans() }}</li>
                                <li>Status: <strong>{{ ucfirst($event->status) }}</strong></li>
                                @if($event->registrations()->where('status', 'confirmed')->count() > 0)
                                <li>Registrations: <strong>{{ $event->registrations()->where('status', 'confirmed')->count() }}</strong></li>
                                @endif
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-card">
                            @if($stage === 'draft')
                            <!-- Draft: Can save or publish -->
                            <button type="submit" 
                                    name="status" 
                                    value="draft" 
                                    class="btn-action btn-action-secondary w-100 mb-3" 
                                    id="saveDraftBtn">
                                <i class="bi bi-save me-2"></i>
                                Save Changes
                            </button>
                            <button type="submit" 
                                    name="status" 
                                    value="published" 
                                    class="btn-action btn-action-primary w-100 mb-3" 
                                    id="publishBtn">
                                <i class="bi bi-send me-2"></i>
                                Publish Event
                            </button>
                            @else
                            <!-- Published/Other: Just update -->
                            <button type="submit" 
                                    class="btn-action btn-action-primary w-100 mb-3" 
                                    id="updateBtn">
                                <i class="bi bi-save me-2"></i>
                                Update Event
                            </button>
                            @endif

                            <!-- Cancel Event Button -->
                            @if($event->status === 'published' && !in_array($stage, ['ongoing', 'past', 'cancelled']))
                            <button type="button" 
                                    class="btn-action btn-cancel-event w-100 mb-3" 
                                    onclick="showCancelModal()">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancel Event
                            </button>
                            @endif

                            <!-- Delete Button -->
                            @if(!in_array($stage, ['past', 'cancelled']) && $event->registrations()->where('status', 'confirmed')->count() === 0)
                            <button type="button" 
                                    class="btn-action btn-delete w-100" 
                                    onclick="confirmDelete()">
                                <i class="bi bi-trash me-2"></i>
                                Delete Event
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Event Modal -->
<div class="modal fade" id="cancelEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Cancel Event
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelEventForm" action="{{ route('events.cancel', $event) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action will cancel the event and notify all registered participants.
                        @if($event->is_paid && $event->refund_available)
                        Refunds will be processed automatically.
                        @endif
                    </div>

                    <div class="form-group-modern">
                        <label for="cancelled_reason" class="modern-label required">
                            Reason for Cancellation
                        </label>
                        <textarea class="form-control-modern" 
                                  id="cancelled_reason" 
                                  name="cancelled_reason" 
                                  rows="4" 
                                  placeholder="Please provide a clear reason for cancelling this event..."
                                  required
                                  minlength="10"></textarea>
                        <small class="form-text">This will be shown to all registered participants</small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancel Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>
                    Delete Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p>Are you sure you want to delete "<strong>{{ $event->title }}</strong>"?</p>
                <p class="mb-0">This will permanently remove the event and all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form action="{{ route('events.destroy', $event) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Yes, Delete Event
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/event-form-modern.css')
<style>
    /* Current Poster Display Styles */
    .current-poster-display {
        margin-bottom: 1.5rem;
    }

    .current-poster-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        max-width: 100%;
    }

    .current-poster-image {
        width: 100%;
        height: auto;
        display: block;
    }

    .current-poster-overlay {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .current-poster-overlay .badge {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/validation/phone-validator.js')
@vite('resources/js/events/event-form-preview.js')
@vite('resources/js/events/event-form-validation.js')
<script>
// Publish Event
    function publishEvent() {
        if (confirm('Are you sure you want to publish this event? It will be visible to all students.')) {
            const form = document.getElementById('eventForm');
            const publishInput = document.createElement('input');
            publishInput.type = 'hidden';
            publishInput.name = 'status';
            publishInput.value = 'published';
            form.appendChild(publishInput);
            form.submit();
        }
    }

// Show Cancel Modal
    function showCancelModal() {
        const modal = new bootstrap.Modal(document.getElementById('cancelEventModal'));
        modal.show();
    }

// Confirm Delete
    function confirmDelete() {
        const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
        modal.show();
    }

// Handle Cancel Form Submission
    document.getElementById('cancelEventForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const reason = document.getElementById('cancelled_reason').value;
        if (reason.length < 10) {
            alert('Please provide a more detailed reason (at least 10 characters)');
            return;
        }

        if (confirm('Are you absolutely sure you want to cancel this event?')) {
            this.submit();
        }
    });

// Load existing tags
//document.addEventListener('DOMContentLoaded', function() {
//    const existingTags = @json(old('tags', $event->tags ?? []));
//    if (Array.isArray(existingTags) && existingTags.length > 0) {
//        window.tags = existingTags;
//        if (typeof window.renderTags === 'function') {
//            window.renderTags();
//        }
//    }
//    
//    // Load existing custom fields
//    const existingFields = @json($event->customRegistrationFields ?? []);
//    if (existingFields.length > 0 && typeof window.loadExistingCustomFields === 'function') {
//        window.loadExistingCustomFields(existingFields);
//    }
//});

    document.addEventListener('DOMContentLoaded', function () {
        const existingTags = @json(old('tags', $event -> tags ?? []));
                console.log('existingTags from Blade:', existingTags);

        if (Array.isArray(existingTags) && existingTags.length > 0 && typeof window.initEventTags === 'function') {
            window.initEventTags(existingTags);
        }
    });

</script>
@endpush

@endsection