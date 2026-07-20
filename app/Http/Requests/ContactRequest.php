<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'], 'email' => ['nullable', 'email', 'max:180'], 'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:3000'], 'product_id' => ['nullable', 'exists:products,id'], 'website' => ['nullable', 'max:0'],
        ];
    }
}
