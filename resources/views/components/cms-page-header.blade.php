@props([
    'eyebrow',
    'title',
    'description' => null,
])

@php
    $hasStatus = isset($status) && $status->isNotEmpty();
@endphp

<header {{ $attributes->class(['cms-page-header', 'cms-page-header--with-meta' => $hasStatus]) }}>
    <div class="cms-page-heading">
        <p class="cms-page-eyebrow mb-2"><span aria-hidden="true"></span>{{ $eyebrow }}</p>
        <h1 class="cms-page-title mb-2">{{ $title }}</h1>
        @if ($description)
            <p class="cms-page-description mb-0">{{ $description }}</p>
        @endif
    </div>
    @if ($hasStatus)
        <div class="cms-page-meta">
            <div class="cms-page-status">{{ $status }}</div>
            @isset($actions)
                <div class="cms-page-actions">{{ $actions }}</div>
            @endisset
        </div>
    @else
        @isset($actions)
            <div class="cms-page-actions">{{ $actions }}</div>
        @endisset
    @endif
</header>
