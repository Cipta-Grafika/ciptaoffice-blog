<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return ['product_category_id' => ['required', 'exists:product_categories,id'], 'name' => ['required', 'string', 'max:180'], 'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'], 'specifications_text' => ['nullable', 'string', 'max:5000'],
            'product_images' => ['nullable', 'array', 'max:8'],
            'product_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_order' => ['nullable', 'array', 'max:8'],
            'gallery_order.*' => ['string', 'distinct', 'regex:/^(thumbnail|image:[1-9][0-9]*)$/'],
            'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
