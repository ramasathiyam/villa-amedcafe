@props([
    'items' => [],
    'columns' => 3,
])

@if (count($items) > 0)
<section class="gallery-strip">
    <div class="gallery-strip-row" data-reveal-group style="--columns: {{ $columns }}">
        @foreach ($items as $index => $item)
            <div class="gallery-strip-item" data-reveal-item style="--stagger-i: {{ min($index, 6) }}">
                <div class="gallery-strip-image-wrap">
                    <img src="{{ \App\Support\CmsImage::url($item['image']['src']) }}" alt="{{ $item['image']['alt'] }}" class="gallery-strip-image" data-reveal="image">
                </div>
                @if (!empty($item['title']) || !empty($item['description']))
                    <div class="gallery-strip-caption-block">
                        @if (!empty($item['title']))
                            <p class="gallery-strip-caption-title">{{ $item['title'] }}</p>
                        @endif
                        @if (!empty($item['description']))
                            <p class="gallery-strip-caption-description">{{ $item['description'] }}</p>
                        @endif
                    </div>
                @endif
                @if (!empty($item['caption']))
                    <p class="gallery-strip-caption">{{ $item['caption'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
@endif
