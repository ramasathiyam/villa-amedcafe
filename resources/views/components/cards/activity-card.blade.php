@props(['activity', 'roundedImage' => false])

<article class="activity-card">
    <div class="activity-card-image-wrap {{ $roundedImage ? 'activity-card-image-wrap-rounded' : '' }}">
        @if (!empty($activity['image']))
            <img src="{{ \App\Support\CmsImage::url($activity['image']) }}" alt="{{ $activity['name'] }}" class="activity-card-image">
        @endif
        @if (!empty($activity['price']))
            <x-ui.price-badge :amount="$activity['price']" class="activity-card-badge" />
        @endif
    </div>

    <div class="activity-card-body">
        <x-ui.eyebrow-label :show-bullet="false">Experience</x-ui.eyebrow-label>
        <h3 class="activity-card-title">{{ $activity['name'] }}</h3>
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
