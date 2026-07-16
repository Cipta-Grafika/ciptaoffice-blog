@extends('layouts.auth')

@section('title', 'Masuk CMS')

@section('content')
    <p class="section-kicker">Area editorial</p>
    <h1 class="font-display display-4 mb-2">Selamat datang kembali.</h1>
    <p class="text-muted mb-4">Masuk untuk mengelola konten CiptaOffice.</p>

    <form method="post" action="{{ url('/cms/login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control form-control-lg @error('email') is-invalid @enderror" type="email" id="email"
                name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Kata sandi</label>
            <input class="form-control form-control-lg" type="password" id="password" name="password"
                autocomplete="current-password" required>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <label for="remember">
                <input class="form-check-input me-1" type="checkbox" id="remember" name="remember">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
        </div>

        <button class="btn btn-primary btn-lg w-100" type="submit">Masuk CMS</button>
    </form>
@endsection
