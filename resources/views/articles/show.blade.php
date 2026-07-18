@extends('layouts.app')
@section('title', $post->title . ' — CiptaOffice')
@section('meta_description', $post->excerpt)
@section('canonical', route('articles.show', $post))
@section('og_title', $post->title)
@section('og_type', 'article')
@section('content')
    <article>
        <header class="page-hero">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-9 text-center">
                        <p class="article-meta mb-4">{{ $post->published_at->translatedFormat('d F Y') }} ·
                            {{ $post->author?->name ?? 'Tim CiptaOffice' }}</p>
                        <h1 class="page-title">{{ $post->title }}</h1>
                        <p class="lead text-muted mt-4">{{ $post->excerpt }}</p>
                    </div>
                </div>
            </div>
        </header>
        @if ($post->cover_image_path)
            <div class="container mt-5"><img class="w-100" style="max-height:38rem;object-fit:cover"
                    src="{{ Storage::disk('public')->url($post->cover_image_path) }}" alt="{{ $post->cover_image_alt }}">
            </div>
        @endif
        <div class="section-space">
            <div class="container">
                <div class="prose">{!! $post->body_html !!}</div>
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
                                <h3 class="card-title card-text-clamp"><a class="stretched-link text-dark text-decoration-none"
                                        href="{{ route('articles.show', $item) }}">{{ $item->title }}</a></h3>
                                <p class="card-text-clamp text-muted mt-1">{{ $item->excerpt }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    @endif
@endsection
