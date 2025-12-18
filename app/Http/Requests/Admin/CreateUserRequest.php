<?php

namespace App\Http\Requests\Admin;

use App\Support\PhoneHelper;
use App\Support\StudentIdHelper;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users'],
            'phone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:student,club'],
            'program' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate phone
            if ($this->filled('phone')) {
                $phoneError = PhoneHelper::getValidationError($this->phone);
                if ($phoneError) {
                    $validator->errors()->add('phone', $phoneError);
                }
            }

            // Validate student_id
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
        // Format phone for storage if valid
        if ($this->filled('phone')) {
            $formatted = PhoneHelper::formatForStorage($this->phone);
            if ($formatted) {
                $this->merge(['phone' => $formatted]);
            }
        }

        // Format student_id to uppercase if valid
        if ($this->filled('student_id')) {
            $formatted = StudentIdHelper::format($this->student_id);
            if ($formatted) {
                $this->merge(['student_id' => $formatted]);
            }
        }
    }
}
