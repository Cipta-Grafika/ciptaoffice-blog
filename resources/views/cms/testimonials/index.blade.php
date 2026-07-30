@extends('layouts.cms')

@section('title', 'Testimonial')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Kepercayaan" title="Testimonial"
            description="Kelola kutipan pelanggan yang memperkuat reputasi CiptaOffice di homepage.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.testimonials.create') }}"><i class="bi bi-plus-lg"></i><span class="cms-action-label--compact">Tambah testimonial</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar testimonial">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.testimonials.partials.table')
            </div>
        </section>
    </div>
@endsection
