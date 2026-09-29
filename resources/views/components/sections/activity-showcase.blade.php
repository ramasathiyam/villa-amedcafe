@props([
    'heading',
    'mainImage',
    'mainCaption',
    'mainHeading',
    'linkLabel',
    'linkDisabled' => false,
    'linkDisabledReason' => null,
    'collage' => [],
])

<section class="activity-showcase">
    <div class="activity-showcase-heading-wrap" data-reveal="fade-up">
        <h2 class="activity-showcase-heading">{{ $heading }}</h2>
    </div>

    <div class="activity-showcase-grid">
        <div class="activity-showcase-main">
            <img src="{{ asset($mainImage['src']) }}" alt="{{ $mainImage['alt'] }}" class="activity-showcase-main-image" data-reveal="image">
            <div class="activity-showcase-main-overlay" aria-hidden="true"></div>
            <div class="activity-showcase-main-content" data-reveal="fade-up">
                <span class="activity-showcase-main-caption">{{ $mainCaption }}</span>
                <p class="activity-showcase-main-heading">{{ $mainHeading }}</p>
                <div>
                    <x-ui.button variant="primary" :disabled="$linkDisabled" :disabled-reason="$linkDisabledReason">{{ $linkLabel }}</x-ui.button>
                </div>
            </div>
        </div>

        <div class="activity-showcase-collage">
            @foreach ($collage as $item)
                <div class="activity-showcase-collage-tile">
                    <img src="{{ asset($item['src']) }}" alt="{{ $item['alt'] }}" class="activity-showcase-collage-image" data-reveal="image">
                </div>
            @endforeach
        </div>
    </div>
</section>
