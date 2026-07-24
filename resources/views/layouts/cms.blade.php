<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicons')
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
    @php
        $cmsUser = auth()->user();
        $cmsUserInitials = collect(preg_split('/\s+/', trim($cmsUser->name)))
            ->filter()
            ->take(2)
            ->map(fn ($name) => mb_strtoupper(mb_substr($name, 0, 1)))
            ->implode('');
    @endphp
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
        <header class="cms-topbar">
            <div class="cms-topbar-left">
                <button class="cms-topbar-icon-button cms-sidebar-toggle d-md-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#cmsSidebar" aria-controls="cmsSidebar"
                    aria-label="Buka navigasi CMS" title="Menu navigasi">
                    <x-cms-icon name="menu" />
                </button>
                <button class="cms-topbar-icon-button" type="button" data-cms-back
                    data-fallback-url="{{ route('cms.dashboard') }}" aria-label="Kembali ke halaman sebelumnya"
                    title="Kembali">
                    <x-cms-icon name="arrow-left" />
                </button>
                <span class="cms-topbar-divider" aria-hidden="true"></span>
                <strong class="cms-topbar-title">@yield('title', 'CMS')</strong>
            </div>
            <div class="cms-topbar-tools">
                <div class="dropdown">
                    <button class="cms-topbar-icon-button" id="cmsSearchMenuButton" type="button"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                        aria-label="Cari menu CMS" title="Cari menu">
                        <x-cms-icon name="search" />
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cms-topbar-popover cms-search-menu"
                        aria-labelledby="cmsSearchMenuButton">
                        <label class="form-label mb-2" for="cmsNavigationSearch">Cari menu CMS</label>
                        <div class="cms-search-field">
                            <x-cms-icon name="search" />
                            <input class="form-control" id="cmsNavigationSearch" type="search"
                                placeholder="Ketik nama menu..." autocomplete="off" data-cms-nav-search>
                        </div>
                        <div class="cms-search-results" data-cms-nav-search-results>
                            <a href="{{ route('cms.dashboard') }}" data-cms-nav-search-item>Dashboard</a>
                            <a href="{{ route('cms.posts.index') }}" data-cms-nav-search-item>Artikel</a>
                            @can('admin')
                                <a href="{{ route('cms.homepage.edit') }}" data-cms-nav-search-item>Homepage</a>
                                <a href="{{ route('cms.testimonials.index') }}" data-cms-nav-search-item>Testimonial</a>
                                <a href="{{ route('cms.products.index') }}" data-cms-nav-search-item>Produk</a>
                                <a href="{{ route('cms.categories.index') }}" data-cms-nav-search-item>Kategori</a>
                                <a href="{{ route('cms.inquiries.index') }}" data-cms-nav-search-item>Inquiry</a>
                                <a href="{{ route('cms.users.index') }}" data-cms-nav-search-item>Pengguna</a>
                            @endcan
                            <p class="cms-search-empty d-none mb-0" data-cms-nav-search-empty>Menu tidak ditemukan.</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown cms-quick-dropdown">
                    <button class="cms-topbar-icon-button" id="cmsQuickMenuButton" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka akses cepat"
                        title="Akses cepat">
                        <x-cms-icon name="grid" />
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cms-topbar-popover cms-quick-menu"
                        aria-labelledby="cmsQuickMenuButton">
                        <p class="cms-popover-kicker">Akses cepat</p>
                        <div class="cms-quick-grid">
                            <a href="{{ route('cms.dashboard') }}"><i class="bi bi-grid" aria-hidden="true"></i><span>Dashboard</span></a>
                            <a href="{{ route('cms.posts.index') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Artikel</span></a>
                            @can('admin')
                                <a href="{{ route('cms.products.index') }}"><i class="bi bi-box-seam" aria-hidden="true"></i><span>Produk</span></a>
                            @endcan
                            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"><i
                                    class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span>Lihat situs</span></a>
                        </div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="cms-topbar-icon-button" id="cmsNotificationMenuButton" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka notifikasi"
                        title="Notifikasi">
                        <x-cms-icon name="bell" />
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cms-topbar-popover cms-notification-menu"
                        aria-labelledby="cmsNotificationMenuButton">
                        <p class="cms-popover-kicker">Notifikasi</p>
                        <div class="cms-notification-empty">
                            <span><x-cms-icon name="bell" /></span>
                            <strong>Belum ada notifikasi</strong>
                            <small>Pembaruan workflow akan muncul di sini.</small>
                        </div>
                    </div>
                </div>
                <div class="dropdown cms-account-dropdown">
                    <button class="cms-topbar-icon-button cms-account-trigger" id="cmsAccountMenuButton"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka menu akun"
                        title="Akun">
                        <x-cms-icon name="person-circle" />
                    </button>
                    <div class="dropdown-menu dropdown-menu-end cms-account-menu"
                        aria-labelledby="cmsAccountMenuButton">
                        <div class="cms-account-user">
                            <span class="cms-account-avatar" aria-hidden="true">{{ $cmsUserInitials }}</span>
                            <span class="cms-account-identity">
                                <strong>{{ $cmsUser->name }}</strong>
                                <small>{{ $cmsUser->email }}</small>
                            </span>
                        </div>
                        <div class="cms-account-actions">
                            <button class="cms-account-menu-item" type="button" disabled>
                                <span class="cms-account-menu-icon"><i class="bi bi-person"
                                        aria-hidden="true"></i></span>
                                <span class="cms-account-menu-copy">
                                    <strong>Profil</strong>
                                    <small>Kelola informasi profil</small>
                                </span>
                                <i class="bi bi-chevron-right cms-account-menu-arrow" aria-hidden="true"></i>
                            </button>
                            <button class="cms-account-menu-item" type="button" disabled>
                                <span class="cms-account-menu-icon"><i class="bi bi-clock-history"
                                        aria-hidden="true"></i></span>
                                <span class="cms-account-menu-copy">
                                    <strong>Riwayat</strong>
                                    <small>Lihat aktivitas editorial</small>
                                </span>
                                <i class="bi bi-chevron-right cms-account-menu-arrow" aria-hidden="true"></i>
                            </button>
                            <button class="cms-account-menu-item" type="button" disabled>
                                <span class="cms-account-menu-icon"><i class="bi bi-gear"
                                        aria-hidden="true"></i></span>
                                <span class="cms-account-menu-copy">
                                    <strong>Pengaturan</strong>
                                    <small>Kelola preferensi akun</small>
                                </span>
                                <i class="bi bi-chevron-right cms-account-menu-arrow" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="cms-account-divider"></div>
                        <form method="post" action="{{ route('cms.logout') }}">
                            @csrf
                            <button class="cms-account-signout" type="submit">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
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
