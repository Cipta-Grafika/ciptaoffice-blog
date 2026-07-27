@extends('layouts.cms')

@section('title', 'Edit ' . $post->title)

@section('cms-page', 'posts-form')

@section('content')
    <div class="cms-page">
        <a class="cms-back-link" href="{{ route('cms.posts.index') }}"><i class="bi bi-arrow-left"></i> Daftar artikel</a>
        <x-cms-page-header eyebrow="Editor artikel" :title="$post->title"
            description="Perbarui naskah, visual, dan status artikel sebelum dipublikasikan.">
            <x-slot:actions>
                <a class="btn btn-outline-dark" href="{{ route('cms.posts.preview', $post) }}" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-eye"></i><span
                        class="cms-action-label--compact">Preview</span></a>
                @can('submit', $post)
                    <form method="post" action="{{ route('cms.posts.submit', $post) }}">@csrf<button class="btn btn-warning"
                            type="submit">Ajukan review</button></form>
                @endcan
                @can('admin')
                    @if ($post->status !== \App\Enums\PostStatus::Published)
                        <form method="post" action="{{ route('cms.posts.publish', $post) }}">@csrf<button
                                class="btn btn-success" type="submit">Terbitkan</button></form>
                    @else
                        <form method="post" action="{{ route('cms.posts.archive', $post) }}">@csrf<button
                                class="btn btn-outline-dark" type="submit">Arsipkan</button></form>
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
                    <div class="mb-3"><label class="form-label" for="title">Judul</label><input class="form-control"
                            id="title" name="title" value="{{ old('title', $post->title) }}" required></div>
                    <div><label class="form-label" for="excerpt">Ringkasan</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500" required>{{ old('excerpt', $post->excerpt) }}</textarea>
                    </div>
                </section>
                <section class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Cover artikel</h2>
                        <p>Upload file baru hanya jika ingin mengganti cover saat ini. Maksimum 4 MB.</p>
                    </div>
                    <div class="cms-cover-field" data-cover-picker>
                        <div class="cms-cover-preview"><img class="{{ $post->cover_image_path ? '' : 'd-none' }}"
                                data-cover-preview
                                src="{{ $post->cover_image_path ? Storage::disk('public')->url($post->cover_image_path) : '' }}"
                                data-current-src="{{ $post->cover_image_path ? Storage::disk('public')->url($post->cover_image_path) : '' }}"
                                alt="{{ $post->cover_image_alt ?: 'Preview cover artikel' }}">
                            <div class="cms-cover-placeholder {{ $post->cover_image_path ? 'd-none' : '' }}"
                                data-cover-placeholder aria-hidden="true"><i class="bi bi-image"></i></div>
                        </div>
                        <div>
                            <strong class="d-block" data-cover-status
                                data-current-status="{{ $post->cover_image_path ? 'Cover tersimpan dan tetap digunakan.' : 'Artikel ini belum memiliki cover.' }}">{{ $post->cover_image_path ? 'Cover tersimpan dan tetap digunakan.' : 'Artikel ini belum memiliki cover.' }}</strong>
                            <p class="small text-muted mt-1 mb-3">
                                {{ $post->cover_image_path ? 'Pilih file baru untuk mengganti cover.' : 'Pilih gambar cover untuk artikel ini.' }}
                            </p>
                            <input class="visually-hidden" type="file" id="cover_image" name="cover_image"
                                accept="image/jpeg,image/png,image/webp" data-cover-input
                                aria-describedby="cover_image_name cover_image_help">
                            <div class="d-flex flex-wrap align-items-center gap-2"><label
                                    class="btn btn-sm btn-outline-dark mb-0" for="cover_image"><i
                                        class="bi bi-upload me-1"></i>{{ $post->cover_image_path ? 'Ganti cover' : 'Pilih cover' }}</label><span
                                    class="small text-muted text-break" id="cover_image_name" data-cover-filename
                                    aria-live="polite">Tidak ada file baru dipilih.</span></div>
                            <div class="form-text" id="cover_image_help">JPEG, PNG, atau WebP. Alt text dibuat otomatis dari
                                judul artikel.</div>
                        </div>
                    </div>
                </section>
                <section class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Isi artikel</h2>
                        <p>Alt text gambar inline dibuat otomatis; maksimum 4 MB per gambar.</p>
                    </div>
                    <input type="hidden" id="body_html" name="body_html" value="{{ old('body_html', $post->body_html) }}">
                    <div data-quill data-input="#body_html" data-upload-url="{{ route('cms.posts.media.store', $post) }}">
                    </div>
                </section>
                <div class="cms-form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>
                        Simpan perubahan</button></div>
            </form>

            <aside class="cms-editor-aside">
                <section class="cms-surface">
                    <div class="cms-surface-header">
                        <div>
                            <p class="cms-surface-kicker mb-1">Metadata</p>
                            <h2 class="cms-surface-title mb-0">Informasi artikel</h2>
                        </div>
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
                        <form class="cms-form-surface" method="post" action="{{ route('cms.posts.return', $post) }}">@csrf
                            <div class="cms-form-section">
                                <div class="cms-form-section-heading">
                                    <h2>Kembalikan ke author</h2>
                                    <p>Sertakan alasan yang dapat ditindaklanjuti.</p>
                                </div><label class="form-label" for="review_note">Catatan wajib</label>
                                <textarea class="form-control" id="review_note" name="review_note" rows="4" required></textarea>
                            </div>
                            <div class="cms-form-actions"><button class="btn btn-outline-danger"
                                    type="submit">Kembalikan</button></div>
                        </form>
                    @endif
                @endcan
                @can('delete', $post)
                    <form class="cms-danger-action" method="post" action="{{ route('cms.posts.destroy', $post) }}"
                        onsubmit="return confirm('Pindahkan draft ini ke sampah?')">@csrf @method('DELETE')<button
                            class="btn btn-link text-danger px-0">Hapus artikel</button></form>
                @endcan
            </aside>
        </div>
    </div>
@endsection
