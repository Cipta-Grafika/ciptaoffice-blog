<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#f7f3eb">
    @include('partials.favicons')
    <title>Halaman Tidak Ditemukan — CiptaOffice</title>
    @vite(['resources/scss/app.scss'])
</head>

<body class="error-page">
    <main class="error-page-shell" aria-labelledby="error-page-title">
        <a class="error-page-brand" href="{{ route('home') }}" aria-label="CiptaOffice — Beranda">
            <img src="{{ asset('images/logos/ciptaoffice-brand.svg') }}" width="867" height="60" alt="CiptaOffice">
        </a>

        <section class="error-page-content">
            <div class="error-page-code" aria-label="Error 404">
                <strong aria-hidden="true">404</strong>
                <span aria-hidden="true">Ruang tak ditemukan</span>
            </div>

            <p class="error-page-eyebrow">Jalur terputus</p>
            <h1 id="error-page-title">Halaman tidak ditemukan.</h1>
            <p class="error-page-description">
                Alamat yang Anda tuju mungkin sudah dipindahkan, dihapus, atau belum pernah tersedia.
                Mari kembali ke ruang yang tepat.
            </p>

            <div class="error-page-actions" aria-label="Pilihan navigasi">
                <a class="error-page-button error-page-button--secondary" href="{{ route('home') }}"
                    data-error-page-back data-fallback-url="{{ route('home') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Kembali
                </a>
                <a class="error-page-button error-page-button--primary" href="{{ route('home') }}">
                    <i class="bi bi-house-door" aria-hidden="true"></i>
                    Ke beranda
                </a>
            </div>

            <div class="error-page-support">
                <p>Butuh bantuan menemukan informasi?</p>
                <a href="mailto:{{ config('ciptaoffice.contact_email') }}">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    {{ config('ciptaoffice.contact_email') }}
                </a>
            </div>
        </section>

        <p class="error-page-coordinate" aria-hidden="true">CO / 404 · Ruang tidak terpetakan</p>
    </main>

    <script>
        document.querySelector('[data-error-page-back]')?.addEventListener('click', (event) => {
            if (window.history.length <= 1) return;
            event.preventDefault();
            window.history.back();
        });
    </script>
</body>

</html>
