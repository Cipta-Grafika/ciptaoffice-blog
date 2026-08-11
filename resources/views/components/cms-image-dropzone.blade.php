@props([
    'name',
    'id' => null,
    'currentSrc' => null,
    'currentAlt' => 'Preview gambar',
    'removeName' => null,
    'accept' => 'image/jpeg,image/png,image/webp',
    'maxSize' => 4194304,
    'currentStatus' => 'Gambar tersimpan dan tetap digunakan.',
    'emptyStatus' => 'Belum ada gambar yang dipilih.',
    'newStatus' => 'Gambar baru siap disimpan.',
    'removedStatus' => 'Gambar akan dihapus saat disimpan.',
    'chooseLabel' => 'Pilih gambar',
    'replaceLabel' => 'Ganti gambar',
    'help' => 'JPEG, PNG, atau WebP. Maksimum 4 MB.',
    'error' => null,
])

@php
    $inputId = $id ?: 'image_' . str_replace(['[', ']', '.'], '_', $name);
    $hasCurrentImage = filled($currentSrc);
    $statusId = $inputId . '_status';
    $filenameId = $inputId . '_filename';
    $feedbackId = $inputId . '_feedback';
    $helpId = $inputId . '_help';
@endphp

<div
    {{ $attributes->class(['cms-image-dropzone', 'has-error' => filled($error)]) }}
    data-image-dropzone
    data-empty-status="{{ $emptyStatus }}"
    data-new-status="{{ $newStatus }}"
    data-removed-status="{{ $removedStatus }}"
    data-choose-label="{{ $chooseLabel }}"
    data-replace-label="{{ $replaceLabel }}"
    data-max-size="{{ $maxSize }}"
>
    <label class="cms-image-dropzone-target" for="{{ $inputId }}" data-image-dropzone-target>
        <span class="cms-image-dropzone-preview" aria-hidden="true">
            <img
                class="{{ $hasCurrentImage ? '' : 'd-none' }}"
                src="{{ $currentSrc ?: '' }}"
                data-image-dropzone-preview
                data-current-src="{{ $currentSrc ?: '' }}"
                alt="{{ $currentAlt }}"
            >
            <span class="cms-image-dropzone-placeholder {{ $hasCurrentImage ? 'd-none' : '' }}" data-image-dropzone-placeholder>
                <i class="bi bi-image" aria-hidden="true"></i>
            </span>
        </span>

        <span class="cms-image-dropzone-copy">
            <span class="cms-image-dropzone-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
            <strong id="{{ $statusId }}" data-image-dropzone-status data-current-status="{{ $hasCurrentImage ? $currentStatus : $emptyStatus }}">
                {{ $hasCurrentImage ? $currentStatus : $emptyStatus }}
            </strong>
            <span class="cms-image-dropzone-instruction">
                Tarik dan lepaskan gambar di sini, atau <span>klik untuk memilih file</span>.
            </span>
            <span class="cms-image-dropzone-action" data-image-dropzone-action aria-hidden="true">
                {{ $hasCurrentImage ? $replaceLabel : $chooseLabel }}
            </span>
        </span>
    </label>

    <input
        class="visually-hidden"
        type="file"
        id="{{ $inputId }}"
        name="{{ $name }}"
        accept="{{ $accept }}"
        data-image-dropzone-input
        aria-describedby="{{ $statusId }} {{ $filenameId }} {{ $feedbackId }} {{ $helpId }}"
        @if($error) aria-invalid="true" @endif
    >

    @if ($removeName)
        <input type="hidden" name="{{ $removeName }}" value="0" data-image-dropzone-remove-input>
    @endif

    <div class="cms-image-dropzone-footer">
        <span class="cms-image-dropzone-file" id="{{ $filenameId }}">
            <i class="bi bi-file-earmark-image" aria-hidden="true"></i>
            <span data-image-dropzone-filename>{{ $hasCurrentImage ? 'Tidak ada file baru dipilih.' : 'Tidak ada file dipilih.' }}</span>
            <span data-image-dropzone-file-size></span>
        </span>
        <button
            type="button"
            class="cms-image-dropzone-remove {{ $hasCurrentImage && $removeName ? '' : 'd-none' }}"
            data-image-dropzone-remove
            aria-label="Hapus pilihan gambar"
        >
            <i class="bi bi-trash3" aria-hidden="true"></i><span>Hapus</span>
        </button>
    </div>

    <div class="cms-image-dropzone-feedback {{ $error ? '' : 'd-none' }}" id="{{ $feedbackId }}" data-image-dropzone-feedback aria-live="polite">
        {{ $error }}
    </div>
    <div class="cms-image-dropzone-help" id="{{ $helpId }}">{{ $help }}</div>
</div>
