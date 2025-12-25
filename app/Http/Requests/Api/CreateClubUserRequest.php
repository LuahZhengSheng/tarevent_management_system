<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Requests\Api;

use App\Support\PhoneHelper;
use App\Support\StudentIdHelper;
use Illuminate\Validation\Rules\Password;

class CreateClubUserRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        
        // Check if user is authenticated
        if (!$user) {
            return false;
        }
        
        // Check if user account is active (not suspended or inactive)
        if ($user->isSuspended() || $user->isInactive()) {
            return false;
        }
        
        // Only admin or super_admin can create club users
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Merge parent rules (timestamp/requestID) with child rules
        return array_merge(parent::rules(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'password_confirmation' => ['nullable', 'string'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users'],
            'phone' => ['required', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'club_id' => ['nullable', 'integer', 'exists:clubs,id'],
        ]);
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
            'password.min' => 'The password must be at least 8 characters.',
            'password.mixed' => 'The password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one symbol.',
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

