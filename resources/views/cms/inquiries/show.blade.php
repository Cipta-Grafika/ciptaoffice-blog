@extends('layouts.cms')

@section('title', 'Inquiry ' . $inquiry->name)

@section('content')
    <div class="cms-page">
        <a class="cms-back-link" href="{{ route('cms.inquiries.index') }}"><i class="bi bi-arrow-left"></i> Daftar inquiry</a>
        <x-cms-page-header eyebrow="Pesan pelanggan" :title="$inquiry->name"
            description="Tinjau kebutuhan pelanggan dan perbarui progres tindak lanjutnya." />
        <div class="cms-detail-grid">
            <section class="cms-surface">
                <div class="cms-surface-header">
                    <div>
                        <p class="cms-surface-kicker mb-1">Detail kontak</p>
                        <h2 class="cms-surface-title mb-0">Informasi pelanggan</h2>
                    </div><span
                        class="cms-status cms-status--{{ ['new' => 'warning', 'contacted' => 'primary', 'closed' => 'success'][$inquiry->status] ?? '' }}">{{ ['new' => 'Baru', 'contacted' => 'Dihubungi', 'closed' => 'Selesai'][$inquiry->status] ?? $inquiry->status }}</span>
                </div>
                <div class="cms-surface-body">
                    <dl class="cms-detail-list">
                        <dt>Telepon</dt>
                        <dd>{{ $inquiry->phone }}</dd>
                        <dt>Email</dt>
                        <dd>{{ $inquiry->email ?? '—' }}</dd>
                        <dt>Produk</dt>
                        <dd>{{ $inquiry->product?->name ?? 'Kebutuhan umum' }}</dd>
                        <dt>Diterima</dt>
                        <dd>{{ $inquiry->created_at->translatedFormat('d M Y H:i') }}</dd>
                    </dl>
                    <div class="cms-message-block">
                        <p class="cms-surface-kicker mb-2">Pesan</p>
                        <p class="mb-0">{{ $inquiry->message }}</p>
                    </div>
                </div>
            </section>
            <form class="cms-form-surface" method="post" action="{{ route('cms.inquiries.update', $inquiry) }}">
                @csrf
                @method('PUT')
                <div class="cms-form-section">
                    <div class="cms-form-section-heading">
                        <h2>Tindak lanjut</h2>
                        <p>Catat progres komunikasi tim.</p>
                    </div><label class="form-label" for="status">Status</label><select class="form-select" id="status"
                        name="status">
                        <option value="new" @selected($inquiry->status === 'new')>Baru</option>
                        <option value="contacted" @selected($inquiry->status === 'contacted')>Sudah dihubungi</option>
                        <option value="closed" @selected($inquiry->status === 'closed')>Selesai</option>
                    </select>
                </div>
                <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Perbarui status</button></div>
            </form>
        </div>
    </div>
@endsection
