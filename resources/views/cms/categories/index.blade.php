@extends('layouts.cms')

@section('title', 'Kategori produk')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Struktur katalog" title="Kategori"
            description="Susun kelompok produk agar katalog tetap mudah dijelajahi pelanggan.">
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar kategori produk">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.categories.partials.table')
            </div>
        </section>
    </div>
@endsection
