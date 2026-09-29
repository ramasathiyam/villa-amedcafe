@props([
    'heading',
    'body',
    'linkLabel' => null,
    'linkHref' => null,
    'linkDisabled' => false,
    'linkDisabledReason' => null,
])

<section class="intro-section">
    <div class="intro-section-content" data-reveal="fade-up">
        <x-ui.section-heading>{{ $heading }}</x-ui.section-heading>
        <p>{{ $body }}</p>
        @if ($linkLabel)
            <x-ui.rule-link :href="$linkHref" :disabled="$linkDisabled" :disabled-reason="$linkDisabledReason">{{ $linkLabel }}</x-ui.rule-link>
        @endif
    </div>
</section>
