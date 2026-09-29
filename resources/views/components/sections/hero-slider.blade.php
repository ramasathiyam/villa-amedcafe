@props([
    'title',
    'subtitle' => null,
    'images' => [],
    'titleAlign' => 'center',
])

<section class="hero" @if(count($images) > 1) data-hero-slider @endif>
    @if (count($images) > 0)
        @foreach ($images as $index => $image)
            <img
                src="{{ \App\Support\CmsImage::url($image['src']) }}"
                alt="{{ $image['alt'] }}"
                class="hero-image hero-slider-image @if($index === 0) is-active @endif"
            >
        @endforeach
    @else
        <div class="hero-placeholder">Hero image placeholder — awaiting real photography</div>
    @endif

    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="hero-content {{ $titleAlign === 'left' ? 'hero-content-left' : '' }}">
        <h1 class="hero-title {{ $titleAlign === 'left' ? 'hero-title-left' : '' }}">{{ $title }}</h1>
        @if ($subtitle)
            <p class="hero-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (count($images) > 1)
        <button type="button" class="hero-slider-arrow hero-slider-arrow-prev" data-hero-arrow="prev" aria-label="Previous photo">&lsaquo;</button>
        <button type="button" class="hero-slider-arrow hero-slider-arrow-next" data-hero-arrow="next" aria-label="Next photo">&rsaquo;</button>

        <div class="hero-slider-dots" data-hero-slider-dots>
            @foreach ($images as $index => $image)
                <button
                    type="button"
                    class="hero-slider-dot @if($index === 0) is-active @endif"
                    data-hero-dot-index="{{ $index }}"
                    aria-label="Go to photo {{ $index + 1 }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                ></button>
            @endforeach
        </div>
    @endif
</section>
