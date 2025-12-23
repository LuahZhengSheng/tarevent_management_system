<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Requests\Admin;

use App\Support\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'avatar' => ['nullable', 'image', 'max:2048'],
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
