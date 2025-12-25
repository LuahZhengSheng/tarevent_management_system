<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base Request for Club API endpoints
 * Implements IFA standard requirement for timestamp/requestID
 */
class ClubApiRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Merge parent rules (timestamp/requestID)
        return array_merge(parent::rules(), [
            // Additional rules can be added by child classes
        ]);
    }
}

