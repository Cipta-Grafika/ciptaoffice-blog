@extends('layouts.cms')

@section('title', $category->exists ? 'Edit kategori' : 'Tambah kategori')

@section('content')
    <div class="cms-page cms-page--narrow">
        <a class="cms-back-link" href="{{ route('cms.categories.index') }}"><i class="bi bi-arrow-left"></i> Daftar kategori</a>
        <x-cms-page-header eyebrow="Struktur katalog" :title="$category->exists ? 'Edit kategori' : 'Tambah kategori'"
            description="Atur identitas, posisi, dan visibilitas kategori pada katalog produk." />
        <form class="cms-form-surface" method="post" action="{{ $category->exists ? route('cms.categories.update', $category) : route('cms.categories.store') }}">
            @csrf
            @if ($category->exists) @method('PUT') @endif
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Informasi kategori</h2><p>Slug dapat dibiarkan kosong agar dibuat otomatis dari nama.</p></div>
                <div class="row g-3">
                    <div class="col-md-7"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name', $category->name) }}" required></div>
                    <div class="col-md-5"><label class="form-label">Slug (opsional)</label><input class="form-control" name="slug" value="{{ old('slug', $category->slug) }}"></div>
                    <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="4">{{ old('description', $category->description) }}</textarea></div>
                    <div class="col-md-4"><label class="form-label">Urutan</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" required></div>
                    <div class="col-md-8 d-flex align-items-end pb-2"><label class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))><span class="form-check-label">Kategori aktif</span></label></div>
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Simpan kategori</button><a class="btn btn-outline-secondary" href="{{ route('cms.categories.index') }}">Batal</a></div>
        </form>
        @if ($category->exists)
            <form class="cms-danger-action" method="post" action="{{ route('cms.categories.destroy', $category) }}" data-cms-confirm-form data-confirm-variant="danger" data-confirm-title="Hapus kategori?" data-confirm-message="Kategori “{{ $category->name }}” akan dihapus dari struktur katalog." data-confirm-action="Hapus kategori">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0" type="submit">Hapus kategori</button></form>
        @endif
    </div>
@endsection
