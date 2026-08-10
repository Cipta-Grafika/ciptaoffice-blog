@extends('layouts.app')
@section('title', 'Hubungi CiptaOffice')
@section('meta_description', 'Konsultasikan kebutuhan meja, kursi, brankas, dan perlengkapan kantor Anda dengan
    CiptaOffice.')
@section('content')
    <header class="page-hero">
        <div class="container">
            <p class="section-kicker">Konsultasi kebutuhan</p>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <h1 class="page-title">Mulai dengan kebutuhan, bukan katalog.</h1>
                </div>
                <div class="col-lg-4">
                    <p class="text-muted">Berikan konteks ruang, jumlah, dan target waktu. Tim kami akan membantu
                        mempersempit pilihan.</p>
                </div>
            </div>
        </div>
    </header>
    <section class="section-space">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h2 class="font-display display-5">Yang akan kami bantu</h2>
                    <ul class="list-unstyled mt-4">
                        <li class="border-top py-3">
                            <i class="bi bi-check2 me-2 text-primary"></i>Memetakan spesifikasi
                        </li>
                        <li class="border-top py-3">
                            <i class="bi bi-check2 me-2 text-primary"></i>Mengecek ketersediaan
                        </li>
                        <li class="border-top py-3">
                            <i class="bi bi-check2 me-2 text-primary"></i>Menyiapkan alternatif setara
                        </li>
                    </ul>
                </div>
                <div class="col-lg-8">
                    <form class="cms-card p-4 p-lg-5" method="post" action="{{ route('contact.store') }}">@csrf
                        <div class="d-none" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">Periksa kembali data yang ditandai.</div>
                        @endif
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nama</label>
                                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Telepon/WhatsApp</label>
                                <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email (opsional)</label>
                                <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="product_id">Produk terkait</label>
                                <select class="form-select" id="product_id" name="product_id">
                                    <option value="">Kebutuhan umum</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected(old('product_id', request('product')) == $product->id)>
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label" for="message">Ceritakan kebutuhan</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="6"
                                    required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12"><button class="btn btn-primary btn-lg px-5">Kirim inquiry</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
