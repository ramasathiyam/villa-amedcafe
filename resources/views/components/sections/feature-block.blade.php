@props([
    'eyebrow' => null,
    'heading',
    'body',
    'linkLabel' => null,
    'linkHref' => null,
    'linkDisabled' => false,
    'linkDisabledReason' => null,
    'image' => null,
    'imageSide' => 'right',
    'imagePosition' => 'center',
    'variant' => 'default',
])

@php
    $isOverlap = $variant === 'overlap';
    $blockClasses = collect([
        'feature-block',
        $isOverlap ? 'feature-block-overlap' : null,
        (!$isOverlap && $imageSide === 'left') ? 'feature-block-reverse' : null,
        ($isOverlap && $imageSide === 'left') ? 'feature-block-overlap-image-left' : null,
    ])->filter()->implode(' ');
    $textClasses = collect(['feature-block-text', $isOverlap ? 'feature-block-card' : null])->filter()->implode(' ');
@endphp

<section class="{{ $blockClasses }}">
    <div class="{{ $textClasses }}" data-reveal="fade-up">
        @if ($eyebrow)
            <x-ui.eyebrow-label>{{ $eyebrow }}</x-ui.eyebrow-label>
        @endif
        <x-ui.section-heading as="h2" align="left">{{ $heading }}</x-ui.section-heading>
        <p>{{ $body }}</p>
        @if ($linkLabel)
            <x-ui.rule-link :href="$linkHref" :disabled="$linkDisabled" :disabled-reason="$linkDisabledReason">{{ $linkLabel }}</x-ui.rule-link>
        @endif
    </div>

    <div class="feature-block-image-wrap">
        @if ($image)
            <img src="{{ \App\Support\CmsImage::url($image['src']) }}" alt="{{ $image['alt'] }}" class="feature-block-image" style="object-position: {{ $imagePosition }}" data-reveal="image">
        @endif
    </div>
</section>
