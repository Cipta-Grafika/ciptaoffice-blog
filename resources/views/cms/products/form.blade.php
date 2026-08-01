@extends('layouts.cms')

@section('title', $product->exists ? 'Edit produk' : 'Tambah produk')

@section('cms-page', 'products-form')

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
            <section class="cms-form-section" data-product-gallery data-has-current="{{ $product->cover_image_path ? 'true' : 'false' }}">
                <div class="cms-form-section-heading">
                    <h2>Galeri produk</h2>
                    <p>Unggah maksimal delapan gambar, masing-masing maksimal 4 MB. Gambar pertama menjadi thumbnail; sisanya tampil berurutan di carousel.</p>
                </div>
                <div class="cms-product-gallery-field">
                    <div>
                        <label class="form-label" for="productImages">Gambar produk</label>
                        <input class="form-control @error('product_images') is-invalid @enderror"
                            id="productImages" type="file" name="product_images[]" multiple
                            accept="image/jpeg,image/png,image/webp" data-product-gallery-input>
                        @error('product_images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @foreach ($errors->get('product_images.*') as $messages)
                            @foreach ($messages as $message)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @endforeach
                        @endforeach
                        <p class="cms-product-gallery-help" data-product-gallery-status>
                            @if ($product->cover_image_path)
                                Tidak ada file baru dipilih. Galeri saat ini tetap digunakan sampai Anda memilih set baru.
                            @else
                                Pilih beberapa file sekaligus sesuai urutan tampilan yang diinginkan.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="cms-product-gallery-preview d-none" data-product-gallery-preview
                    aria-live="polite"></div>

                @if ($product->cover_image_path)
                    @php($galleryCount = $product->images->count() + 1)
                    @php($galleryOrder = old('gallery_order', ['thumbnail', ...$product->images->map(fn ($image) => 'image:' . $image->id)->all()]))
                    <div class="cms-product-gallery-current" data-product-gallery-current>
                        <div data-product-gallery-order-fields>
                            @foreach ((array) $galleryOrder as $galleryToken)
                                <input type="hidden" name="gallery_order[]" value="{{ $galleryToken }}">
                            @endforeach
                        </div>
                        @error('gallery_order')
                            <div class="alert alert-danger py-2 px-3 mb-3 small">{{ $message }}</div>
                        @enderror
                        <div class="cms-product-gallery-current-heading">
                            <div>
                                <span class="section-kicker">Galeri saat ini</span>
                                <p data-product-gallery-order-status>Tarik dan lepas untuk mengubah urutan. Gambar pertama menjadi thumbnail.</p>
                            </div>
                            <span>{{ $galleryCount }} gambar</span>
                        </div>
                        <div class="cms-product-gallery-grid" data-product-gallery-sortable>
                            <figure class="cms-product-gallery-item is-thumbnail" data-product-gallery-item
                                data-gallery-token="thumbnail" draggable="{{ $galleryCount > 1 ? 'true' : 'false' }}"
                                tabindex="0">
                                <span class="cms-product-gallery-drag-handle" aria-hidden="true"><i class="bi bi-grip-vertical"></i></span>
                                <img src="{{ asset('storage/' . $product->cover_image_path) }}"
                                    alt="{{ $product->cover_image_alt }}">
                                <button class="cms-product-gallery-delete" type="submit"
                                    form="delete-product-thumbnail-{{ $product->id }}"
                                    aria-label="Hapus thumbnail produk" title="Hapus thumbnail">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                                <figcaption>Thumbnail</figcaption>
                            </figure>
                            @foreach ($product->images as $image)
                                <figure class="cms-product-gallery-item" data-product-gallery-item
                                    data-gallery-token="image:{{ $image->id }}"
                                    draggable="{{ $galleryCount > 1 ? 'true' : 'false' }}" tabindex="0">
                                    <span class="cms-product-gallery-drag-handle" aria-hidden="true"><i class="bi bi-grip-vertical"></i></span>
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                        alt="{{ $image->alt_text }}">
                                    <button class="cms-product-gallery-delete" type="submit"
                                        form="delete-product-image-{{ $image->id }}"
                                        aria-label="Hapus gambar carousel {{ $loop->iteration }}"
                                        title="Hapus gambar carousel">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                    <figcaption><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>Carousel</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif
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
            @if ($product->cover_image_path)
                <form class="d-none" id="delete-product-thumbnail-{{ $product->id }}" method="post"
                    action="{{ route('cms.products.thumbnail.destroy', $product) }}" data-cms-confirm-form
                    data-confirm-variant="danger" data-confirm-title="Hapus thumbnail?"
                    data-confirm-message="{{ $product->images->isNotEmpty() ? 'Thumbnail akan dihapus dan gambar carousel pertama otomatis menjadi thumbnail baru.' : 'Thumbnail akan dihapus. Produk ini tidak akan memiliki gambar.' }}"
                    data-confirm-action="Hapus thumbnail">
                    @csrf @method('DELETE')
                </form>
                @foreach ($product->images as $image)
                    <form class="d-none" id="delete-product-image-{{ $image->id }}" method="post"
                        action="{{ route('cms.products.images.destroy', [$product, $image]) }}"
                        data-cms-confirm-form data-confirm-variant="danger"
                        data-confirm-title="Hapus gambar carousel?"
                        data-confirm-message="Gambar carousel ini akan dihapus permanen tanpa mengubah gambar lainnya."
                        data-confirm-action="Hapus gambar">
                        @csrf @method('DELETE')
                    </form>
                @endforeach
            @endif
            <form class="cms-danger-action" method="post" action="{{ route('cms.products.destroy', $product) }}" data-cms-confirm-form data-confirm-variant="warning" data-confirm-title="Arsipkan produk?" data-confirm-message="Produk “{{ $product->name }}” tidak akan lagi tampil pada katalog publik." data-confirm-action="Arsipkan produk">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0" type="submit">Arsipkan produk</button></form>
        @endif
    </div>
@endsection
