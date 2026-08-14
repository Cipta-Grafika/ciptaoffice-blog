@props([
    'id',
    'action',
    'error' => null,
    'openOnLoad' => false,
])

@php
    $titleId = $id.'_title';
    $descriptionId = $id.'_description';
    $helpId = $id.'_help';
    $feedbackId = $id.'_feedback';
    $inputId = $id.'_file';
@endphp

<dialog class="cms-import-modal" id="{{ $id }}" data-cms-import-modal data-max-size="10485760"
    data-accepted-extensions=".csv,.xlsx,.json" aria-labelledby="{{ $titleId }}"
    aria-describedby="{{ $descriptionId }}" @if ($openOnLoad) data-open-on-load @endif>
    <form class="cms-import-modal__panel" method="post" action="{{ $action }}" enctype="multipart/form-data"
        data-cms-import-form novalidate>
        @csrf

        <div class="cms-import-modal__accent" aria-hidden="true"></div>
        <header class="cms-import-modal__header">
            <span class="cms-import-modal__header-icon" aria-hidden="true">
                <i class="bi bi-file-earmark-arrow-up"></i>
            </span>
            <div>
                <p>Editorial intake</p>
                <h2 id="{{ $titleId }}">Import artikel</h2>
            </div>
            <button class="cms-import-modal__close" type="button" data-cms-import-close
                aria-label="Tutup modal import">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="cms-import-modal__body">
            <div class="cms-import-modal__intro">
                <div>
                    <span class="cms-import-modal__step">01 / Pilih file</span>
                    <h3>Tambahkan artikel dalam satu proses</h3>
                </div>
                <p id="{{ $descriptionId }}">Setiap baris yang valid akan dibuat sebagai draft milik akun Anda.</p>
            </div>

            <div class="cms-import-dropzone {{ $error ? 'has-error' : '' }}" data-cms-import-dropzone>
                <label class="cms-import-dropzone__target" for="{{ $inputId }}" data-cms-import-dropzone-target>
                    <span class="cms-import-dropzone__symbol" aria-hidden="true">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </span>
                    <strong>Tarik dan letakkan file di sini</strong>
                    <span>atau <u>pilih file</u> dari perangkat Anda</span>
                    <small>CSV, Excel (XLSX), atau JSON · Maksimal 10 MB</small>
                </label>

                <div class="cms-import-file" data-cms-import-file hidden>
                    <span class="cms-import-file__icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </span>
                    <span class="cms-import-file__copy">
                        <strong data-cms-import-filename></strong>
                        <span><span data-cms-import-filesize></span> · Siap diimpor</span>
                    </span>
                    <button type="button" data-cms-import-remove aria-label="Hapus file terpilih">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <input class="visually-hidden" id="{{ $inputId }}" name="import_file" type="file"
                    accept=".csv,.xlsx,.json" aria-describedby="{{ $helpId }} {{ $feedbackId }}"
                    data-cms-import-input required>
            </div>

            <p class="cms-import-modal__feedback {{ $error ? '' : 'd-none' }}" id="{{ $feedbackId }}"
                data-cms-import-feedback role="alert">{{ $error }}</p>

            <div class="cms-import-schema" id="{{ $helpId }}">
                <span class="cms-import-schema__icon" aria-hidden="true"><i class="bi bi-layout-three-columns"></i></span>
                <div>
                    <strong>Struktur data artikel</strong>
                    <p>CSV/XLSX menggunakan baris header. JSON dapat berupa array, objek tunggal, atau wrapper <code>articles</code>. Alias <em>judul</em>, <em>ringkasan</em>, dan <em>isi</em> juga diterima.</p>
                    <div class="cms-import-schema__columns" aria-label="Kolom wajib file import">
                        <code>title</code>
                        <code>excerpt</code>
                        <code>body_html</code>
                    </div>
                </div>
            </div>
        </div>

        <footer class="cms-import-modal__footer">
            <p><i class="bi bi-shield-check" aria-hidden="true"></i>Konten HTML akan dibersihkan sebelum disimpan.</p>
            <div>
                <button class="btn btn-outline-secondary" type="button" data-cms-import-close>
                    Batal
                </button>
                <button class="btn btn-primary cms-import-modal__submit" type="submit"
                    data-cms-import-submit disabled>
                    <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"
                        data-cms-import-spinner></span>
                    <i class="bi bi-upload" aria-hidden="true" data-cms-import-submit-icon></i>
                    <span data-cms-import-submit-label>Import artikel</span>
                </button>
            </div>
        </footer>
    </form>
</dialog>
