<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Enum;
use App\Models\Event;
use App\Support\PhoneHelper;
use App\Enums\EventCategory;

class UpdateEventRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        $event = $this->route('event');

        // Must be authenticated
        if (!auth()->check()) {
            return false;
        }

        // Must be club admin and own this event
        return $event->canBeEditedBy(auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $event = $this->route('event');
        $now = now();

        // Determine event stage
        $isDraft = $event->status === 'draft';
        $isBeforeRegistration = $event->registration_start_time > $now;
        $isDuringRegistration = $event->registration_start_time <= $now && $event->registration_end_time >= $now;
        $isDuringEvent = $event->start_time <= $now && $event->end_time >= $now;
        $isPastEvent = $event->end_time < $now;

        $rules = [];

        // ========================
        // STAGE 0: CANCELLED EVENT
        // ========================
        if ($event->status === 'cancelled') {
            return $this->getPastEventRules();
        }

        // ====================
        // STAGE 1: DRAFT
        // ====================
        if ($isDraft) {
            return $this->getDraftRules();
        }

        // ====================
        // STAGE 2: BEFORE REGISTRATION
        // ====================
        if ($isBeforeRegistration) {
            return $this->getBeforeRegistrationRules($event);
        }

        // ====================
        // STAGE 3: DURING REGISTRATION
        // ====================
        if ($isDuringRegistration) {
            return $this->getDuringRegistrationRules($event);
        }

        // ====================
        // STAGE 4: DURING EVENT
        // ====================
        if ($isDuringEvent) {
            return $this->getDuringEventRules();
        }

        // ====================
        // STAGE 5: PAST EVENT
        // ====================
        if ($isPastEvent) {
            return $this->getPastEventRules();
        }

        return $rules;
    }

    /**
     * Rules for draft stage - everything can be modified
     */
    protected function getDraftRules(): array {
        return [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category' => ['required', new Enum(EventCategory::class)],
            'is_public' => 'required|boolean',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'registration_start_time' => 'required|date|after:now|before:start_time',
            'registration_end_time' => 'required|date|after:registration_start_time|before:start_time',
            'venue' => 'required|string|max:255',
            'location_map_url' => 'nullable|url|max:500',
            'is_paid' => 'required|boolean',
            'fee_amount' => 'nullable|numeric|min:0|max:9999.99',
            'max_participants' => 'nullable|integer|min:1',
            'refund_available' => 'nullable|boolean',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'allow_cancellation' => 'nullable|boolean',
            'require_emergency_contact' => 'nullable|boolean',
            'require_dietary_info' => 'nullable|boolean',
            'require_special_requirements' => 'nullable|boolean',
            'registration_instructions' => 'nullable|string|max:2000',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
            'status' => ['required', Rule::in(['draft', 'published'])],
            // Custom fields allowed in draft
            'custom_fields' => 'nullable|array',
            'custom_fields.*.label' => 'required_with:custom_fields|string|max:255',
            'custom_fields.*.name' => 'required_with:custom_fields|string|max:255',
            'custom_fields.*.type' => ['required_with:custom_fields', 'string', Rule::in(['text', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'email', 'tel'])],
            'custom_fields.*.options' => 'nullable|string',
            'custom_fields.*.placeholder' => 'nullable|string|max:255',
            'custom_fields.*.help_text' => 'nullable|string|max:500',
        ];
    }

    /**
     * Rules for before registration starts
     */
    protected function getBeforeRegistrationRules($event): array {
        return [
            // Display fields - can modify
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category' => ['required', new Enum(EventCategory::class)],
            'venue' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'location_map_url' => 'nullable|url|max:500',
            'registration_instructions' => 'nullable|string|max:2000',
            'requirements' => 'nullable|array',
            // Time fields - can modify
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'registration_start_time' => 'required|date|after:now|before:start_time',
            'registration_end_time' => 'required|date|after:registration_start_time|before:start_time',
            // Can modify fee settings
            'is_paid' => 'required|boolean',
            'fee_amount' => 'nullable|numeric|min:0|max:9999.99',
            'refund_available' => 'nullable|boolean',
            // Can increase max_participants
            'max_participants' => [
                'nullable',
                'integer',
                'min:1',
            ],
            // Registration settings can be modified
            'allow_cancellation' => 'nullable|boolean',
            'require_emergency_contact' => 'nullable|boolean',
            'require_dietary_info' => 'nullable|boolean',
            'require_special_requirements' => 'nullable|boolean',
            'is_public' => 'required|boolean',
            // Custom fields can be modified before registration
            'custom_fields' => 'nullable|array',
            'custom_fields.*.label' => 'required_with:custom_fields|string|max:255',
            'custom_fields.*.name' => 'required_with:custom_fields|string|max:255',
            'custom_fields.*.type' => ['required_with:custom_fields', 'string', Rule::in(['text', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'email', 'tel'])],
            'custom_fields.*.options' => 'nullable|string',
            'custom_fields.*.placeholder' => 'nullable|string|max:255',
            'custom_fields.*.help_text' => 'nullable|string|max:500',
            'custom_fields.*.required' => ['nullable', 'boolean', 'in:0,1'],
        ];
    }

    /**
     * Rules for during registration
     */
    protected function getDuringRegistrationRules($event): array {
        $confirmedCount = $event->registrations()->where('status', 'confirmed')->count();

        return [
            // Display fields only
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'category' => ['required', new Enum(EventCategory::class)],
            'venue' => 'required|string|max:255', // Small changes only
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'location_map_url' => 'nullable|url|max:500',
            'registration_instructions' => 'nullable|string|max:2000',
            // Can only extend registration_end_time
            'registration_end_time' => [
                'required',
                'date',
                'after:registration_start_time',
                'before:start_time',
                function ($attribute, $value, $fail) use ($event) {
                    if (strtotime($value) < strtotime($event->registration_end_time)) {
                        $fail('Registration end time can only be extended, not shortened.');
                    }
                },
            ],
            // Can only increase max_participants
            'max_participants' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($confirmedCount) {
                    if ($value !== null && $value < $confirmedCount) {
                        $fail("Cannot reduce capacity below current registrations ({$confirmedCount}).");
                    }
                },
            ],
                // NO changes to: start_time, end_time, registration_start_time, is_paid, fee_amount, 
                // refund_available, custom_fields, registration settings
        ];
    }

    /**
     * Rules for during event
     */
    protected function getDuringEventRules(): array {
        return [
            // Only display fields
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
                // Everything else locked
        ];
    }

    /**
     * Rules for past events
     */
    protected function getPastEventRules(): array {
        return [
            // Only display fields
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'category' => ['required', new Enum(EventCategory::class)],
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
                // All historical data locked
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array {
        return [
            'title.required' => 'Event title is required.',
            'title.min' => 'Event title must be at least 5 characters.',
            'title.max' => 'Event title cannot exceed 255 characters.',
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 20 characters.',
            'description.max' => 'Event description cannot exceed 5000 characters.',
            'start_time.after' => 'Event must start in the future.',
            'end_time.after' => 'Event end time must be after start time.',
            'registration_start_time.before' => 'Registration must start before the event.',
            'registration_start_time.after' => 'Registration must start in the future.',
            'registration_end_time.before' => 'Registration must close before event starts.',
            'registration_end_time.after' => 'Registration close time must be after registration open time.',
            'venue.required' => 'Venue is required.',
            'venue.max' => 'Venue cannot exceed 255 characters.',
            'fee_amount.numeric' => 'Fee amount must be a valid number.',
            'fee_amount.min' => 'Fee amount cannot be negative.',
            'fee_amount.max' => 'Fee amount cannot exceed RM 9,999.99.',
            'max_participants.integer' => 'Maximum participants must be a whole number.',
            'max_participants.min' => 'Maximum participants must be at least 1.',
            'contact_email.required' => 'Contact email is required.',
            'contact_email.email' => 'Please provide a valid email address.',
            'contact_phone.max' => 'Phone number cannot exceed 20 characters.',
            'poster.image' => 'Poster must be an image file.',
            'poster.mimes' => 'Poster must be a JPEG, PNG, JPG, or WEBP file.',
            'poster.max' => 'Poster size cannot exceed 5MB.',
            'location_map_url.url' => 'Please provide a valid URL for the map link.',
            'tags.max' => 'You can add up to 10 tags only.',
            'custom_fields.*.label.required_with' => 'Field label is required.',
            'custom_fields.*.name.required_with' => 'Field name is required.',
            'custom_fields.*.type.required_with' => 'Field type is required.',
            'custom_fields.*.type.in' => 'Invalid field type selected.',
            'custom_fields.*.required' => ['nullable', 'boolean', 'in:0,1'],
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation() {
        // 先对主要文本字段 trim
        $this->merge([
            'title' => trim((string) $this->title),
            'description' => trim((string) $this->description),
            'venue' => trim((string) $this->venue),
            'category' => trim((string) $this->category),
            'contact_email' => trim((string) $this->contact_email),
            'contact_phone' => trim((string) $this->contact_phone),
            'location_map_url' => trim((string) $this->location_map_url),
            'registration_instructions' => trim((string) $this->registration_instructions),
        ]);

        // 原来的 boolean 转换逻辑
        $booleanFields = [
            'is_public',
            'is_paid',
            'refund_available',
            'allow_cancellation',
            'require_emergency_contact',
            'require_dietary_info',
            'require_special_requirements',
        ];

        $data = [];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $data[$field] = filter_var(
                                $this->input($field),
                                FILTER_VALIDATE_BOOLEAN,
                                FILTER_NULL_ON_FAILURE
                        ) ?? false;
            }
        }

        // 原来的 phone 预格式化逻辑
        if ($this->has('contact_phone') && $this->input('contact_phone')) {
            $phone = $this->input('contact_phone');
            $formatted = PhoneHelper::formatForStorage($phone);

            if ($formatted) {
                $data['contact_phone'] = $formatted;
            } else {
                $data['contact_phone'] = $phone;
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array {
        return [
            'is_public' => 'event visibility',
            'is_paid' => 'payment requirement',
            'max_participants' => 'maximum participants',
            'fee_amount' => 'registration fee',
            'refund_available' => 'refund policy',
            'allow_cancellation' => 'cancellation policy',
            'require_emergency_contact' => 'emergency contact requirement',
            'require_dietary_info' => 'dietary info requirement',
            'require_special_requirements' => 'special requirements',
        ];
    }

    /**
     * Handle a failed authorization attempt
     */
    protected function failedAuthorization() {
        abort(403, 'You do not have permission to update this event.');
    }

    protected function passedValidation() {
        if ($this->filled('contact_phone')) {
            $error = PhoneHelper::getValidationError($this->contact_phone);
            if ($error) {
                throw ValidationException::withMessages([
                            'contact_phone' => $error,
                ]);
            }

            $this->merge([
                'contact_phone' => PhoneHelper::formatForStorage($this->contact_phone),
            ]);
        }
    }
}
