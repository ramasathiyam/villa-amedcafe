@props(['activity', 'roundedImage' => false, 'anchorId' => null])

<article class="activity-card" @if($anchorId) id="{{ $anchorId }}" @endif>
    <div class="activity-card-image-wrap {{ $roundedImage ? 'activity-card-image-wrap-rounded' : '' }}">
        @if (!empty($activity['image']))
            @if (!empty($activity['linkUrl']))
                <a href="{{ $activity['linkUrl'] }}" class="activity-card-image-link" tabindex="-1" aria-hidden="true">
                    <img src="{{ \App\Support\CmsImage::url($activity['image']) }}" alt="{{ $activity['name'] }}" class="activity-card-image">
                </a>
            @else
                <img src="{{ \App\Support\CmsImage::url($activity['image']) }}" alt="{{ $activity['name'] }}" class="activity-card-image">
            @endif
        @endif
        @if (!empty($activity['price']))
            <x-ui.price-badge :amount="$activity['price']" class="activity-card-badge" />
        @endif
    </div>

    <div class="activity-card-body">
        <x-ui.eyebrow-label :show-bullet="false">Experience</x-ui.eyebrow-label>
        <h3 class="activity-card-title">
            @if (!empty($activity['linkUrl']))
                <a href="{{ $activity['linkUrl'] }}" class="activity-card-title-link">{{ $activity['name'] }}</a>
            @else
                {{ $activity['name'] }}
            @endif
        </h3>
        @if (!empty($activity['description']))
            <p class="activity-card-description">{{ $activity['description'] }}</p>
        @endif
        @if (!empty($activity['includes']))
            <ul class="activity-card-includes">
                @foreach ($activity['includes'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
