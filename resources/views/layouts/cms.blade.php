<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS') — CiptaOffice</title>@vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="cms-body">
    <aside class="offcanvas-lg offcanvas-start cms-sidebar" tabindex="-1" id="cmsSidebar"
        aria-labelledby="cmsSidebarLabel">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <a class="d-flex align-items-center gap-2 text-white text-decoration-none" id="cmsSidebarLabel"
                href="{{ route('cms.dashboard') }}"><span
                    class="brand-mark bg-light text-dark">CO</span><strong>CiptaOffice CMS</strong></a>
            <button class="btn-close btn-close-white d-lg-none" type="button" data-bs-dismiss="offcanvas"
                data-bs-target="#cmsSidebar" aria-label="Tutup navigasi CMS"></button>
        </div>
        <p class="small text-uppercase text-white-50 letter-space mb-2">Editorial</p>
        <nav class="nav flex-column gap-1"><a class="nav-link {{ request()->routeIs('cms.dashboard') ? 'active' : '' }}"
                href="{{ route('cms.dashboard') }}"><i class="bi bi-grid me-2"></i>Dashboard</a><a
                class="nav-link {{ request()->routeIs('cms.posts.*') ? 'active' : '' }}"
                href="{{ route('cms.posts.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Artikel</a>
            @can('admin')
                <a class="nav-link {{ request()->routeIs('cms.homepage.*') ? 'active' : '' }}"
                    href="{{ route('cms.homepage.edit') }}"><i class="bi bi-window me-2"></i>Homepage</a><a
                    class="nav-link {{ request()->routeIs('cms.testimonials.*') ? 'active' : '' }}"
                    href="{{ route('cms.testimonials.index') }}"><i class="bi bi-chat-quote me-2"></i>Testimonial</a>
                <p class="small text-uppercase text-white-50 letter-space mt-4 mb-2">Katalog & layanan</p><a
                    class="nav-link {{ request()->routeIs('cms.products.*') ? 'active' : '' }}"
                    href="{{ route('cms.products.index') }}"><i class="bi bi-box-seam me-2"></i>Produk</a><a
                    class="nav-link {{ request()->routeIs('cms.categories.*') ? 'active' : '' }}"
                    href="{{ route('cms.categories.index') }}"><i class="bi bi-tags me-2"></i>Kategori</a><a
                    class="nav-link {{ request()->routeIs('cms.inquiries.*') ? 'active' : '' }}"
                    href="{{ route('cms.inquiries.index') }}"><i class="bi bi-inbox me-2"></i>Inquiry</a><a
                    class="nav-link {{ request()->routeIs('cms.users.*') ? 'active' : '' }}"
                    href="{{ route('cms.users.index') }}"><i class="bi bi-people me-2"></i>Pengguna</a>
            @endcan
        </nav>
    </aside>
    <div class="cms-main">
        <header class="cms-topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-dark cms-sidebar-toggle d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#cmsSidebar" aria-controls="cmsSidebar"
                    aria-label="Buka navigasi CMS"><i class="bi bi-list fs-5" aria-hidden="true"></i></button>
                <div><span class="small text-muted">{{ auth()->user()->role->label() }}</span><strong
                        class="d-block">{{ auth()->user()->name }}</strong></div>
            </div>
            <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('home') }}"
                    target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat situs</a>
                <div class="dropdown"><button class="btn btn-sm btn-outline-dark dropdown-toggle"
                        data-bs-toggle="dropdown">Akun</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('cms.password.edit') }}">Ganti kata sandi</a></li>
                        <li>
                            <form method="post" action="{{ route('cms.logout') }}">@csrf<button
                                    class="dropdown-item text-danger">Keluar</button></form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><strong>Periksa kembali:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</body>

</html>
