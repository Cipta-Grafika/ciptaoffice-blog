@extends('layouts.cms')

@section('title', $user->exists ? 'Edit pengguna' : 'Tambah pengguna')

@section('content')
    <div class="cms-page cms-page--narrow">
        <a class="cms-back-link" href="{{ route('cms.users.index') }}"><i class="bi bi-arrow-left"></i> Daftar pengguna</a>
        <x-cms-page-header eyebrow="Akses CMS" :title="$user->exists ? 'Edit pengguna' : 'Tambah pengguna'"
            description="Kelola identitas, peran, dan status akses anggota tim editorial." />
        <form class="cms-form-surface" method="post" action="{{ $user->exists ? route('cms.users.update', $user) : route('cms.users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Identitas & peran</h2><p>Peran menentukan kewenangan pengguna di dalam CMS.</p></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Role</label><select class="form-select" name="role">@foreach ($roles as $role)<option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>@endforeach</select></div>
                    <div class="col-md-6 d-flex align-items-end pb-2"><label class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))><span class="form-check-label">Akun aktif</span></label></div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Keamanan akun</h2><p>{{ $user->exists ? 'Kosongkan kedua field jika kata sandi tidak ingin diubah.' : 'Gunakan kata sandi yang kuat untuk akun baru.' }}</p></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">{{ $user->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</label><input class="form-control" type="password" name="password" @required(!$user->exists)></div>
                    <div class="col-md-6"><label class="form-label">Konfirmasi</label><input class="form-control" type="password" name="password_confirmation" @required(!$user->exists)></div>
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Simpan pengguna</button><a class="btn btn-outline-secondary" href="{{ route('cms.users.index') }}">Batal</a></div>
        </form>
        @if ($user->exists && !$user->is(auth()->user()))
            <form class="cms-danger-action" method="post" action="{{ route('cms.users.destroy', $user) }}" data-cms-confirm-form data-confirm-variant="danger" data-confirm-title="Hapus pengguna?" data-confirm-message="Akun “{{ $user->name }}” akan kehilangan akses ke CMS." data-confirm-action="Hapus pengguna">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0" type="submit">Hapus pengguna</button></form>
        @endif
    </div>
@endsection
