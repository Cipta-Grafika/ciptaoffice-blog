@extends('layouts.cms')

@section('title', 'Produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Katalog" title="Produk"
            description="Atur informasi produk, visibilitas, dan pilihan unggulan pada katalog publik.">
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar produk">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.products.partials.table')
            </div>
        </section>
    </div>
@endsection
