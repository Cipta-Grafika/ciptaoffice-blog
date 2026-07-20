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
            <form class="row g-2 mb-5" method="get">
                <div class="col-md-8"><label class="visually-hidden" for="q">Cari artikel</label><input
                        class="form-control form-control-lg" id="q" name="q" value="{{ $q }}"
                        placeholder="Cari judul atau ringkasan..."></div>
                <div class="col-md-2 d-grid"><button class="btn btn-primary">Cari</button></div>
                @if ($q)
                    <div class="col-md-2 d-grid"><a class="btn btn-outline-secondary"
                            href="{{ route('articles.index') }}">Reset</a></div>
                @endif
            </form>
            @if ($posts->isEmpty())
                <div class="empty-state">
                    <h2>Tidak ada artikel ditemukan</h2>
                    <p class="text-muted mb-0">Coba kata kunci lain atau lihat kembali seluruh artikel.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($posts as $post)
                        <div class="col-md-6 col-lg-4">
                            <article class="article-card">
                                <div class="card-visual"><i class="bi bi-journal-richtext"></i></div>
                                <div class="p-4">
                                    <p class="article-meta">{{ $post->published_at->translatedFormat('d M Y') }}</p>
                                    <h2 class="card-title card-text-clamp"><a
                                            class="stretched-link text-dark text-decoration-none"
                                            href="{{ route('articles.show', $post) }}">{{ $post->title }}</a></h2>
                                    <p class="card-text-clamp text-muted mt-1">{{ $post->excerpt }}</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
@endsection
