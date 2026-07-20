@extends('layouts.cms')
@section('title', 'Dashboard')
@section('content')<div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <p class="section-kicker mb-1">Ringkasan editorial</p>
            <h1 class="font-display display-5 mb-0">Dashboard</h1>
        </div><a class="btn btn-primary" href="{{ route('cms.posts.create') }}"><i class="bi bi-plus-lg"></i> Artikel baru</a>
    </div>
    <div class="row g-3">
        @foreach ($statuses as $status)
            <div class="col-sm-6 col-xl">
                <div class="cms-card p-3 h-100"><span
                        class="badge text-bg-{{ $status->badge() }}">{{ $status->label() }}</span>
                    <div class="stat-value">{{ $counts[$status->value] ?? 0 }}</div><span
                        class="small text-muted">artikel</span>
                </div>
            </div>
        @endforeach
    </div>
    @can('admin')
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <div class="cms-card p-4">
                    <p class="section-kicker">Reputasi</p>
                    <div class="stat-value">{{ $activeTestimonials }}</div>
                    <p class="mb-0">testimonial aktif di homepage</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="cms-card p-4">
                    <p class="section-kicker">Permintaan masuk</p>
                    <div class="stat-value">{{ $newInquiries }}</div>
                    <p class="mb-0">inquiry baru memerlukan tindak lanjut</p>
                </div>
            </div>
        </div>
    @endcan
@endsection
