@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'imagePosition' => 'center',
    'titleAlign' => 'center',
    'subtitleVariant' => 'tagline',
])

<section class="hero">
    @if ($image)
        <img src="{{ \App\Support\CmsImage::url($image['src']) }}" alt="{{ $image['alt'] }}" class="hero-image" style="object-position: {{ $imagePosition }}">
    @else
        <div class="hero-placeholder">Hero image placeholder — awaiting real photography</div>
    @endif

    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="hero-content {{ $titleAlign === 'left' ? 'hero-content-left' : '' }}">
        <h1 class="hero-title {{ $titleAlign === 'left' ? 'hero-title-left' : '' }}">{{ $title }}</h1>
        @if ($subtitle)
            <p class="hero-subtitle {{ $subtitleVariant === 'statement' ? 'hero-subtitle-statement' : '' }}">{{ $subtitle }}</p>
        @endif
    </div>
</section>
