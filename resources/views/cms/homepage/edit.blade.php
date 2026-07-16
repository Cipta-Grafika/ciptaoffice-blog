@extends('layouts.cms')
@section('title', 'Homepage')
@section('content')<div class="mb-4">
        <p class="section-kicker mb-1">Konten utama</p>
        <h1 class="font-display display-5">Homepage</h1>
        <p class="text-muted">Atur proposisi utama tanpa mengubah struktur desain.</p>
    </div>
    <form class="cms-card p-4" method="post" action="{{ route('cms.homepage.update') }}" enctype="multipart/form-data">@csrf
        @method('PUT')<div class="row g-4">
            <div class="col-md-6"><label class="form-label">Eyebrow</label><input class="form-control" name="eyebrow"
                    value="{{ old('eyebrow', $settings->eyebrow) }}"></div>
            <div class="col-12"><label class="form-label">Judul hero</label><input class="form-control form-control-lg"
                    name="title" value="{{ old('title', $settings->title) }}" required></div>
            <div class="col-12"><label class="form-label">Ringkasan</label>
                <textarea class="form-control" name="summary" rows="4" required>{{ old('summary', $settings->summary) }}</textarea>
            </div>
            <div class="col-md-6"><label class="form-label">Gambar hero</label><input class="form-control" type="file"
                    name="hero_image" accept="image/jpeg,image/png,image/webp"></div>
            <div class="col-md-6"><label class="form-label">Alt text gambar</label><input class="form-control"
                    name="hero_image_alt" value="{{ old('hero_image_alt', $settings->hero_image_alt) }}"></div>
            @foreach (['primary' => 'Utama', 'secondary' => 'Sekunder'] as $key => $label)
                <div class="col-md-3"><label class="form-label">Label CTA {{ $label }}</label><input
                        class="form-control" name="{{ $key }}_cta_label"
                        value="{{ old($key . '_cta_label', $settings->{$key . '_cta_label'}) }}"></div>
                <div class="col-md-3"><label class="form-label">URL CTA {{ $label }}</label><input
                        class="form-control" name="{{ $key }}_cta_url"
                        value="{{ old($key . '_cta_url', $settings->{$key . '_cta_url'}) }}"></div>
            @endforeach
            <div class="col-12"><button class="btn btn-primary">Simpan homepage</button></div>
        </div>
    </form>
@endsection
