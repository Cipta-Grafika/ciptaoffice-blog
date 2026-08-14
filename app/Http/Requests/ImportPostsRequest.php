<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class ImportPostsRequest extends FormRequest
{
    protected $errorBag = 'postImport';

    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'import_file' => [
                'required',
                'file',
                'max:10240',
                'extensions:csv,xlsx,json',
                'mimes:csv,txt,xlsx,json',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'Pilih file yang akan diimpor.',
            'import_file.file' => 'File import tidak valid.',
            'import_file.max' => 'Ukuran file import maksimal 10 MB.',
            'import_file.extensions' => 'Format file harus CSV, XLSX, atau JSON.',
            'import_file.mimes' => 'Format file harus CSV, XLSX, atau JSON.',
        ];
    }
}
