@extends('layouts.cms')

@section('title', 'Homepage')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Konten utama" title="Homepage"
            description="Atur proposisi utama dan arah tindakan pengunjung tanpa mengubah struktur desain halaman." />
        <form class="cms-form-surface" method="post" action="{{ route('cms.homepage.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Pesan utama</h2><p>Teks ini menjadi fokus pertama saat pengunjung membuka situs.</p></div>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label" for="eyebrow">Eyebrow</label><input class="form-control" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $settings->eyebrow) }}"></div>
                    <div class="col-12"><label class="form-label" for="title">Judul hero</label><input class="form-control form-control-lg" id="title" name="title" value="{{ old('title', $settings->title) }}" required></div>
                    <div class="col-12"><label class="form-label" for="summary">Ringkasan</label><textarea class="form-control" id="summary" name="summary" rows="4" required>{{ old('summary', $settings->summary) }}</textarea></div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Visual hero</h2><p>Gunakan JPEG, PNG, atau WebP yang tajam dan relevan dengan pesan utama.</p></div>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label" for="hero_image">Gambar hero</label><input class="form-control" id="hero_image" type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></div>
                    <div class="col-md-6"><label class="form-label" for="hero_image_alt">Alt text gambar</label><input class="form-control" id="hero_image_alt" name="hero_image_alt" value="{{ old('hero_image_alt', $settings->hero_image_alt) }}"></div>
                </div>
            </section>
            <section class="cms-form-section">
                <div class="cms-form-section-heading"><h2>Tombol tindakan</h2><p>Arahkan pengunjung ke tujuan paling penting dari homepage.</p></div>
                <div class="row g-4">
                    @foreach (['primary' => 'Utama', 'secondary' => 'Sekunder'] as $key => $label)
                        <div class="col-md-3"><label class="form-label" for="{{ $key }}_cta_label">Label CTA {{ $label }}</label><input class="form-control" id="{{ $key }}_cta_label" name="{{ $key }}_cta_label" value="{{ old($key . '_cta_label', $settings->{$key . '_cta_label'}) }}"></div>
                        <div class="col-md-3"><label class="form-label" for="{{ $key }}_cta_url">URL CTA {{ $label }}</label><input class="form-control" id="{{ $key }}_cta_url" name="{{ $key }}_cta_url" value="{{ old($key . '_cta_url', $settings->{$key . '_cta_url'}) }}"></div>
                    @endforeach
                </div>
            </section>
            <div class="cms-form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Simpan homepage</button><a class="btn btn-outline-secondary" href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Lihat situs</a></div>
        </form>
    </div>
@endsection
