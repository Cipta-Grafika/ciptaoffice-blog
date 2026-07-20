<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS') — CiptaOffice</title>
    <script>
        try {
            if (window.matchMedia('(min-width: 768px)').matches && localStorage.getItem('cms-sidebar-collapsed') === 'true') {
                document.documentElement.classList.add('cms-sidebar-collapsed');
            }
        } catch (error) {}
    </script>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="cms-body">
    <aside class="offcanvas-md offcanvas-start cms-sidebar" tabindex="-1" id="cmsSidebar"
        aria-labelledby="cmsSidebarLabel">
        <div class="cms-sidebar-header d-flex align-items-center justify-content-between gap-3 mb-4">
            <a class="cms-sidebar-brand d-flex align-items-center gap-2 text-white text-decoration-none"
                id="cmsSidebarLabel" href="{{ route('cms.dashboard') }}" data-cms-sidebar-brand
                aria-controls="cmsSidebar"><span class="brand-mark bg-light text-dark cms-brand-mark"><span
                        class="cms-brand-monogram">CO</span><i class="cms-brand-expand-icon bi bi-chevron-right"
                        aria-hidden="true"></i></span><strong class="cms-brand-label">CiptaOffice CMS</strong></a>
            <button class="btn btn-sm btn-outline-light cms-sidebar-collapse d-none d-md-grid" type="button"
                data-cms-sidebar-collapse aria-controls="cmsSidebar" aria-expanded="true"
                aria-label="Perkecil sidebar"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button class="btn-close btn-close-white d-md-none" type="button" data-bs-dismiss="offcanvas"
                data-bs-target="#cmsSidebar" aria-label="Tutup navigasi CMS"></button>
        </div>
        <p class="cms-nav-section small text-uppercase text-white-50 letter-space mb-2">Editorial</p>
        <nav class="nav flex-column gap-1"><a class="nav-link {{ request()->routeIs('cms.dashboard') ? 'active' : '' }}"
                href="{{ route('cms.dashboard') }}" title="Dashboard"><i class="bi bi-grid me-2"></i><span
                    class="cms-nav-label">Dashboard</span></a><a
                class="nav-link {{ request()->routeIs('cms.posts.*') ? 'active' : '' }}"
                href="{{ route('cms.posts.index') }}" title="Artikel"><i
                    class="bi bi-file-earmark-text me-2"></i><span class="cms-nav-label">Artikel</span></a>
            @can('admin')
                <a class="nav-link {{ request()->routeIs('cms.homepage.*') ? 'active' : '' }}"
                    href="{{ route('cms.homepage.edit') }}" title="Homepage"><i
                        class="bi bi-window me-2"></i><span class="cms-nav-label">Homepage</span></a><a
                    class="nav-link {{ request()->routeIs('cms.testimonials.*') ? 'active' : '' }}"
                    href="{{ route('cms.testimonials.index') }}" title="Testimonial"><i
                        class="bi bi-chat-quote me-2"></i><span class="cms-nav-label">Testimonial</span></a>
                <p class="cms-nav-section small text-uppercase text-white-50 letter-space mt-4 mb-2">Katalog & layanan</p><a
                    class="nav-link {{ request()->routeIs('cms.products.*') ? 'active' : '' }}"
                    href="{{ route('cms.products.index') }}" title="Produk"><i class="bi bi-box-seam me-2"></i><span
                        class="cms-nav-label">Produk</span></a><a
                    class="nav-link {{ request()->routeIs('cms.categories.*') ? 'active' : '' }}"
                    href="{{ route('cms.categories.index') }}" title="Kategori"><i class="bi bi-tags me-2"></i><span
                        class="cms-nav-label">Kategori</span></a><a
                    class="nav-link {{ request()->routeIs('cms.inquiries.*') ? 'active' : '' }}"
                    href="{{ route('cms.inquiries.index') }}" title="Inquiry"><i class="bi bi-inbox me-2"></i><span
                        class="cms-nav-label">Inquiry</span></a><a
                    class="nav-link {{ request()->routeIs('cms.users.*') ? 'active' : '' }}"
                    href="{{ route('cms.users.index') }}" title="Pengguna"><i class="bi bi-people me-2"></i><span
                        class="cms-nav-label">Pengguna</span></a>
            @endcan
        </nav>
    </aside>
    <div class="cms-main">
        <header class="cms-topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-dark cms-sidebar-toggle d-md-none" type="button"
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
