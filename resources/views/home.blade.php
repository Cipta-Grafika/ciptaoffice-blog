@extends('layouts.app')
@section('content')
    <section class="hero">
        <div class="container">
            <div class="hero-layout">
                <div class="hero-intro reveal">
                    <!-- <p class="section-kicker hero-kicker">{{ $settings->eyebrow }}</p> -->
                    <h1 class="hero-title font-display">{{ $settings->title }}</h1>
                    <p class="hero-copy">{{ $settings->summary }}</p>
                    <div class="hero-actions">
                        @if ($settings->primary_cta_label)
                            <a class="btn hero-cta hero-cta--primary"
                                href="{{ $settings->primary_cta_url ?: '#produk' }}">
                                <span>{{ $settings->primary_cta_label }}</span>
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if ($settings->secondary_cta_label)
                            <a class="btn hero-cta hero-cta--secondary"
                                href="{{ $settings->secondary_cta_url ?: route('contact.create') }}">
                                <span>{{ $settings->secondary_cta_label }}</span>
                            </a>
                        @endif
                    </div>

                    <p class="hero-categories" aria-label="Kategori produk utama">
                        <span>Meja kantor</span><span>Kursi kantor</span><span>Brankas</span>
                    </p>
                </div>

                <figure class="hero-media reveal">
                    <div class="hero-media-frame">
                        @if ($settings->hero_image_path)
                            <img class="hero-image" src="{{ asset('storage/' . $settings->hero_image_path) }}"
                                alt="{{ $settings->hero_image_alt }}" fetchpriority="high" decoding="async">
                        @else
                            <div class="hero-image hero-image--placeholder" role="img"
                                aria-label="Palet material furnitur kantor berwarna hijau, brass, dan kayu">
                                <div class="swatch mx-4" aria-hidden="true"></div>
                            </div>
                        @endif
                        <span class="hero-media-badge"><i class="bi bi-stars" aria-hidden="true"></i> Kurasi ruang
                            kerja</span>
                    </div>
                    <figcaption class="hero-media-caption">
                        <span></span>
                        <span>Karawang · Jawa Barat</span>
                    </figcaption>
                </figure>

                <form class="hero-search reveal" method="get" action="{{ route('products.index') }}"
                    aria-label="Cari kebutuhan produk kantor">
                    <div class="hero-search-heading">
                        <div>
                            <span class="hero-search-index">Temukan kebutuhan</span>
                            <strong>Mulai dari ruang Anda</strong>
                        </div>
                        <i class="bi bi-grid-1x2" aria-hidden="true"></i>
                    </div>
                    <div class="hero-search-fields">
                        <label class="hero-search-field" for="hero_category">
                            <span>Kategori</span>
                            <span class="hero-search-control hero-search-control--select">
                                <i class="bi bi-collection" aria-hidden="true"></i>
                                <select id="hero_category" name="category">
                                    <option value="">Semua produk</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <i class="bi bi-chevron-down hero-search-chevron" aria-hidden="true"></i>
                            </span>
                        </label>
                        <label class="hero-search-field" for="hero_query">
                            <span>Kata kunci</span>
                            <span class="hero-search-control">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input id="hero_query" name="q" type="search" placeholder="Contoh: meja kerja">
                            </span>
                        </label>
                        <button class="hero-search-submit" type="submit">
                            <span>Cari produk</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <div class="metric-strip-sentinel" data-metric-strip-sentinel aria-hidden="true"></div>
    <nav class="metric-strip" data-metric-strip aria-label="Navigasi bagian beranda">
        <div class="container">
            <div class="metric-strip-grid">
                <div class="metric-item">
                    <div class="metric-strip-content metric-copy"><span class="metric-number">01</span><span>Kebutuhan
                            dipetakan</span></div>
                    <a class="metric-strip-content metric-section-link" href="#produk" tabindex="-1"
                        aria-hidden="true"><span class="metric-nav-index">01</span><span>Produk</span></a>
                </div>
                <div class="metric-item">
                    <div class="metric-strip-content metric-copy"><span class="metric-number">02</span><span>Pilihan
                            dibandingkan</span></div>
                    <a class="metric-strip-content metric-section-link" href="#artikel" tabindex="-1"
                        aria-hidden="true"><span class="metric-nav-index">02</span><span>Artikel</span></a>
                </div>
                <div class="metric-item">
                    <div class="metric-strip-content metric-copy"><span class="metric-number">03</span><span>Alternatif
                            disiapkan</span></div>
                    <a class="metric-strip-content metric-section-link" href="#testimoni" tabindex="-1"
                        aria-hidden="true"><span class="metric-nav-index">03</span><span>Testimoni</span></a>
                </div>
            </div>
        </div>
    </nav>
    <section class="section-space home-product-section" id="produk">
        <div class="container">
            <div class="row align-items-end mb-5 reveal">
                <div class="col-lg-8 home-section-heading">
                    <p class="section-kicker">Ruang kerja, disusun dengan pertimbangan</p>
                    <h2 class="section-title mb-0">Kebutuhan utama kantor.</h2>
                </div>
                <div class="col-lg-4 text-lg-end mt-3"><a class="link-arrow" href="{{ route('products.index') }}">Lihat
                        seluruh produk <i class="bi bi-arrow-right"></i></a></div>
            </div>
            @if ($categories->isEmpty())
                <div class="empty-state">
                    <h3>Produk sedang disiapkan</h3>
                    <p class="text-muted mb-0">Tim kami sedang menyusun pilihan kebutuhan kantor terbaik.</p>
            </div>@else<div class="row g-3">
                    @foreach ($categories as $category)
                        @php
                            $icon = match($category->slug) {
                                'meja-kantor' => 'desk.svg',
                                'kursi-kantor' => 'chair-outline.svg',
                                'brankas' => 'safe-outline.svg',
                                default => 'desk.svg',
                            };
                            $iconPath = public_path('images/icons/' . $icon);
                        @endphp
                        <div class="col-md-4 reveal">
                            <a class="category-tile d-flex flex-column justify-content-end"
                                href="{{ route('products.index', ['category' => $category->slug]) }}">
                                @if(file_exists($iconPath))
                                    <div class="category-icon-wrapper" aria-hidden="true">
                                        {!! file_get_contents($iconPath) !!}
                                    </div>
                                @endif
                                <span class="section-kicker text-white-50 position-relative z-2">0{{ $loop->iteration }}</span>
                                <h3 class="font-display display-6 mb-2 position-relative z-2">{{ $category->name }}</h3>
                                <p class="small mb-0 text-white-50 position-relative z-2">{{ $category->description }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <section class="section-space bg-white" id="artikel">
        <div class="container">
            <div class="row align-items-end mb-5 reveal">
                <div class="col-lg-8 home-section-heading">
                    <p class="section-kicker">Wawasan ruang kerja</p>
                    <h2 class="section-title mb-0">Latest Article.</h2>
                </div>
                <div class="col-lg-4 text-lg-end mt-3"><a class="link-arrow" href="{{ route('articles.index') }}">Arsip
                        artikel <i class="bi bi-arrow-right"></i></a></div>
            </div>
            @if ($posts->isEmpty())
                <div class="empty-state">
                    <h3>Artikel pertama sedang disiapkan</h3>
                    <p class="text-muted mb-0">Kembali lagi untuk membaca wawasan terbaru CiptaOffice.</p>
            </div>@else<div class="row g-4">
                    @foreach ($posts as $post)
                        <div class="col-lg-4 reveal">
                            <article class="article-card">
                                @include('articles.partials.card-visual', ['post' => $post])
                                <div class="p-4">
                                    <p class="article-meta mb-3">{{ $post->published_at->translatedFormat('d M Y') }} ·
                                        {{ $post->author?->name ?? 'Tim CiptaOffice' }}</p>
                                    <h3 class="card-title card-text-clamp mb-3">
                                        <a class="stretched-link text-decoration-none text-dark"
                                            href="{{ route('articles.show', $post) }}">{{ $post->title }}
                                        </a>
                                    </h3>
                                    <p class="card-text-clamp text-muted mt-1 mb-0">{{ $post->excerpt }}</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <section class="testimonial-section section-space" id="testimoni">
        <div class="container">
            <div class="row mb-5 reveal">
                <div class="col-lg-8 home-section-heading">
                    <p class="section-kicker">Kepercayaan yang dibangun</p>
                    <h2 class="section-title">Pengadaan yang terasa lebih pasti.</h2>
                </div>
            </div>
            @if ($testimonials->isEmpty())
                <div class="empty-state bg-transparent border-secondary text-white">
                    <h3>Ulasan akan segera hadir</h3>
                    <p class="text-white-50 mb-0">Testimonial terverifikasi akan ditampilkan di bagian ini.</p>
            </div>@else<div class="row g-4">
                    @foreach ($testimonials as $testimonial)
                        <div class="col-lg-4 reveal">
                            <figure class="quote-card mb-0">
                                <div class="quote-mark" aria-hidden="true">“</div>
                                <blockquote>{{ $testimonial->quote }}</blockquote>
                                <figcaption class="quote-card-author">
                                    @if ($testimonial->avatar_path)
                                        <img class="quote-card-avatar"
                                            src="{{ asset('storage/' . $testimonial->avatar_path) }}"
                                            alt="{{ $testimonial->avatar_alt ?: $testimonial->reviewer_name }}">
                                    @else
                                        <span class="quote-card-avatar quote-card-avatar--fallback" aria-hidden="true">
                                            {{ Str::upper(Str::substr($testimonial->reviewer_name, 0, 1)) }}
                                        </span>
                                    @endif
                                    <span class="quote-card-author-copy">
                                        <strong class="quote-card-author-name">{{ $testimonial->reviewer_name }}</strong>
                                        @if ($testimonial->reviewer_title || $testimonial->company)
                                            <span class="quote-card-author-meta">
                                                @if ($testimonial->reviewer_title)
                                                    <span>{{ $testimonial->reviewer_title }}</span>
                                                @endif
                                                @if ($testimonial->reviewer_title && $testimonial->company)
                                                    <span class="quote-card-author-separator" aria-hidden="true">·</span>
                                                @endif
                                                @if ($testimonial->company)
                                                    <span>{{ $testimonial->company }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
