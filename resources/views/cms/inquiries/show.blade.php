@extends('layouts.cms')
@section('title', 'Inquiry ' . $inquiry->name)
@section('content')<a class="link-arrow" href="{{ route('cms.inquiries.index') }}"><i class="bi bi-arrow-left"></i> Daftar
        inquiry</a>
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="cms-card p-4">
                <p class="section-kicker">Pesan pelanggan</p>
                <h1 class="font-display display-5">{{ $inquiry->name }}</h1>
                <dl class="row">
                    <dt class="col-sm-3">Telepon</dt>
                    <dd class="col-sm-9">{{ $inquiry->phone }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $inquiry->email ?? '—' }}</dd>
                    <dt class="col-sm-3">Produk</dt>
                    <dd class="col-sm-9">{{ $inquiry->product?->name ?? 'Kebutuhan umum' }}</dd>
                </dl>
                <hr>
                <p style="white-space:pre-line">{{ $inquiry->message }}</p>
            </div>
        </div>
        <div class="col-lg-4">
            <form class="cms-card p-4" method="post" action="{{ route('cms.inquiries.update', $inquiry) }}">@csrf
                @method('PUT')<label class="form-label">Status tindak lanjut</label><select class="form-select mb-3"
                    name="status">
                    <option value="new" @selected($inquiry->status === 'new')>Baru</option>
                    <option value="contacted" @selected($inquiry->status === 'contacted')>Sudah dihubungi</option>
                    <option value="closed" @selected($inquiry->status === 'closed')>Selesai</option>
                </select><button class="btn btn-primary">Perbarui status</button></form>
        </div>
    </div>
@endsection
