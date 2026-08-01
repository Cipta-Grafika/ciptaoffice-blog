@extends('layouts.app')
@section('title', $product->name . ' — CiptaOffice')
@section('meta_description', $product->summary)
@section('canonical', route('products.show', $product))
@if ($product->cover_image_path)
    @section('og_image', asset('storage/' . $product->cover_image_path))
    @section('og_image_alt', $product->cover_image_alt ?: $product->name)
@endif
@section('content')
    <section class="page-hero">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <p class="section-kicker">{{ $product->category->name }}</p>
                    <h1 class="page-title">{{ $product->name }}</h1>
                    <p class="lead text-muted mt-4">{{ $product->summary }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-4"><a class="btn btn-primary btn-lg" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp me-2"></i>Tanyakan ketersediaan</a><a class="btn btn-outline-dark btn-lg" href="{{ route('contact.create', ['product' => $product->id]) }}">Kirim inquiry</a></div>
                </div>
                <div class="col-lg-6">
                    @php($galleryCount = ($product->cover_image_path ? 1 : 0) + $product->images->count())
                    <div id="productGallery{{ $product->id }}" class="carousel slide product-gallery"
                        data-bs-touch="true" aria-label="Galeri {{ $product->name }}">
                        <div class="carousel-inner card-visual product-gallery-stage">
                            @if ($product->cover_image_path)
                                <div class="carousel-item active">
                                    <img src="{{ asset('storage/' . $product->cover_image_path) }}"
                                        alt="{{ $product->cover_image_alt }}">
                                    @if ($galleryCount > 1)
                                        <span class="product-gallery-index">01 / {{ str_pad($galleryCount, 2, '0', STR_PAD_LEFT) }}</span>
                                    @endif
                                </div>
                                @foreach ($product->images as $image)
                                    <div class="carousel-item">
                                        <img src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $image->alt_text }}" loading="lazy">
                                        <span class="product-gallery-index">{{ str_pad($loop->iteration + 1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($galleryCount, 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="carousel-item active">
                                    <div class="product-gallery-placeholder">
                                        <i class="bi bi-lamp display-1" aria-hidden="true"></i>
                                        <span>Visual produk segera tersedia</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($galleryCount > 1)
                            <div class="carousel-indicators product-gallery-indicators">
                                @for ($index = 0; $index < $galleryCount; $index++)
                                    <button type="button" data-bs-target="#productGallery{{ $product->id }}"
                                        data-bs-slide-to="{{ $index }}" @class(['active' => $index === 0])
                                        @if ($index === 0) aria-current="true" @endif
                                        aria-label="Tampilkan gambar {{ $index + 1 }}"></button>
                                @endfor
                            </div>
                            <button class="carousel-control-prev product-gallery-control" type="button"
                                data-bs-target="#productGallery{{ $product->id }}" data-bs-slide="prev">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                <span class="visually-hidden">Gambar sebelumnya</span>
                            </button>
                            <button class="carousel-control-next product-gallery-control" type="button"
                                data-bs-target="#productGallery{{ $product->id }}" data-bs-slide="next">
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                <span class="visually-hidden">Gambar berikutnya</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-space">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-7">
                    <p class="section-kicker">Tentang produk</p>
                    <div class="prose m-0">
                        <p>{{ $product->description }}</p>
                    </div>
                </div>
                <div class="col-lg-5">
                    @if ($product->specifications)
                        <div class="cms-card p-4">
                            <h2 class="h3">Informasi</h2>
                            <dl class="mb-0">
                                @foreach ($product->specifications as $key => $value)
                                    <div class="border-top py-3">
                                        <dt class="small text-muted">{{ $key }}</dt>
                                        <dd class="mb-0">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
