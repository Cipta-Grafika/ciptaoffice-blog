@extends('layouts.cms')

@section('title', 'Edit ' . $post->title)

@section('cms-page', 'posts-form')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Editor artikel" :title="$post->title"
            description="Perbarui naskah, visual, dan status artikel sebelum dipublikasikan.">
            <x-slot:actions>
                <a class="btn btn-outline-dark" href="{{ route('cms.posts.preview', $post) }}" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-eye"></i>
                    <span class="cms-action-label--compact">Preview</span>
                </a>
                @if ($post->status->canSubmitForReview())
                    @can('submit', $post)
                        <form method="post" action="{{ route('cms.posts.submit', $post) }}">
                            @csrf
                            <button class="btn btn-warning" type="submit">Ajukan review</button>
                        </form>
                    @endcan
                @endif
                @can('admin')
                    @if ($post->status !== \App\Enums\PostStatus::Published)
                        <form method="post" action="{{ route('cms.posts.publish', $post) }}">
                            @csrf
                            <button class="btn btn-success" type="submit">Terbitkan</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('cms.posts.archive', $post) }}">
                            @csrf
                            <button class="btn btn-outline-dark" type="submit">Arsipkan</button>
                        </form>
                    @endif
                @endcan
            </x-slot:actions>
        </x-cms-page-header>

        <div><span class="cms-status cms-status--{{ $post->status->badge() }}">{{ $post->status->label() }}</span></div>

        @if ($post->review_note)
            <div class="alert alert-warning mb-0"><strong>Catatan reviewer</strong>
                <p class="mb-0 mt-1">{{ $post->review_note }}</p>
            </div>
        @endif

        <div class="cms-editor-layout">
            <form id="post-form" class="cms-form-surface" method="post" action="{{ route('cms.posts.update', $post) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Informasi artikel</h2>
                        <p>Judul dan ringkasan tampil pada halaman daftar serta metadata artikel.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="title">Judul</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="excerpt">Ringkasan</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="5" maxlength="1000" aria-describedby="excerpt_help" required>{{ old('excerpt', $post->excerpt) }}</textarea>
                        @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text" id="excerpt_help">Maksimum 1.000 karakter.</div>
                    </div>
                </section>
                <section class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Cover artikel</h2>
                        <p>Upload file baru hanya jika ingin mengganti cover saat ini. Maksimum 4 MB.</p>
                    </div>
                    <x-cms-image-dropzone
                        name="cover_image"
                        id="cover_image"
                        remove-name="remove_cover_image"
                        :current-src="$post->cover_image_path ? asset('storage/' . $post->cover_image_path) : null"
                        :current-alt="$post->cover_image_alt ?: 'Preview cover artikel'"
                        current-status="Cover tersimpan dan tetap digunakan."
                        empty-status="Artikel ini belum memiliki cover."
                        new-status="Cover baru siap disimpan."
                        removed-status="Cover akan dihapus saat disimpan."
                        choose-label="Pilih cover"
                        replace-label="Ganti cover"
                        help="JPEG, PNG, atau WebP. Maksimum 4 MB. Alt text dibuat otomatis dari judul artikel."
                        :error="$errors->first('cover_image')"
                    />
                    <div class="form-text mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Rekomendasi resolusi:</strong> 1280 × 640 piksel (rasio 2:1) atau 1280 × 720 piksel (rasio
                        16:9) dengan orientasi horizontal/landscape.
                    </div>
                </section>
                <section class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Isi artikel</h2>
                        <p>Alt text gambar inline dibuat otomatis; maksimum 4 MB per gambar.</p>
                    </div>
                    <input type="hidden" id="body_html" name="body_html" class="@error('body_html') is-invalid @enderror" value="{{ old('body_html', $post->body_html) }}">
                    @error('body_html')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                    <div data-quill data-input="#body_html" data-upload-url="{{ route('cms.posts.media.store', $post) }}">
                    </div>
                </section>
            </form>

            <aside class="cms-editor-aside">
                <section class="cms-surface">
                    <div class="cms-surface-header">
                        <div>
                            <p class="cms-surface-kicker mb-1">Metadata</p>
                            <h2 class="cms-surface-title mb-0">Informasi artikel</h2>
                        </div>
                        <button class="btn btn-primary cms-surface-save" type="submit" form="post-form">
                            <i class="bi bi-check" aria-hidden="true"></i><span>Simpan</span>
                        </button>
                    </div>
                    <div class="cms-surface-body">
                        <dl class="cms-detail-list cms-detail-list--stacked">
                            <dt>Slug</dt>
                            <dd class="text-break">{{ $post->slug }}</dd>
                            <dt>Author</dt>
                            <dd>{{ $post->author?->name ?? 'Konten impor' }}</dd>
                            <dt>Diajukan</dt>
                            <dd>{{ $post->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                            <dt>Diterbitkan</dt>
                            <dd>{{ $post->published_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                        </dl>
                    </div>
                </section>
                @can('admin')
                    @if ($post->status === \App\Enums\PostStatus::PendingReview)
                        <form class="cms-form-surface" method="post" action="{{ route('cms.posts.return', $post) }}">
                            @csrf
                            <div class="cms-form-section">
                                <div class="cms-form-section-heading">
                                    <h2>Kembalikan ke author</h2>
                                    <p>Sertakan alasan yang dapat ditindaklanjuti.</p>
                                </div>
                                <label class="form-label" for="review_note">Catatan wajib</label>
                                <textarea class="form-control @error('review_note') is-invalid @enderror" id="review_note" name="review_note" rows="4" required></textarea>
                                @error('review_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="cms-form-actions">
                                <button class="btn btn-outline-danger" type="submit">Kembalikan</button>
                            </div>
                        </form>
                    @endif
                @endcan
                <div class="cms-danger-actions" aria-label="Tindakan penghapusan artikel">
                    @can('delete', $post)
                        <form method="post" action="{{ route('cms.posts.destroy', $post) }}"
                            data-cms-confirm-form data-confirm-variant="warning"
                            data-confirm-title="Pindahkan artikel ke trash?"
                            data-confirm-message="Artikel “{{ $post->title }}” akan dipindahkan ke sampah."
                            data-confirm-action="Pindahkan ke sampah">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                                Pindahkan ke Sampah
                            </button>
                        </form>
                    @endcan
                    @can('forceDelete', $post)
                        <form method="post" action="{{ route('cms.posts.force-delete', $post) }}"
                            data-cms-confirm-form data-confirm-variant="danger"
                            data-confirm-title="Hapus artikel secara permanen?"
                            data-confirm-message="Artikel “{{ $post->title }}”, cover, dan seluruh gambar inline akan dihapus permanen serta tidak dapat dipulihkan."
                            data-confirm-action="Hapus permanen">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit">
                                <i class="bi bi-x-circle" aria-hidden="true"></i>
                                Hapus Permanen
                            </button>
                        </form>
                    @endcan
                </div>
            </aside>
        </div>
    </div>
@endsection
