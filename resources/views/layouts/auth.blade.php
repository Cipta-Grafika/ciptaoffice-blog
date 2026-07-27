<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicons')
    <title>@yield('title', 'Masuk CMS') — CiptaOffice</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body data-app-context="auth">
    <main class="auth-shell d-flex align-items-center justify-content-center p-3">
        <div class="auth-card w-100">
            <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark mb-5"
                href="{{ route('home') }}">
                <span class="brand-mark">CO</span>
                <strong>CiptaOffice</strong>
            </a>

            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</body>

</html>
