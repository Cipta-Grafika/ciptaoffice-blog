<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicons')
    <title>@yield('title', 'CiptaOffice — Solusi Kebutuhan Kantor')</title>
    <meta name="description" content="@yield('meta_description', 'Furnitur dan perlengkapan kantor dengan rekomendasi alternatif berkualitas setara dari CiptaOffice.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', 'CiptaOffice — Solusi Kebutuhan Kantor')">
    <meta property="og:description" content="@yield('meta_description', 'Furnitur dan perlengkapan kantor dengan rekomendasi alternatif berkualitas setara.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('head')
</head>

@php
    $contactPhone = config('ciptaoffice.contact_phone');
    $contactEmail = config('ciptaoffice.contact_email');
    $contactPhoneHref = preg_replace('/[^\d+]/', '', $contactPhone);
@endphp

<body data-app-context="site">
    <header class="site-header" data-site-header>
        <div class="site-top-header">
            <div class="container d-flex align-items-center justify-content-between gap-3">
                <a class="top-header-logo" href="{{ route('home') }}" aria-label="CiptaOffice — Beranda">
                    <img class="top-header-logo-image" src="{{ asset('images/logos/ciptaoffice-logo-brand.png') }}"
                        width="867" height="60" alt="CiptaOffice">
                </a>
                <div class="site-contact-list" aria-label="Kontak CiptaOffice">
                    <a href="tel:{{ $contactPhoneHref }}">
                        <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                        <span class="site-contact-text">{{ $contactPhone }}</span>
                        <span class="visually-hidden d-md-none">Telepon CiptaOffice</span>
                    </a>
                    <a href="mailto:{{ $contactEmail }}">
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <span class="site-contact-text">{{ $contactEmail }}</span>
                        <span class="visually-hidden d-md-none">Email CiptaOffice</span>
                    </a>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg site-nav" aria-label="Navigasi utama">
            <div class="container py-2">
                <div class="site-nav-context" aria-label="Bidang layanan CiptaOffice">
                    <span class="site-nav-context-line" aria-hidden="true"></span>
                    <span>Solusi kebutuhan kantor</span>
                </div>
                <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse"
                    data-bs-target="#siteMenu" aria-controls="siteMenu" aria-expanded="false"
                    aria-label="Buka navigasi"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="siteMenu">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                                href="{{ route('home') }}">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}"
                                href="{{ route('about') }}">Tentang</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                                href="{{ route('products.index') }}">Produk</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('articles.*', 'cms.posts.preview') ? 'active' : '' }}"
                                href="{{ route('articles.index') }}">Artikel</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary px-4"
                                href="{{ route('contact.create') }}">Konsultasi kebutuhan</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
        @if (session('success'))
            <div class="container position-relative" style="z-index:1031">
                <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow"
                    role="status">{{ session('success') }}<button type="button" class="btn-close"
                        data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @yield('content')
    </main>
    @include('partials.site-footer')
    @stack('scripts')
</body>

</html>
