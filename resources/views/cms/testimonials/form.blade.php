@extends('layouts.cms')

@section('title', $testimonial->exists ? 'Edit testimonial' : 'Tambah testimonial')

@section('content')
    <div class="cms-page cms-page--narrow">
        <x-cms-page-header eyebrow="Kepercayaan" :title="$testimonial->exists ? 'Edit testimonial' : 'Tambah testimonial'"
            description="Kelola identitas pelanggan dan kutipan yang akan ditampilkan pada homepage." />
        <form class="cms-form-surface" method="post" enctype="multipart/form-data"
            action="{{ $testimonial->exists ? route('cms.testimonials.update', $testimonial) : route('cms.testimonials.store') }}">
            @csrf
            @if ($testimonial->exists) @method('PUT') @endif
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Identitas pelanggan</h2><p>Informasi ini memberi konteks dan kredibilitas pada kutipan.</p></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input class="form-control @error('reviewer_name') is-invalid @enderror" name="reviewer_name" value="{{ old('reviewer_name', $testimonial->reviewer_name) }}" required>
                        @error('reviewer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input class="form-control @error('reviewer_title') is-invalid @enderror" name="reviewer_title" value="{{ old('reviewer_title', $testimonial->reviewer_title) }}">
                        @error('reviewer_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Perusahaan</label>
                        <input class="form-control @error('company') is-invalid @enderror" name="company" value="{{ old('company', $testimonial->company) }}">
                        @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rating</label>
                        <input class="form-control @error('rating') is-invalid @enderror" type="number" min="1" max="5" name="rating" value="{{ old('rating', $testimonial->rating) }}">
                        @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urutan</label>
                        <input class="form-control @error('sort_order') is-invalid @enderror" type="number" min="0" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}" required>
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Kutipan & avatar</h2><p>Gunakan kutipan singkat yang tetap terasa autentik.</p></div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Kutipan</label>
                        <textarea class="form-control @error('quote') is-invalid @enderror" name="quote" rows="5" required>{{ old('quote', $testimonial->quote) }}</textarea>
                        @error('quote')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Avatar</label>
                        <input class="form-control @error('avatar') is-invalid @enderror" type="file" name="avatar">
                        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alt text avatar</label>
                        <input class="form-control @error('avatar_alt') is-invalid @enderror" name="avatar_alt" value="{{ old('avatar_alt', $testimonial->avatar_alt) }}">
                        @error('avatar_alt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-check mb-0">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active))>
                            <span class="form-check-label">Tampilkan di homepage</span>
                        </label>
                        @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit">Simpan testimonial</button><a class="btn btn-outline-secondary" href="{{ route('cms.testimonials.index') }}">Batal</a></div>
        </form>
        @if ($testimonial->exists)
            <form class="cms-danger-action" method="post" action="{{ route('cms.testimonials.destroy', $testimonial) }}" data-cms-confirm-form data-confirm-variant="danger" data-confirm-title="Hapus testimonial?" data-confirm-message="Testimonial “{{ $testimonial->name }}” akan dihapus dari CMS." data-confirm-action="Hapus testimonial">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0" type="submit">Hapus testimonial</button></form>
        @endif
    </div>
@endsection
