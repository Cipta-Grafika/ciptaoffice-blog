@extends('layouts.app')
@php($isPreview = $isPreview ?? false)
@section('title', ($isPreview ? 'Preview — ' : '') . $post->title . ' — CiptaOffice')
@section('meta_description', \Illuminate\Support\Str::limit($post->excerpt, 160))
@section('canonical', route('articles.show', $post))
@section('og_title', $post->title)
@section('og_type', 'article')
@if ($isPreview)
    @push('head')
        <meta name="robots" content="noindex,nofollow">
    @endpush
@endif
@section('content')
    @if ($isPreview)
        <div class="article-preview-bar">
            <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <strong class="d-block text-uppercase letter-space">Mode preview</strong>
                    <span>Menampilkan versi terakhir yang sudah disimpan. Artikel belum diterbitkan dari halaman ini.</span>
                </div>
                @can('update', $post)
                    <a class="btn btn-sm btn-outline-light" href="{{ route('cms.posts.edit', $post) }}">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke editor
                    </a>
                @else
                    <a class="btn btn-sm btn-outline-light" href="{{ route('cms.posts.index') }}">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar artikel
                    </a>
                @endcan
            </div>
        </div>
    @endif
    <article>
        <header class="page-hero {{ $isPreview ? 'page-hero--preview' : '' }}">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-9 text-center">
                        <p class="article-meta mb-4">
                            {{ $post->published_at?->translatedFormat('d F Y') ?? 'Belum diterbitkan' }} ·
                            {{ $post->author?->name ?? 'Tim CiptaOffice' }}</p>
                        <h1 class="page-title">{{ $post->title }}</h1>
                        <p class="lead text-muted mt-4">{{ $post->excerpt }}</p>
                    </div>
                </div>
            </div>
        </header>
        @if ($post->cover_image_path)
            <div class="container mt-4"><img class="w-100" style="max-height:38rem;object-fit:cover"
                    src="{{ asset('storage/' . $post->cover_image_path) }}" alt="{{ $post->cover_image_alt }}">
            </div>
        @endif
        <div class="section-space section-space--compact-top">
            <div class="container">
                <div class="article-body-layout" data-article-toc>
                    <div class="prose" data-article-content>{!! $post->body_html !!}</div>
                    <aside class="article-toc" aria-label="Daftar isi artikel">
                        <button class="article-toc-toggle" type="button" data-article-toc-toggle
                            aria-controls="article-toc-panel" aria-expanded="false">
                            <i class="bi bi-list-nested" aria-hidden="true"></i>
                            <span class="visually-hidden">Buka daftar isi</span>
                        </button>
                        <div class="article-toc-panel" id="article-toc-panel" data-article-toc-panel>
                            <p class="article-toc-kicker">Dalam artikel</p>
                            <p class="article-toc-title">Daftar isi</p>
                            <nav aria-label="Bagian artikel">
                                <ol class="article-toc-list" data-article-toc-list></ol>
                            </nav>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </article>
    @if ($latest->isNotEmpty())
        <aside class="section-space bg-white">
            <div class="container">
                <p class="section-kicker">Baca berikutnya</p>
                <h2 class="section-title mb-5">Artikel terbaru.</h2>
                <div class="row g-4">
                    @foreach ($latest as $item)
                        <div class="col-md-4">
                            <article class="article-card p-4">
                                <p class="article-meta">{{ $item->published_at->translatedFormat('d M Y') }}</p>
                                <h3 class="card-title card-text-clamp">
                                    <a class="stretched-link text-dark text-decoration-none"
                                        href="{{ route('articles.show', $item) }}">
                                        {{ $item->title }}
                                    </a>
                                </h3>
                                <p class="card-text-clamp text-muted mt-1">{{ $item->excerpt }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    @endif
@endsection
