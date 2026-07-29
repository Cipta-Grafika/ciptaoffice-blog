@extends('layouts.cms')

@section('title', 'Pengguna')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Akses CMS" title="Pengguna"
            description="Kelola akun, peran, dan akses tim editorial CiptaOffice.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.users.create') }}"><i class="bi bi-person-plus"></i><span class="cms-action-label--compact">Tambah pengguna</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar pengguna">
            <div data-cms-ajax-container class="position-relative" style="transition: opacity 0.2s;">
                @include('cms.users.partials.table')
            </div>
        </section>
    </div>
@endsection
