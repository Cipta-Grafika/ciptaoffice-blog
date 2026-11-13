<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')){
            $slug = trim((string) $this->input('slug'));
            $this->merge([
                'slug' => $slug !== '' ? Str::slug($slug) : Str::slug((string) $this->input('title')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'], 
            'slug' => [
                'sometimes',
                'required', 
                'string', 
                'max:200', 
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($this->route('post')),
            ],
            'author_name' => [
                'nullable', 
                'string', 
                'max:120',
                'regex:/^(?=.*\p{L})[\p{L}\s.\',-]+$/u',
            ],
            'excerpt' => ['required', 'string', 'max:1000'], 
            'keywords' => ['nullable', 'string', 'max:500'],
            'body_html' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 
            'remove_cover_image' => ['nullable', 'boolean']];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Slug URL ini sudah digunakan oleh artikel lain',
            'slug.regex' => 'Format slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'slug.required' => 'Slug URL wajib diisi.',
            'author_name.regex' => 'Nama author hanya boleh berisi huruf, spasi, titik (.), koma (,), apostrof (\'), atau tanda hubung (-).',
        ];
    }
}
