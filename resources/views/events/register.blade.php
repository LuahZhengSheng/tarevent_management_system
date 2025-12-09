@extends('layouts.app')

@section('title', 'Register for ' . $event->title)

@push('styles')
@vite('resources/css/events/event-registration.css')
@endpush

@section('content')
<div class="registration-container">
    <div class="registration-card">
        <!-- Event Header -->
        <div class="event-header">
            <h1>{{ $event->title }}</h1>
            <div class="event-meta">
                <div class="meta-item">
                    <i class="bi bi-calendar-event"></i>
                    <span>{{ $event->start_time->format('M d, Y') }}</span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-clock"></i>
                    <span>{{ $event->start_time->format('h:i A') }}</span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>{{ $event->venue }}</span>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <form id="registrationForm" class="registration-form" method="POST" action="{{ route('events.register.store', $event) }}">
            @csrf
            <input type="hidden" id="event_id" value="{{ $event->id }}">

            <!-- Event Information -->
            <div class="event-info-box">
                <h3><i class="bi bi-info-circle me-2"></i>Event Details</h3>
                <ul>
                    <li>
                        <i class="bi bi-tag"></i>
                        <span>Category: <strong>{{ $event->category }}</strong></span>
                    </li>
                    @if($event->max_participants)
                    <li>
                        <i class="bi bi-people"></i>
                        <span>Remaining Seats: <strong>{{ $event->remaining_seats }} / {{ $event->max_participants }}</strong></span>
                    </li>
                    @endif
                    <li>
                        <i class="bi bi-calendar-check"></i>
                        <span>Registration Deadline: <strong>{{ $event->registration_end_time->format('M d, Y h:i A') }}</strong></span>
                    </li>
                </ul>
            </div>

            <!-- Fee Display -->
            @if($event->is_paid)
            <div class="fee-display">
                <div class="label">Event Fee</div>
                <div class="amount">{{ $event->formatted_fee }}</div>
                <small class="text-muted mt-2 d-block">
                    @if($event->refund_available)
                    <i class="bi bi-check-circle text-success"></i> Refund available if cancelled before event
                    @else
                    <i class="bi bi-x-circle text-danger"></i> Non-refundable
                    @endif
                </small>
            </div>
            @else
            <div class="fee-display">
                <div class="label">Event Fee</div>
                <div class="amount" style="color: var(--success);">FREE</div>
            </div>
            @endif

            <!-- Cancellation Policy Notice -->
            @if(!$event->allow_cancellation)
            <div class="policy-notice warning">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>No Cancellation Policy</strong>
                    <p>Please note that once registered, you will not be able to cancel your registration for this event. Please confirm your availability before registering.</p>
                </div>
            </div>
            @else
            <div class="policy-notice success">
                <i class="bi bi-check-circle"></i>
                <div>
                    <strong>Flexible Cancellation</strong>
                    <p>You may cancel your registration before the event starts.</p>
                </div>
            </div>
            @endif

            <!-- Custom Instructions -->
            @if($event->registration_instructions)
            <div class="custom-instructions">
                <h4><i class="bi bi-chat-left-text me-2"></i>Important Instructions</h4>
                <p>{{ $event->registration_instructions }}</p>
            </div>
            @endif

            <!-- Personal Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="bi bi-person-circle"></i>
                    Personal Information
                </h2>

                {{-- Full Name --}}
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="text"
                               class="form-control"
                               id="full_name"
                               name="full_name"
                               value="{{ old('full_name', $userData['full_name']) }}"
                               data-validate="true"
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email">Email Address <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               value="{{ old('email', $userData['email']) }}"
                               data-validate="true"
                               readonly
                               disabled
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <small class="form-text"><i class="bi bi-lock-fill"></i> This field cannot be changed</small>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>

                {{-- Phone --}}
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="tel"
                               class="form-control"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $userData['phone']) }}"
                               placeholder="0123456789"
                               data-validate="true"
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!--<small class="form-text">Include country code (e.g., +60 for Malaysia)</small>-->
                    <small class="form-text">Malaysia phone number only</small>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="bi bi-mortarboard"></i>
                    Academic Information
                </h2>

                {{-- Student ID --}}
                <div class="form-group">
                    <label for="student_id">Student ID <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="text"
                               class="form-control"
                               id="student_id"
                               name="student_id"
                               value="{{ old('student_id', $userData['student_id']) }}"
                               data-validate="true"
                               disabled
                               readonly
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <small class="form-text"><i class="bi bi-lock-fill"></i> This field cannot be changed</small>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>

                {{-- Program --}}
                <div class="form-group">
                    <label for="program">Program/Course <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <select class="form-control"
                                id="program"
                                name="program"
                                data-validate="true"
                                required>
                            <option value="">Select your program</option>

                            @foreach($programOptions as $value => $label)
                            <option value="{{ $value }}"
                                    {{ old('program', $userData['program']) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>

            </div>

            <!-- Additional Information (Conditional) -->
            @if($event->require_dietary_info || $event->require_special_requirements)
            <div class="form-section">
                <h2 class="section-title">
                    <i class="bi bi-chat-left-text"></i>
                    Additional Information
                </h2>

                @if($event->require_dietary_info)
                <div class="form-group">
                    <label for="dietary_requirements">Dietary Requirements <span class="required-mark">*</span></label>
                    <textarea class="form-control"
                              id="dietary_requirements"
                              name="dietary_requirements"
                              rows="3"
                              placeholder="e.g., Vegetarian, Halal, No seafood, etc."
                              {{ $event->require_dietary_info ? 'required' : '' }}>{{ old('dietary_requirements') }}</textarea>
                    <small class="form-text">Let us know if you have any dietary restrictions or allergies</small>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                </div>
                @endif

                @if($event->require_special_requirements)
                <div class="form-group">
                    <label for="special_requirements">Special Requirements <span class="required-mark">*</span></label>
                    <textarea class="form-control"
                              id="special_requirements"
                              name="special_requirements"
                              rows="3"
                              placeholder="e.g., Wheelchair access, Sign language interpreter, etc."
                              {{ $event->require_special_requirements ? 'required' : '' }}>{{ old('special_requirements') }}</textarea>
                    <small class="form-text">Any special needs or accommodations required</small>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Custom Registration Fields -->
            @if($event->customRegistrationFields->count() > 0)
            <div class="form-section">
                <h2 class="section-title">
                    <i class="bi bi-list-ul"></i>
                    Event-Specific Information
                </h2>

                @foreach($event->customRegistrationFields as $customField)
                <div class="form-group">
                    <label for="custom_{{ $customField->name }}" class="modern-label {{ $customField->required ? 'required' : '' }}">
                        {{ $customField->label }}
                    </label>

                    @if($customField->type === 'textarea')
                    <textarea class="form-control"
                              id="custom_{{ $customField->name }}"
                              name="custom_fields[{{ $customField->name }}]"
                              rows="3"
                              placeholder="{{ $customField->placeholder }}"
                              data-validate="true"
                              data-custom-field="{{ $customField->name }}"
                              {{ $customField->required ? 'required' : '' }}>{{ old("custom_fields.{$customField->name}") }}</textarea>

                    @elseif($customField->type === 'select')
                    <select class="form-control"
                            id="custom_{{ $customField->name }}"
                            name="custom_fields[{{ $customField->name }}]"
                            data-validate="true"
                            data-custom-field="{{ $customField->name }}"
                            {{ $customField->required ? 'required' : '' }}>
                        <option value="">Select an option</option>
                        @if($customField->options && is_array($customField->options))
                        @foreach($customField->options as $option)
                        <option value="{{ $option }}" {{ old("custom_fields.{$customField->name}") == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                        @endforeach
                        @endif
                    </select>

                    @elseif($customField->type === 'radio')
                    @if($customField->options && is_array($customField->options))
                    @foreach($customField->options as $option)
                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               id="custom_{{ $customField->name }}_{{ $loop->index }}"
                               name="custom_fields[{{ $customField->name }}]"
                               value="{{ $option }}"
                               {{ old("custom_fields.{$customField->name}") == $option ? 'checked' : '' }}
                        {{ $customField->required ? 'required' : '' }}>
                        <label class="form-check-label" for="custom_{{ $customField->name }}_{{ $loop->index }}">
                            {{ $option }}
                        </label>
                    </div>
                    @endforeach
                    @endif

                    @elseif($customField->type === 'checkbox')
                    @if($customField->options && is_array($customField->options))
                    @foreach($customField->options as $option)
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="custom_{{ $customField->name }}_{{ $loop->index }}"
                               name="custom_fields[{{ $customField->name }}][]"
                               value="{{ $option }}"
                               {{ is_array(old("custom_fields.{$customField->name}")) && in_array($option, old("custom_fields.{$customField->name}")) ? 'checked' : '' }}>
                        <label class="form-check-label" for="custom_{{ $customField->name }}_{{ $loop->index }}">
                            {{ $option }}
                        </label>
                    </div>
                    @endforeach
                    @endif

                    @else
                    <div class="field-with-spinner">
                        <input type="{{ $customField->type }}"
                               class="form-control"
                               id="custom_{{ $customField->name }}"
                               name="custom_fields[{{ $customField->name }}]"
                               value="{{ old("custom_fields.{$customField->name}") }}"
                               placeholder="{{ $customField->placeholder }}"
                               data-validate="true"
                               data-custom-field="{{ $customField->name }}"
                               {{ $customField->required ? 'required' : '' }}>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($customField->help_text)
                    <small class="form-text">{{ $customField->help_text }}</small>
                    @endif

                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Emergency Contact (Conditional) -->
            @if($event->require_emergency_contact)
            <div class="form-section">
                <h2 class="section-title">
                    <i class="bi bi-telephone"></i>
                    Emergency Contact
                </h2>

                {{-- Emergency Contact Name --}}
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="text"
                               class="form-control"
                               id="emergency_contact_name"
                               name="emergency_contact_name"
                               value="{{ old('emergency_contact_name') }}"
                               data-validate="true"
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>

                {{-- Emergency Contact Phone --}}
                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone <span class="required-mark">*</span></label>

                    <div class="field-with-spinner">
                        <input type="tel"
                               class="form-control"
                               id="emergency_contact_phone"
                               name="emergency_contact_phone"
                               value="{{ old('emergency_contact_phone') }}"
                               placeholder="0123456789"
                               data-validate="true"
                               required>

                        <div class="field-spinner">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 2px;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i>
                        <span></span>
                    </div>
                    <div class="valid-feedback">
                        <i class="bi bi-check-circle"></i>
                        <span>Looks good!</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Terms and Conditions -->
            <div class="terms-checkbox">
                <input type="checkbox"
                       id="terms_accepted"
                       name="terms_accepted"
                       value="1"
                       required>
                <label for="terms_accepted">
                    I have read and agree to the <a href="#" target="_blank">Terms and Conditions</a>
                    and <a href="#" target="_blank">Privacy Policy</a>. I understand that my information
                    will be used for event management purposes only.
                    @if(!$event->allow_cancellation)
                    <strong class="text-danger d-block mt-2">
                        I acknowledge that this registration cannot be cancelled once confirmed.
                    </strong>
                    @endif
                </label>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <i class="bi bi-shield-check"></i>
                <span>Your information is encrypted and securely stored. We respect your privacy.</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">
                    <i class="bi bi-check-circle"></i>
                    @if($event->is_paid)
                    Proceed to Payment
                    @else
                    Complete Registration
                    @endif
                </span>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Phone Validator - Must load BEFORE registration form script -->
@vite('resources/js/validation/phone-validator.js')
@vite('resources/js/events/event-registration.js')
@endpush
