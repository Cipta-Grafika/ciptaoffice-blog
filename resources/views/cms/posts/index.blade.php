@extends('layouts.cms')

@section('title', 'Artikel')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Editorial" title="Artikel"
            description="Kelola draft, proses review, dan publikasi artikel CiptaOffice.">
            <x-slot:actions>
                <a class="btn btn-primary" href="{{ route('cms.posts.create') }}">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span class="cms-action-label--compact">Artikel baru</span>
                </a>
            </x-slot:actions>
        </x-cms-page-header>

        <section class="cms-surface" aria-label="Daftar artikel">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.posts.partials.table')
            </div>
        </section>
    </div>
@endsection
