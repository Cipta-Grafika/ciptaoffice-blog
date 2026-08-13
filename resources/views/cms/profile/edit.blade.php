@extends('layouts.cms')

@section('title', 'Profil saya')

@section('cms-page', 'profile')

@section('content')
    @php
        $initials = collect(preg_split('/\s+/', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($name) => mb_strtoupper(mb_substr($name, 0, 1)))
            ->implode('');
    @endphp

    <div class="cms-page cms-profile-page">
        <x-cms-page-header eyebrow="Identitas editorial" title="Profil saya"
            description="Kelola identitas yang digunakan saat menyusun dan menerbitkan konten CiptaOffice." />

        <div class="cms-profile-layout">
            <aside class="cms-profile-identity" aria-label="Ringkasan profil">
                <span class="cms-profile-identity__index" aria-hidden="true">CO / {{ str_pad((string) $user->id, 3, '0', STR_PAD_LEFT) }}</span>
                <div class="cms-profile-monogram" aria-hidden="true">{{ $initials }}</div>
                <div class="cms-profile-identity__copy">
                    <p>Anggota editorial</p>
                    <h2>{{ $user->name }}</h2>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="cms-profile-tags" aria-label="Informasi akses">
                    <span><i class="bi bi-shield-check" aria-hidden="true"></i>{{ $user->role->label() }}</span>
                    <span><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $user->is_active ? 'Akun aktif' : 'Akun nonaktif' }}</span>
                </div>
                <dl class="cms-profile-metrics">
                    <div><dt>{{ $articleCount }}</dt><dd>Total artikel</dd></div>
                    <div><dt>{{ $publishedArticleCount }}</dt><dd>Telah terbit</dd></div>
                </dl>
                <p class="cms-profile-since"><i class="bi bi-calendar3" aria-hidden="true"></i>Bergabung {{ $user->created_at->translatedFormat('F Y') }}</p>
            </aside>

            <div class="cms-profile-content">
                <form class="cms-form-surface" method="post" action="{{ route('cms.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <section class="cms-form-section">
                        <div class="cms-form-section-heading">
                            <h2>Informasi pribadi</h2>
                            <p>Nama akan tampil sebagai identitas author. Email digunakan untuk masuk dan memulihkan akun.</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profile_name">Nama lengkap</label>
                                <input class="form-control @error('name') is-invalid @enderror" id="profile_name"
                                    name="name" value="{{ old('name', $user->name) }}" maxlength="120"
                                    autocomplete="name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_email">Alamat email</label>
                                <input class="form-control @error('email') is-invalid @enderror" id="profile_email"
                                    name="email" type="email" value="{{ old('email', $user->email) }}"
                                    maxlength="180" autocomplete="email" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <!-- <div class="cms-profile-access-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Peran dan status akun hanya dapat diubah oleh administrator melalui modul Pengguna.</span>
                        </div> -->
                    </section>
                    <div class="cms-form-actions">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2" aria-hidden="true"></i>Simpan profil</button>
                    </div>
                </form>

                <form class="cms-profile-security" method="post" action="{{ route('cms.profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    <header class="cms-profile-security__header">
                        <span class="cms-profile-security__icon"><i class="bi bi-key" aria-hidden="true"></i></span>
                        <div>
                            <p>Keamanan akun</p>
                            <h2>Perbarui kata sandi</h2>
                            <span>Gunakan kata sandi unik dengan minimum 10 karakter untuk menjaga akses CMS.</span>
                        </div>
                    </header>
                    <div class="cms-profile-security__fields">
                        <div>
                            <label class="form-label" for="current_password">Kata sandi saat ini</label>
                            <div class="input-group">
                                <input class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" type="password"
                                    autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary cms-password-toggle" type="button"
                                    data-password-toggle="#current_password" aria-label="Tampilkan kata sandi"
                                    aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                            @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="new_password">Kata sandi baru</label>
                            <div class="input-group">
                                <input class="form-control @error('password') is-invalid @enderror" id="new_password"
                                    name="password" type="password" minlength="10" maxlength="255"
                                    autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary cms-password-toggle" type="button"
                                    data-password-toggle="#new_password" aria-label="Tampilkan kata sandi"
                                    aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="password_confirmation">Konfirmasi kata sandi baru</label>
                            <div class="input-group">
                                <input class="form-control" id="password_confirmation" name="password_confirmation"
                                    type="password" minlength="10" maxlength="255" autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary cms-password-toggle" type="button"
                                    data-password-toggle="#password_confirmation" aria-label="Tampilkan kata sandi"
                                    aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                    <footer class="cms-profile-security__footer">
                        <p><i class="bi bi-shield-check" aria-hidden="true"></i>Kata sandi baru tidak akan mengubah sesi aktif Anda saat ini.</p>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-lock" aria-hidden="true"></i>Perbarui kata sandi</button>
                    </footer>
                </form>
            </div>
        </div>
    </div>
@endsection
