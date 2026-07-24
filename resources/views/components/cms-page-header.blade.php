@props([
    'eyebrow',
    'title',
    'description' => null,
])

<header {{ $attributes->class(['cms-page-header']) }}>
    <div class="cms-page-heading">
        <p class="cms-page-eyebrow mb-2"><span aria-hidden="true"></span>{{ $eyebrow }}</p>
        <h1 class="cms-page-title mb-2">{{ $title }}</h1>
        @if ($description)
            <p class="cms-page-description mb-0">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="cms-page-actions">{{ $actions }}</div>
    @endisset
</header>
