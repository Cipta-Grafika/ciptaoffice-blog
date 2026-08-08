@extends('layouts.cms')

@section('title', 'Dashboard')

@section('content')
    @php
        $totalArticles = collect($statuses)->sum(fn($status) => $counts[$status->value] ?? 0);
        $statusPresentation = [
            'draft' => [
                'icon' => 'bi-pencil-square',
                'tone' => 'draft',
                'description' => 'Artikel yang masih disusun.',
            ],
            'pending_review' => [
                'icon' => 'bi-clock-history',
                'tone' => 'review',
                'description' => 'Menunggu pemeriksaan editor.',
            ],
            'returned' => [
                'icon' => 'bi-arrow-return-left',
                'tone' => 'returned',
                'description' => 'Perlu diperbaiki oleh penulis.',
            ],
            'published' => [
                'icon' => 'bi-check2-circle',
                'tone' => 'published',
                'description' => 'Sudah tampil di situs.',
            ],
            'archived' => [
                'icon' => 'bi-archive',
                'tone' => 'archived',
                'description' => 'Disimpan dari publikasi aktif.',
            ],
        ];
    @endphp

    <div class="cms-dashboard">
        <header class="cms-dashboard-header">
            <div class="cms-dashboard-heading">
                <p class="cms-dashboard-eyebrow mb-2"><span aria-hidden="true"></span> Ringkasan editorial</p>
                <h1 class="cms-dashboard-title mb-2">Dashboard</h1>
                <p class="cms-dashboard-lead mb-0">Pantau alur artikel dan aktivitas terbaru CiptaOffice dalam satu tampilan.
                </p>
            </div>
            <a class="btn btn-primary cms-dashboard-primary-action fw-bold" href="{{ route('cms.posts.create') }}">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                <span>Artikel baru</span>
            </a>
        </header>

        <section class="cms-dashboard-section" aria-labelledby="editorial-status-title">
            <div class="cms-dashboard-section-heading">
                <div>
                    <p class="cms-dashboard-section-kicker mb-1">Workflow</p>
                    <h2 id="editorial-status-title" class="cms-dashboard-section-title mb-0">Status artikel</h2>
                </div>
                <p class="cms-dashboard-total mb-0"><strong>{{ $totalArticles }}</strong> artikel tercatat</p>
            </div>

            <div class="cms-dashboard-metrics">
                @foreach ($statuses as $status)
                    @php($presentation = $statusPresentation[$status->value])
                    <a href="{{ route('cms.posts.index', ['status' => $status->value]) }}" class="cms-dashboard-metric cms-dashboard-metric--{{ $presentation['tone'] }}">
                        <div class="cms-dashboard-metric-topline">
                            <span class="cms-dashboard-metric-icon" aria-hidden="true">
                                <i class="bi {{ $presentation['icon'] }}"></i>
                            </span>
                            <span class="cms-dashboard-status-dot" aria-hidden="true"></span>
                        </div>
                        <p class="cms-dashboard-metric-label mb-0">{{ $status->label() }}</p>
                        <p class="cms-dashboard-metric-value mb-0">{{ $counts[$status->value] ?? 0 }}</p>
                        <p class="cms-dashboard-metric-description mb-0">{{ $presentation['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        @can('admin')
            <section class="cms-dashboard-section" aria-labelledby="activity-title">
                <div class="cms-dashboard-section-heading">
                    <div>
                        <p class="cms-dashboard-section-kicker mb-1">Aktivitas</p>
                        <h2 id="activity-title" class="cms-dashboard-section-title mb-0">Perlu diperhatikan</h2>
                    </div>
                    <p class="cms-dashboard-total mb-0">Ringkasan kanal publik</p>
                </div>

                <div class="cms-dashboard-insights">
                    <a class="cms-dashboard-insight" href="{{ route('cms.testimonials.index') }}">
                        <span class="cms-dashboard-insight-icon" aria-hidden="true"><i class="bi bi-chat-quote"></i></span>
                        <span class="cms-dashboard-insight-copy">
                            <small>Reputasi</small>
                            <strong>{{ $activeTestimonials }} testimonial aktif</strong>
                            <span>Ditampilkan sebagai bukti kepercayaan di homepage.</span>
                        </span>
                        <i class="bi bi-arrow-up-right cms-dashboard-insight-arrow" aria-hidden="true"></i>
                    </a>

                    <a class="cms-dashboard-insight" href="{{ route('cms.inquiries.index') }}">
                        <span class="cms-dashboard-insight-icon" aria-hidden="true"><i class="bi bi-inbox"></i></span>
                        <span class="cms-dashboard-insight-copy">
                            <small>Permintaan masuk</small>
                            <strong>{{ $newInquiries }} inquiry baru</strong>
                            <span>Memerlukan tindak lanjut dari tim CiptaOffice.</span>
                        </span>
                        <i class="bi bi-arrow-up-right cms-dashboard-insight-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        @endcan
    </div>
@endsection
