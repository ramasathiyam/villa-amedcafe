<div class="promo-banner-text-col">
    <x-ui.eyebrow-label>{{ $eyebrow }}</x-ui.eyebrow-label>
    <x-ui.section-heading as="h2" align="left">{{ $heading }}</x-ui.section-heading>
</div>
<div class="{{ $copyClasses }}">
    <p>{{ $body }}</p>
    @if ($linkLabel)
        <x-ui.rule-link :href="$linkHref" :disabled="$linkDisabled" :disabled-reason="$linkDisabledReason">{{ $linkLabel }}</x-ui.rule-link>
    @endif
</div>
