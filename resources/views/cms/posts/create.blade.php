@extends('layouts.cms')

@section('title', 'Artikel baru')

@section('cms-page', 'posts-form')

@section('content')
    <div class="cms-page cms-page--narrow">
        <x-cms-page-header eyebrow="Langkah pertama" title="Buat artikel baru"
            description="Mulai dengan judul yang jelas. Sistem akan menyiapkan draft dan slug unik untuk dilengkapi pada editor berikutnya." />
        <form class="cms-form-surface" method="post" action="{{ route('cms.posts.store') }}">
            @csrf
            <div class="cms-form-section">
                <div class="cms-form-section-heading">
                    <h2>Identitas artikel</h2>
                    <p>Judul dan nama author masih dapat disunting sebelum artikel diterbitkan.</p>
                </div>
                <label class="form-label" for="title">Judul artikel</label>
                <input class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}"
                    maxlength="180" placeholder="Contoh: Merancang ruang kerja yang tetap relevan" required autofocus>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="mt-4">
                    <label class="form-label" for="author_name">Nama author</label>
                    <input class="form-control @error('author_name') is-invalid @enderror" id="author_name"
                        name="author_name" value="{{ old('author_name') }}" maxlength="120"
                        placeholder="Contoh: Tim Editorial CiptaOffice" aria-describedby="author_name_help">
                    @error('author_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text" id="author_name_help">Opsional. Kosongkan untuk menggunakan nama akun
                        {{ auth()->user()->name }}.</div>
                </div>
            </div>
            <div class="cms-form-actions">
                <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-right"></i> Buat draft &
                    lanjutkan</button>
                <a class="btn btn-outline-secondary" href="{{ route('cms.posts.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
