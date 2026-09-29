@props([
    'eyebrow',
    'heading',
    'body',
    'linkLabel' => null,
    'linkHref' => null,
    'linkDisabled' => false,
    'linkDisabledReason' => null,
    'image' => null,
    'imagePosition' => 'center',
    'contentPanel' => false,
    'copyClass' => null,
])

@php
    $copyClasses = collect(['promo-banner-copy', $copyClass])->filter()->implode(' ');
    $textVars = compact('eyebrow', 'heading', 'body', 'linkLabel', 'linkHref', 'linkDisabled', 'linkDisabledReason', 'copyClasses');
@endphp

<section class="promo-banner">
    @if ($image)
        <img src="{{ \App\Support\CmsImage::url($image['src']) }}" alt="{{ $image['alt'] }}" class="promo-banner-image" style="object-position: {{ $imagePosition }}" data-reveal="image">
    @else
        <div class="promo-banner-placeholder" aria-hidden="true"></div>
    @endif
    <div class="promo-banner-overlay" aria-hidden="true"></div>

    <div class="promo-banner-content" data-reveal="fade-up">
        @if ($contentPanel)
            <div class="promo-banner-panel">
                @include('components.sections.partials.promo-banner-text', $textVars)
            </div>
        @else
            @include('components.sections.partials.promo-banner-text', $textVars)
        @endif
    </div>
</section>
