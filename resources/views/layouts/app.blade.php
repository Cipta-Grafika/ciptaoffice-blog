<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<body>
<nav class="navbar navbar-expand-lg fixed-top site-nav" aria-label="Navigasi utama">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}"><span class="brand-mark">CO</span><span>CiptaOffice</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#siteMenu" aria-controls="siteMenu" aria-expanded="false" aria-label="Buka navigasi"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="siteMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Tentang</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}">Artikel</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-primary px-4" href="{{ route('contact.create') }}">Konsultasi kebutuhan</a></li>
            </ul>
        </div>
    </div>
</nav>
<main>
    @if(session('success'))<div class="container position-relative" style="z-index:1031"><div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow" role="status">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>@endif
    @yield('content')
</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 align-items-end">
            <div class="col-lg-6"><div class="d-flex align-items-center gap-2 mb-3"><span class="brand-mark bg-light text-dark">CO</span><strong>CiptaOffice</strong></div><p class="mb-0 text-white-50">Partner pengadaan kebutuhan kantor dan alternatif produk berkualitas setara.</p></div>
            <div class="col-lg-6 text-lg-end"><p class="small text-uppercase letter-space text-white-50 mb-2">Informasi footer akan dilengkapi</p><p class="mb-0 small">&copy; {{ date('Y') }} CiptaOffice. Seluruh hak dilindungi.</p></div>
        </div>
    </div>
</footer>
@stack('scripts')
</body>
</html>
