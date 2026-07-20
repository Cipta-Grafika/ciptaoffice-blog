<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $hasCover = $this->hasFile('cover_image') || (bool) $this->route('product')?->cover_image_path;

        return ['product_category_id' => ['required', 'exists:product_categories,id'], 'name' => ['required', 'string', 'max:180'], 'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'], 'specifications_text' => ['nullable', 'string', 'max:5000'], 'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_image_alt' => [Rule::requiredIf($hasCover), 'nullable', 'string', 'max:180'], 'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
