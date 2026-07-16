@extends('layouts.app')
@section('title', 'Tentang CiptaOffice')
@section('meta_description', 'CiptaOffice membantu perusahaan memenuhi kebutuhan kantor melalui rekomendasi yang tepat
    dan alternatif berkualitas setara.')
@section('content')
    <header class="page-hero">
        <div class="container">
            <p class="section-kicker">Tentang CiptaOffice</p>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <h1 class="page-title">Pilihan yang tepat, bahkan ketika rencana berubah.</h1>
                </div>
                <div class="col-lg-4">
                    <p class="text-muted">Kami melihat pengadaan bukan sebagai daftar belanja, tetapi rangkaian keputusan
                        yang memengaruhi cara sebuah tim bekerja.</p>
                </div>
            </div>
        </div>
    </header>
    <section class="section-space">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <p class="section-kicker">Cara kami bekerja</p>
                    <h2 class="section-title">Lebih dari sekadar menyediakan barang.</h2>
                </div>
                <div class="col-lg-7">
                    <div class="prose m-0">
                        <p>CiptaOffice menyediakan meja, kursi, brankas, dan perlengkapan yang dibutuhkan sebuah kantor.
                            Kami memulai dari kebutuhan penggunaan, ukuran ruang, kualitas, serta batasan pengadaan Anda.
                        </p>
                        <p>Ketika produk utama tidak tersedia, pekerjaan tidak harus berhenti. Tim kami menyeleksi
                            alternatif dengan fungsi dan kualitas yang setara, lalu menjelaskan perbandingannya secara
                            jernih agar keputusan tetap dapat dipertanggungjawabkan.</p>
                        <h3>Tiga prinsip layanan</h3>
                        <ul>
                            <li>Kebutuhan bisnis lebih dahulu daripada katalog.</li>
                            <li>Alternatif harus memiliki alasan yang dapat dijelaskan.</li>
                            <li>Komunikasi pengadaan harus rapi dan responsif.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-space bg-white">
        <div class="container text-center">
            <p class="section-kicker">Mulai dari percakapan</p>
            <h2 class="section-title mx-auto" style="max-width:12ch">Ceritakan kebutuhan ruang kerja Anda.</h2><a
                class="btn btn-primary btn-lg px-5 mt-4" href="{{ route('contact.create') }}">Konsultasi kebutuhan</a>
        </div>
    </section>
@endsection
