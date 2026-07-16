<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        $hasCover = $this->hasFile('cover_image') || (bool) $this->route('post')->cover_image_path;

        return ['title' => ['required', 'string', 'max:180'], 'excerpt' => ['required', 'string', 'max:500'], 'body_html' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'cover_image_alt' => [Rule::requiredIf($hasCover), 'nullable', 'string', 'max:180']];
    }
}
