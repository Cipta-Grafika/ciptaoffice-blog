@extends('layouts.cms')

@section('title', 'Access Denied')

@section('cms-page', 'error-forbidden')

@section('content')
    <main class="cms-access-denied" aria-labelledby="access-denied-title">
        <div class="cms-access-denied-panel">
            <div class="cms-access-denied-status" aria-label="Error 403">
                <strong>403</strong>
                <span>Area terbatas</span>
            </div>

            <div class="cms-access-denied-visual" aria-hidden="true">
                <span
                    class="cms-access-denied-icon"
                    style="--access-denied-icon: url('{{ asset('images/icons/no-access-line.svg') }}')"
                ></span>
            </div>

            <div class="cms-access-denied-copy">
                <p class="cms-access-denied-eyebrow"><span aria-hidden="true"></span> Hak akses diperlukan</p>
                <h1 id="access-denied-title">Access Denied</h1>
                <p>
                    Anda tidak memiliki izin untuk membuka halaman ini. Silakan kembali ke area kerja Anda atau hubungi
                    administrator jika akses tersebut memang diperlukan.
                </p>
            </div>

            <div class="cms-access-denied-actions">
                <button class="btn btn-primary" type="button" data-cms-back
                    data-fallback-url="{{ route('cms.dashboard') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Kembali
                </button>
                <a class="btn btn-outline-dark" href="{{ route('cms.dashboard') }}">
                    <i class="bi bi-grid" aria-hidden="true"></i>
                    Dashboard
                </a>
            </div>

            <p class="cms-access-denied-footnote">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                Sistem tetap melindungi modul yang tidak termasuk dalam hak akses akun Anda.
            </p>
        </div>
    </main>
@endsection
