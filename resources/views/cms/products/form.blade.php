@extends('layouts.cms')

@section('title', $product->exists ? 'Edit produk' : 'Tambah produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Katalog" :title="$product->exists ? 'Edit produk' : 'Tambah produk'"
            description="Kelola informasi produk, visual, spesifikasi, dan visibilitasnya pada katalog publik." />
        <form class="cms-form-surface" method="post" enctype="multipart/form-data"
            action="{{ $product->exists ? route('cms.products.update', $product) : route('cms.products.store') }}">
            @csrf
            @if ($product->exists) @method('PUT') @endif
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Informasi utama</h2><p>Nama dan ringkasan membantu pelanggan memahami produk dengan cepat.</p></div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Kategori</label>
                        <select class="form-select @error('product_category_id') is-invalid @enderror" name="product_category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('product_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Nama produk</label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ringkasan</label>
                        <textarea class="form-control @error('summary') is-invalid @enderror" name="summary" rows="3" required>{{ old('summary', $product->summary) }}</textarea>
                        @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="6">{{ old('description', $product->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Spesifikasi</h2><p>Masukkan satu spesifikasi per baris dengan format Nama: Nilai.</p></div>
                <textarea class="form-control @error('specifications_text') is-invalid @enderror" name="specifications_text" rows="5" placeholder="Material: Kayu olahan&#10;Layanan: Konsultasi">{{ old('specifications_text', collect($product->specifications ?? [])->map(fn ($value, $key) => $key . ': ' . $value)->join("\n")) }}</textarea>
                @error('specifications_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Visual produk</h2><p>Gunakan gambar cover yang jelas dan alt text yang mendeskripsikan produk.</p></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Gambar cover</label>
                        <input class="form-control @error('cover_image') is-invalid @enderror" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
                        @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alt text gambar</label>
                        <input class="form-control @error('cover_image_alt') is-invalid @enderror" name="cover_image_alt" value="{{ old('cover_image_alt', $product->cover_image_alt) }}">
                        @error('cover_image_alt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Publikasi katalog</h2><p>Atur posisi dan penekanan produk di halaman publik.</p></div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Urutan</label>
                        <input class="form-control @error('sort_order') is-invalid @enderror" type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" required>
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 d-flex flex-wrap align-items-end gap-4 pb-2">
                        <div>
                            <label class="form-check mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
                                <span class="form-check-label">Aktif</span>
                            </label>
                            @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-check mb-0">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input @error('is_featured') is-invalid @enderror" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                                <span class="form-check-label">Produk unggulan</span>
                            </label>
                            @error('is_featured')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Simpan produk</button><a class="btn btn-outline-secondary" href="{{ route('cms.products.index') }}">Batal</a></div>
        </form>
        @if ($product->exists)
            <form class="cms-danger-action" method="post" action="{{ route('cms.products.destroy', $product) }}" data-cms-confirm-form data-confirm-variant="warning" data-confirm-title="Arsipkan produk?" data-confirm-message="Produk “{{ $product->name }}” tidak akan lagi tampil pada katalog publik." data-confirm-action="Arsipkan produk">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0" type="submit">Arsipkan produk</button></form>
        @endif
    </div>
@endsection
