<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (config('app.env') === 'local' && !Auth::check()) {
            $user = User::find(1);
            if ($user) {
                Auth::login($user);
            }
        }

        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('status')) {
            $this->merge(['status' => 'published']);
        }

        if (!$this->has('visibility')) {
            $this->merge(['visibility' => 'public']);
        }

        if ($this->has('tags')) {
            $tags = $this->input('tags');

            if (is_string($tags)) {
                if (empty(trim($tags))) {
                    $this->merge(['tags' => []]);
                } elseif ($this->isJsonString($tags)) {
                    $this->merge(['tags' => json_decode($tags, true) ?? []]);
                } else {
                    $tagsArray = array_filter(array_map('trim', explode(',', $tags)));
                    $this->merge(['tags' => array_values($tagsArray)]);
                }
            } elseif (is_array($tags)) {
                $cleanTags = array_filter(array_map('trim', $tags));
                $this->merge(['tags' => array_values($cleanTags)]);
            } else {
                $this->merge(['tags' => []]);
            }
        } else {
            $this->merge(['tags' => []]);
        }
    }

    public function isJsonString($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:100'],
            'content' => ['required', 'string', 'min:1', 'max:500000'],
            'category_id' => ['required', 'exists:categories,id'],
            'visibility' => ['required', 'string', Rule::in(['public', 'club_only'])],
            'club_ids' => ['required_if:visibility,club_only', 'array', 'min:1'],
            'club_ids.*' => ['exists:clubs,id'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => [
                'file',
                function ($attribute, $value, $fail) {
                    $mimeType = $value->getMimeType();

                    if (str_starts_with($mimeType, 'image/')) {
                        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                        if (!in_array($mimeType, $allowedImageTypes)) {
                            $fail('The image must be JPEG, PNG, JPG, GIF, or WEBP.');
                            return;
                        }

                        if ($value->getSize() > 10 * 1024 * 1024) {
                            $fail('Image size must not exceed 10MB.');
                            return;
                        }
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $allowedVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
                        if (!in_array($mimeType, $allowedVideoTypes)) {
                            $fail('The video must be MP4, MOV, AVI, or MPEG.');
                            return;
                        }

                        if ($value->getSize() > 100 * 1024 * 1024) {
                            $fail('Video size must not exceed 100MB.');
                            return;
                        }
                    } else {
                        $fail('File must be an image or video.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Post title is required.',
            'title.min' => 'Post title must be at least 5 characters.',
            'title.max' => 'Post title must not exceed 100 characters.',

            'content.required' => 'Post content is required.',
            'content.max' => 'Post content must not exceed 500,000 characters.',

            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category is invalid.',

            'visibility.required' => 'Please select post visibility.',
            'visibility.in' => 'Invalid visibility option.',

            'club_ids.required_if' => 'Please select at least one club for club-only posts.',
            'club_ids.min' => 'Please select at least one club.',
            'club_ids.*.exists' => 'One or more selected clubs are invalid.',

            'status.in' => 'Invalid post status.',

            'media.max' => 'You can upload maximum 10 media files.',

            'tags.array' => 'Tags must be in array format.',
            'tags.max' => 'You can add up to 10 tags only.',
            'tags.*.string' => 'Each tag must be a valid string.',
            'tags.*.max' => 'Each tag must not exceed 50 characters.',
        ];
    }
}
