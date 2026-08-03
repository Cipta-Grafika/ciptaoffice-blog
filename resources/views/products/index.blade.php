@extends('layouts.app')
@section('title', 'Katalog Produk — CiptaOffice')
@section('meta_description', 'Katalog informatif meja, kursi, brankas, dan kebutuhan kantor dari CiptaOffice.')
@section('content')
    <header class="page-hero">
        <div class="container">
            <p class="section-kicker">Katalog informatif</p>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <h1 class="page-title">Produk untuk kerja yang lebih siap.</h1>
                </div>
                <div class="col-lg-4">
                    <p class="text-muted">Tanyakan ketersediaan kepada tim kami. Jika pilihan utama tidak tersedia, kami akan
                        mengusulkan alternatif berkualitas setara.</p>
                </div>
            </div>
        </div>
    </header>
    <section class="section-space section-space--compact-top">
        <div class="container">
            <form class="live-search mb-4" method="get" action="{{ route('products.index') }}"
                data-live-search-form data-live-search-delay="500">
                <input type="hidden" name="category" value="{{ $category }}" data-live-search-param
                    @disabled(!$category)>
                <div class="live-search-field"><label class="visually-hidden" for="q">Cari produk</label><input
                        class="form-control form-control-lg" id="q" name="q" type="search" value="{{ $q }}"
                        placeholder="Cari nama atau ringkasan produk..." autocomplete="off" data-live-search-input><button
                        class="live-search-clear" type="button" aria-label="Hapus pencarian" title="Hapus pencarian"
                        data-live-search-clear @if (!$q) hidden @endif><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <noscript><button class="btn btn-primary mt-2" type="submit">Cari</button></noscript>
            </form>
            <div class="live-search-results" data-live-search-results aria-busy="false">
                @include('products.partials.catalog')
            </div>
            <p class="visually-hidden" role="status" aria-live="polite" data-live-search-status></p>
        </div>
    </section>
@endsection
