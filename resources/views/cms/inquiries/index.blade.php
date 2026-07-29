@extends('layouts.cms')

@section('title', 'Inquiry')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Permintaan pelanggan" title="Inquiry"
            description="Pantau kebutuhan yang masuk dan pastikan setiap calon pelanggan mendapat tindak lanjut." />
        <section class="cms-surface" aria-label="Daftar inquiry">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.inquiries.partials.table')
            </div>
        </section>
    </div>
@endsection
