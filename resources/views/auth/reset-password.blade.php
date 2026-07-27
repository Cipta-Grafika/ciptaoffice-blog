@extends('layouts.auth')
@section('title', 'Buat kata sandi baru')
@section('content')
    <h1 class="font-display display-4">Kata sandi baru.</h1>
    <form method="post" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email"
                value="{{ old('email', $request->email) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Kata sandi baru</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Konfirmasi</label><input class="form-control"
                type="password" id="password_confirmation" name="password_confirmation" required>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <button class="btn btn-primary w-100">Simpan kata sandi</button>
    </form>
@endsection
