<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Base API Request with IFA (Interface Agreement) compliant validation
 * 
 * All API requests must include either timestamp or requestID field.
 * If neither is provided, a requestID will be automatically generated.
 */
abstract class BaseApiRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     * Automatically generate requestID if not provided and timestamp is also missing.
     */
    protected function prepareForValidation(): void
    {
        // Only for POST/PUT/PATCH requests
        if (in_array($this->method(), ['POST', 'PUT', 'PATCH'])) {
            // If neither timestamp nor requestID is provided, generate a random requestID
            if (!$this->filled('timestamp') && !$this->filled('requestID')) {
                // Generate a unique requestID using UUID
                $this->merge([
                    'requestID' => (string) Str::uuid(),
                ]);
            }
        }
    }
    /**
     * Get the validation rules that apply to the request.
     * 
     * Child classes should merge their rules with parent rules
     */
    public function rules(): array
    {
        return [
            // At least one of timestamp or requestID must be provided
            'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
            'requestID' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     * 
     * Note: Validation is no longer needed here because prepareForValidation()
     * automatically generates a requestID if neither timestamp nor requestID is provided.
     */
    public function withValidator($validator): void
    {
        // Validation is handled in prepareForValidation() by auto-generating requestID
        // This method is kept for potential future custom validations
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'timestamp.regex' => 'The timestamp must be in format YYYY-MM-DD HH:MM:SS.',
            'requestID.max' => 'The requestID must not exceed 255 characters.',
        ];
    }
}

