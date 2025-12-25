<?php

namespace App\Http\Requests\Event;

use App\Support\PhoneHelper;
use App\Enums\EventCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Enum;

class StoreEventRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // Only club admins can create events
        return auth()->check() && auth()->user()->hasRole('club');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:5',
            ],
            'description' => [
                'required',
                'string',
                'min:20',
                'max:5000',
            ],
            'club_id' => [
                'nullable',
                'exists:clubs,id',
            ],
            'start_time' => [
                'required',
                'date',
                'after:now',
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
            ],
            'registration_start_time' => [
                'required',
                'date',
                'after:now',
                'before:start_time',
            ],
            'registration_end_time' => [
                'required',
                'date',
                'after:registration_start_time',
                'before:start_time',
            ],
            'venue' => [
                'required',
                'string',
                'max:255',
            ],
            'category' => ['required', new Enum(EventCategory::class)],
            'is_public' => [
                'required',
                'boolean',
            ],
            'is_paid' => [
                'required',
                'boolean',
            ],
            'fee_amount' => [
                'required_if:is_paid,true',
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'refund_available' => [
                'required',
                'boolean',
            ],
            'max_participants' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'poster' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB
            ],
            'contact_email' => [
                'required',
                'email',
                'max:255',
            ],
            'contact_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\(\)\s]+$/',
            ],
            'location_map_url' => [
                'nullable',
                'url',
                'max:500',
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10',
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'status' => [
                'nullable',
                Rule::in(['draft', 'published']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'title.required' => 'Event title is required.',
            'title.min' => 'Event title must be at least 5 characters.',
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 20 characters.',
            'start_time.required' => 'Event start time is required.',
            'start_time.after' => 'Event must start in the future.',
            'end_time.required' => 'Event end time is required.',
            'end_time.after' => 'Event end time must be after start time.',
            'registration_start_time.required' => 'Registration start time is required.',
            'registration_start_time.before' => 'Registration must start before the event begins.',
            'registration_start_time.after' => 'Registration must start in the future.',
            'registration_end_time.required' => 'Registration end time is required.',
            'registration_end_time.after' => 'Registration close time must be after open time.',
            'registration_end_time.before' => 'Registration must close before event starts.',
            'venue.required' => 'Event venue is required.',
            'fee_amount.required_if' => 'Fee amount is required for paid events.',
            'poster.image' => 'Poster must be an image file.',
            'poster.max' => 'Poster size must not exceed 5MB.',
            'contact_email.required' => 'Contact email is required.',
            'contact_email.email' => 'Please enter a valid contact email.',
            'contact_phone.regex' => 'Please enter a valid Malaysian phone number.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array {
        return [
            'is_public' => 'event visibility',
            'is_paid' => 'payment requirement',
            'max_participants' => 'maximum participants',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization() {
        abort(403, 'Only club administrators can create events.');
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation() {
        // 先对文本字段做 trim
        $this->merge([
            'title' => trim((string) $this->title),
            'description' => trim((string) $this->description),
            'venue' => trim((string) $this->venue),
            'category' => trim((string) $this->category),
            'contact_email' => trim((string) $this->contact_email),
            'contact_phone' => trim((string) $this->contact_phone),
            'location_map_url' => trim((string) $this->location_map_url),
            // status 也顺便 trim 一下，避免意外空格
            'status' => $this->has('status') ? trim((string) $this->status) : $this->status,
        ]);

        // 再做布尔字段转换
        $this->merge([
            'is_public' => filter_var($this->is_public, FILTER_VALIDATE_BOOLEAN),
            'is_paid' => filter_var($this->is_paid, FILTER_VALIDATE_BOOLEAN),
            'refund_available' => filter_var($this->refund_available, FILTER_VALIDATE_BOOLEAN),
        ]);

        // 没有 status 时默认 draft
        if (!$this->has('status') || $this->status === null || $this->status === '') {
            $this->merge(['status' => 'draft']);
        }
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
