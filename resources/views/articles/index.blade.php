@extends('layouts.app')
@section('title', 'Artikel — CiptaOffice')
@section('meta_description', 'Wawasan CiptaOffice tentang furnitur, perlengkapan, dan pengadaan kebutuhan kantor.')
@section('content')
    <header class="page-hero">
        <div class="container">
            <p class="section-kicker">CiptaOffice Journal</p>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <h1 class="page-title">Wawasan untuk ruang kerja.</h1>
                </div>
                <div class="col-lg-4">
                    <p class="text-muted">Pertimbangan praktis untuk membuat keputusan pengadaan yang lebih jernih.</p>
                </div>
            </div>
        </div>
    </header>
    <section class="section-space section-space--compact-top">
        <div class="container">
            <form class="live-search mb-5" method="get" action="{{ route('articles.index') }}"
                data-live-search-form data-live-search-delay="500">
                <div class="live-search-field"><label class="visually-hidden" for="q">Cari artikel</label><input
                        class="form-control form-control-lg" id="q" name="q" type="search" value="{{ $q }}"
                        placeholder="Cari judul atau ringkasan..." autocomplete="off" data-live-search-input><button
                        class="live-search-clear" type="button" aria-label="Hapus pencarian" title="Hapus pencarian"
                        data-live-search-clear @if (!$q) hidden @endif><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <noscript><button class="btn btn-primary mt-2" type="submit">Cari</button></noscript>
            </form>
            <div class="live-search-results" data-live-search-results aria-busy="false">
                @include('articles.partials.catalog')
            </div>
            <p class="visually-hidden" role="status" aria-live="polite" data-live-search-status></p>
        </div>
    </section>
@endsection
