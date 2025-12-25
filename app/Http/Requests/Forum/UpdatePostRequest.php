<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');
        return $post && $post->canBeEditedBy(auth()->user());
    }

    protected function prepareForValidation(): void
    {
        // 默认值（防止前端没传）
        if (!$this->has('status')) {
            $this->merge(['status' => 'published']);
        }

        if (!$this->has('visibility')) {
            $this->merge(['visibility' => 'public']);
        }

        // 统一把 tags 转成数组，逻辑和 StorePostRequest 一致
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
            'title'       => ['required', 'string', 'min:5', 'max:100'],
            'content'     => ['required', 'string', 'min:1', 'max:500000'],
            'category_id' => ['required', 'exists:categories,id'],
            'visibility'  => ['required', 'string', Rule::in(['public', 'club_only'])],
            'club_ids'    => ['required_if:visibility,club_only', 'array', 'min:1'],
            'club_ids.*'  => ['exists:clubs,id'],
            'status'      => ['required', 'string', Rule::in(['draft', 'published'])],
            'tags'        => ['nullable', 'array', 'max:10'],
            'tags.*'      => ['string', 'max:50'],
            'media'       => ['nullable', 'array', 'max:10'],
            'media.*'     => [
                'file',
                function ($attribute, $value, $fail) {
                    $mimeType = $value->getMimeType();

                    if (str_starts_with($mimeType, 'image/')) {
                        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) {
                            $fail('Image must be: JPEG, PNG, JPG, GIF, or WEBP.');
                            return;
                        }

                        if ($value->getSize() > 10 * 1024 * 1024) {
                            $fail('Image size must not exceed 10MB.');
                            return;
                        }
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        if (!in_array($mimeType, ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'])) {
                            $fail('Video must be: MP4, MOV, AVI, or MPEG.');
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
            'replace_media' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Post title is required.',
            'title.min'        => 'Post title must be at least 5 characters.',
            'title.max'        => 'Post title must not exceed 100 characters.',
            'content.required' => 'Post content is required.',
            'content.max'      => 'Post content must not exceed 500,000 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists'   => 'Selected category is invalid.',
            'visibility.required'  => 'Please select post visibility.',
            'club_ids.required_if' => 'Please select at least one club for club-only posts.',
            'media.max'            => 'You can upload maximum 10 media files.',
            'tags.max'             => 'You can add up to 10 tags only.',
        ];
    }
}
