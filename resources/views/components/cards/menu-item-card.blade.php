@props(['item'])

<article class="menu-item-card">
    <div class="menu-item-card-image-wrap">
        <img src="{{ \App\Support\CmsImage::url($item['image']) }}" alt="{{ $item['name'] }}" class="menu-item-card-image">
    </div>
    <h4 class="menu-item-card-name">{{ $item['name'] }}</h4>
    <p class="menu-item-card-description">{{ $item['description'] }}</p>
</article>
