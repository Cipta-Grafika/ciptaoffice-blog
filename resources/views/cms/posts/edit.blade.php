@extends('layouts.cms')
@section('title', 'Edit ' . $post->title)
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><a class="link-arrow" href="{{ route('cms.posts.index') }}"><i class="bi bi-arrow-left"></i> Daftar artikel</a>
            <h1 class="font-display display-5 mt-2 mb-1">{{ $post->title }}</h1>
            <span class="badge text-bg-{{ $post->status->badge() }}">{{ $post->status->label() }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-dark" href="{{ route('cms.posts.preview', $post) }}" target="_blank"
                rel="noopener noreferrer"><i class="bi bi-eye me-1"></i>Preview artikel</a>
            @can('submit', $post)
                <form method="post" action="{{ route('cms.posts.submit', $post) }}">
                    @csrf
                    <button class="btn btn-warning">Ajukan review</button>
                </form>
            @endcan
            @can('admin')
                @if ($post->status !== \App\Enums\PostStatus::Published)
                    <form method="post" action="{{ route('cms.posts.publish', $post) }}">
                        @csrf
                        <button class="btn btn-success">Terbitkan</button>
                    </form>
                @else
                    <form method="post" action="{{ route('cms.posts.archive', $post) }}">
                        @csrf
                        <button class="btn btn-outline-dark">Arsipkan</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
    @if ($post->review_note)
        <div class="alert alert-warning"><strong>Catatan reviewer:</strong><br>{{ $post->review_note }}</div>
    @endif
    <div class="row g-4">
        <div class="col-xl-8">
            <form id="post-form" class="cms-card p-4" method="post" action="{{ route('cms.posts.update', $post) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="title">Judul</label>
                    <input class="form-control" id="title" name="title" value="{{ old('title', $post->title) }}"
                        required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="excerpt">Ringkasan</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="500" required>{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div class="mb-3">
                    <span class="form-label d-block">Cover artikel</span>
                    <div class="cms-cover-field" data-cover-picker>
                        <div class="cms-cover-preview">
                            <img class="{{ $post->cover_image_path ? '' : 'd-none' }}" data-cover-preview
                                src="{{ $post->cover_image_path ? Storage::disk('public')->url($post->cover_image_path) : '' }}"
                                data-current-src="{{ $post->cover_image_path ? Storage::disk('public')->url($post->cover_image_path) : '' }}"
                                alt="{{ $post->cover_image_alt ?: 'Preview cover artikel' }}">
                            <div class="cms-cover-placeholder {{ $post->cover_image_path ? 'd-none' : '' }}"
                                data-cover-placeholder aria-hidden="true"><i class="bi bi-image"></i></div>
                        </div>
                        <div>
                            <strong class="d-block" data-cover-status
                                data-current-status="{{ $post->cover_image_path ? 'Cover tersimpan dan tetap digunakan.' : 'Artikel ini belum memiliki cover.' }}">
                                {{ $post->cover_image_path ? 'Cover tersimpan dan tetap digunakan.' : 'Artikel ini belum memiliki cover.' }}
                            </strong>
                            <p class="small text-muted mt-1 mb-3">Upload file baru hanya jika Anda ingin
                                {{ $post->cover_image_path ? 'mengganti cover saat ini' : 'menambahkan cover' }}.</p>
                            <input class="visually-hidden" type="file" id="cover_image" name="cover_image"
                                accept="image/jpeg,image/png,image/webp" data-cover-input
                                aria-describedby="cover_image_name cover_image_help">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <label class="btn btn-sm btn-outline-dark mb-0" for="cover_image"><i
                                        class="bi bi-upload me-1"></i>{{ $post->cover_image_path ? 'Ganti cover' : 'Pilih cover' }}</label>
                                <span class="small text-muted text-break" id="cover_image_name" data-cover-filename
                                    aria-live="polite">Tidak ada file baru dipilih.</span>
                            </div>
                            <div class="form-text" id="cover_image_help">JPEG, PNG, atau WebP; maksimum 4 MB. Alt text
                                dibuat otomatis dari judul artikel.</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <label class="form-label mb-0">Isi artikel</label>
                        <span class="form-text mt-0 text-end">Alt text gambar dibuat otomatis; maksimum 4 MB.</span>
                    </div>
                    <input type="hidden" id="body_html" name="body_html" value="{{ old('body_html', $post->body_html) }}">
                    <div data-quill data-input="#body_html" data-upload-url="{{ route('cms.posts.media.store', $post) }}">
                    </div>
                </div>

                <button class="btn btn-primary mt-4">Simpan perubahan</button>
            </form>
        </div>
        <aside class="col-xl-4">
            <div class="cms-card p-4 mb-4">
                <h2 class="h4">Informasi</h2>
                <dl class="small mb-0">
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
            @can('admin')
                @if ($post->status === \App\Enums\PostStatus::PendingReview)
                    <form class="cms-card p-4 mb-4" method="post" action="{{ route('cms.posts.return', $post) }}">
                        @csrf
                        <h2 class="h4">Kembalikan ke Author</h2>
                        <label class="form-label" for="review_note">Catatan wajib</label>
                        <textarea class="form-control" id="review_note" name="review_note" rows="4" required></textarea>
                        <button class="btn btn-outline-danger mt-3">Kembalikan</button>
                    </form>
                @endif
            @endcan
            @can('delete', $post)
                <form method="post" action="{{ route('cms.posts.destroy', $post) }}"
                    onsubmit="return confirm('Pindahkan draft ini ke sampah?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-link text-danger px-0">Hapus artikel</button>
                </form>
            @endcan
        </aside>
    </div>
@endsection
