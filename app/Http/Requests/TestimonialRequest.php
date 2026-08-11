<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $testimonial = $this->route('testimonial');
        $reviewerName = trim((string) $this->input('reviewer_name'));

        if ($this->hasFile('avatar') || $testimonial?->avatar_path) {
            $this->merge(['avatar_alt' => 'Profil '.$reviewerName]);
        }
    }

    public function rules(): array
    {
        $hasAvatar = $this->hasFile('avatar') || (bool) $this->route('testimonial')?->avatar_path;

        return ['reviewer_name' => ['required', 'string', 'max:120'], 'reviewer_title' => ['nullable', 'string', 'max:120'], 'company' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:1000'], 'rating' => ['nullable', 'integer', 'between:1,5'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'avatar_alt' => [Rule::requiredIf($hasAvatar), 'nullable', 'string', 'max:180']];
    }
}
