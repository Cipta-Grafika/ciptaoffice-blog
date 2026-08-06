<nav class="d-flex flex-wrap gap-2 mb-5" aria-label="Filter kategori">
    <a class="btn {{ !$category ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('products.index', array_filter(['q' => $q])) }}" data-live-search-link>Semua</a>
    @foreach ($categories as $item)
        <a class="btn {{ $category === $item->slug ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('products.index', array_filter(['category' => $item->slug, 'q' => $q])) }}" data-live-search-link>{{ $item->name }}</a>
    @endforeach
</nav>
@if ($products->isEmpty())
    <div class="empty-state">
        <h2>{{ $q ? 'Tidak ada produk ditemukan' : 'Produk belum tersedia' }}</h2>
        <p class="text-muted">{{ $q ? 'Coba kata kunci lain atau lihat kembali seluruh produk.' : 'Hubungi kami untuk mendiskusikan kebutuhan spesifik Anda.' }}</p>
        <a class="btn btn-primary" href="{{ route('contact.create') }}">Hubungi CiptaOffice</a>
    </div>
@else
    <div class="row g-4">
        @foreach ($products as $product)
            <div class="col-md-6 col-lg-4">
                <article class="product-card">
                    <div class="card-visual">
                        @if ($product->cover_image_path)
                            <img src="{{ asset('storage/' . $product->cover_image_path) }}"
                                alt="{{ $product->cover_image_alt }}">
                        @else
                            <i class="bi bi-lamp" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="article-meta">{{ $product->category->name }}</p>
                        <h2 class="card-title card-text-clamp"><a class="stretched-link text-dark text-decoration-none"
                                href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
                        <p class="card-text-clamp text-muted mt-1 mb-0">{{ $product->summary }}</p>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $products->links() }}</div>
@endif
