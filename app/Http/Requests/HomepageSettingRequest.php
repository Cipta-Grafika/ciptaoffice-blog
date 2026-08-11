<?php

namespace App\Http\Requests;

use App\Models\HomepageSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomepageSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $settings = HomepageSetting::current();

        if ($this->hasFile('hero_image')) {
            $this->merge(['hero_image_alt' => trim((string) $this->input('title'))]);
        } elseif ($settings->hero_image_path) {
            $this->merge([
                'hero_image_alt' => $settings->hero_image_alt ?: trim((string) $this->input('title')),
            ]);
        }
    }

    public function rules(): array
    {
        $hasImage = $this->hasFile('hero_image') || (bool) HomepageSetting::current()->hero_image_path;
        $safeUrl = ['nullable', 'string', 'max:255', 'regex:/\A(?:#[A-Za-z0-9_-]+|\/(?!\/)\S*|https?:\/\/\S+)\z/i'];

        return ['eyebrow' => ['nullable', 'string', 'max:100'], 'title' => ['required', 'string', 'max:180'], 'summary' => ['required', 'string', 'max:1000'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'hero_image_alt' => [Rule::requiredIf($hasImage), 'nullable', 'string', 'max:180'],
            'primary_cta_label' => ['nullable', 'string', 'max:60'], 'primary_cta_url' => $safeUrl, 'secondary_cta_label' => ['nullable', 'string', 'max:60'], 'secondary_cta_url' => $safeUrl];
    }
}
