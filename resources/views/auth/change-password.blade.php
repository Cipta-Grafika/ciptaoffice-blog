@extends('layouts.cms')
@section('title', 'Ganti kata sandi')
@section('content')<div class="row justify-content-center">
        <div class="col-xl-7">
            <div class="cms-card p-4">
                <h1 class="h2">Ganti kata sandi</h1>
                <form method="post" action="{{ route('cms.password.update') }}">@csrf @method('PUT')<div class="mb-3">
                        <label class="form-label">Kata sandi saat ini</label><input class="form-control" type="password"
                            name="current_password" required></div>
                    <div class="mb-3"><label class="form-label">Kata sandi baru</label><input class="form-control"
                            type="password" name="password" required></div>
                    <div class="mb-4"><label class="form-label">Konfirmasi kata sandi</label><input class="form-control"
                            type="password" name="password_confirmation" required></div>
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <button class="btn btn-primary">Perbarui kata sandi</button>
                </form>
            </div>
        </div>
    </div>
@endsection
