<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Requests\Api;

use App\Support\PhoneHelper;
use App\Support\StudentIdHelper;
use Illuminate\Foundation\Http\FormRequest;

class CreateClubUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization can be added later if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users'],
            'phone' => ['required', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'club_id' => ['nullable', 'integer', 'exists:clubs,id'],
            'timestamp' => ['required', 'integer'], // Unix timestamp in seconds
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'The email address is already registered.',
            'student_id.unique' => 'The student ID is already registered.',
            'phone.required' => 'Phone number is required.',
            'club_id.exists' => 'The specified club does not exist.',
            'timestamp.required' => 'The timestamp field is required.',
            'timestamp.integer' => 'The timestamp must be a valid Unix timestamp.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate phone using PhoneHelper (reuse existing logic)
            if ($this->filled('phone')) {
                $phoneError = PhoneHelper::getValidationError($this->phone);
                if ($phoneError) {
                    $validator->errors()->add('phone', $phoneError);
                }
            }

            // Validate student_id using StudentIdHelper (reuse existing logic)
            if ($this->filled('student_id')) {
                $studentIdError = StudentIdHelper::getValidationError($this->student_id);
                if ($studentIdError) {
                    $validator->errors()->add('student_id', $studentIdError);
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Format phone for storage if valid (reuse existing logic)
        if ($this->filled('phone')) {
            $formatted = PhoneHelper::formatForStorage($this->phone);
            if ($formatted) {
                $this->merge(['phone' => $formatted]);
            }
        }

        // Format student_id to uppercase if valid (reuse existing logic)
        if ($this->filled('student_id')) {
            $formatted = StudentIdHelper::format($this->student_id);
            if ($formatted) {
                $this->merge(['student_id' => $formatted]);
            }
        }

        // Set default status if not provided
        if (!$this->filled('status')) {
            $this->merge(['status' => 'active']);
        }
    }
}

