@extends('layouts.cms')

@section('title', $testimonial->exists ? 'Edit testimonial' : 'Tambah testimonial')

@section('content')
    <div class="cms-page cms-page--narrow">
        <a class="cms-back-link" href="{{ route('cms.testimonials.index') }}"><i class="bi bi-arrow-left"></i> Daftar testimonial</a>
        <x-cms-page-header eyebrow="Kepercayaan" :title="$testimonial->exists ? 'Edit testimonial' : 'Tambah testimonial'"
            description="Kelola identitas pelanggan dan kutipan yang akan ditampilkan pada homepage." />
        <form class="cms-form-surface" method="post" enctype="multipart/form-data"
            action="{{ $testimonial->exists ? route('cms.testimonials.update', $testimonial) : route('cms.testimonials.store') }}">
            @csrf
            @if ($testimonial->exists) @method('PUT') @endif
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Identitas pelanggan</h2><p>Informasi ini memberi konteks dan kredibilitas pada kutipan.</p></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="reviewer_name" value="{{ old('reviewer_name', $testimonial->reviewer_name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Jabatan</label><input class="form-control" name="reviewer_title" value="{{ old('reviewer_title', $testimonial->reviewer_title) }}"></div>
                    <div class="col-md-6"><label class="form-label">Perusahaan</label><input class="form-control" name="company" value="{{ old('company', $testimonial->company) }}"></div>
                    <div class="col-md-3"><label class="form-label">Rating</label><input class="form-control" type="number" min="1" max="5" name="rating" value="{{ old('rating', $testimonial->rating) }}"></div>
                    <div class="col-md-3"><label class="form-label">Urutan</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}" required></div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Kutipan & avatar</h2><p>Gunakan kutipan singkat yang tetap terasa autentik.</p></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Kutipan</label><textarea class="form-control" name="quote" rows="5" required>{{ old('quote', $testimonial->quote) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label">Avatar</label><input class="form-control" type="file" name="avatar"></div>
                    <div class="col-md-6"><label class="form-label">Alt text avatar</label><input class="form-control" name="avatar_alt" value="{{ old('avatar_alt', $testimonial->avatar_alt) }}"></div>
                    <div class="col-12"><label class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active))><span class="form-check-label">Tampilkan di homepage</span></label></div>
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Simpan testimonial</button><a class="btn btn-outline-secondary" href="{{ route('cms.testimonials.index') }}">Batal</a></div>
        </form>
        @if ($testimonial->exists)
            <form class="cms-danger-action" method="post" action="{{ route('cms.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimonial ini?')">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0">Hapus testimonial</button></form>
        @endif
    </div>
@endsection
