@extends('layouts.auth')
@section('title','Lupa kata sandi')
@section('content')<p class="section-kicker">Pemulihan akun</p><h1 class="font-display display-4">Reset kata sandi.</h1><p class="text-muted">Masukkan email akun. Jika terdaftar, sistem akan mengirim tautan reset.</p><form method="post" action="{{ route('password.email') }}">@csrf<label class="form-label" for="email">Email</label><input class="form-control form-control-lg mb-3 @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror<button class="btn btn-primary btn-lg w-100">Kirim tautan reset</button></form><a class="d-block mt-4" href="{{ route('login') }}">Kembali ke login</a>
@endsection
