<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Requests;

use App\Models\User;
use App\Support\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Email and student_id are not editable by users
            // 头像可选，最多 2MB
            'avatar' => ['nullable', 'image', 'max:2048'],
            // 下面这些字段是你 users 表中新加的资料字段
            'phone' => ['nullable', 'string', 'max:50'],
            // program is not editable by users
            'interested_categories' => ['nullable', 'array'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate phone if provided
            if ($this->filled('phone')) {
                $phoneError = PhoneHelper::getValidationError($this->phone);
                if ($phoneError) {
                    $validator->errors()->add('phone', $phoneError);
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
    }
}
