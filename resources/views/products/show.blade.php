@extends('layouts.app')
@section('title', $product->name . ' — CiptaOffice')
@section('meta_description', $product->summary)
@section('canonical', route('products.show', $product))
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
                    <div class="card-visual" style="height:30rem">
                        @if ($product->cover_image_path)
                            <img src="{{ asset('storage/' . $product->cover_image_path) }}"
                            alt="{{ $product->cover_image_alt }}">@else<i class="bi bi-lamp display-1"></i>
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
