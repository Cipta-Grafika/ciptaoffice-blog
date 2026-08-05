@extends('layouts.cms')

@section('title', 'Testimonial')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Kepercayaan" title="Testimonial"
            description="Kelola kutipan pelanggan yang memperkuat reputasi CiptaOffice di homepage.">
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar testimonial">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.testimonials.partials.table')
            </div>
        </section>
    </div>
@endsection
