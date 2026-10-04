@extends('layouts.app')

@section('title', 'Barak Rooftop and Bar — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    @php
        // "Signature Cocktails" is a static section label, same pattern as Activity's
        // "Exceptional Experiences" — never DB-driven anywhere on the site.
        $featureImage = $venue->imagesByGroup('feature')->first();
    @endphp

    <x-sections.hero
        :title="$page->hero_title"
        :subtitle="$page->hero_subtitle"
        title-align="left"
        :image="['src' => $page->hero_image, 'alt' => $page->hero_title]"
    />

    <x-sections.intro-section
        :heading="$page->intro_heading"
        :body="$page->intro_body"
        link-label="Book Now"
        :link-href="route('room')"
    />

    <x-sections.gallery-strip
        :columns="5"
        :items="$venue->imagesByGroup('menu_highlight')->map(fn ($img) => [
            'image' => ['src' => $img->image, 'alt' => $img->alt_text ?: $venue->name],
            'title' => $img->title,
            'description' => $img->description,
        ])->all()"
    />

    {{-- Signature Cocktails row: carousel on a cream panel beside the candid photo, side
         by side on desktop/tablet — matches design/Barak Rooftop and bar.png. --}}
    <div class="barak-cocktails-row">
        <div class="barak-cocktails-carousel">
            <x-sections.card-grid heading="Signature Cocktails" :dot-count="$venue->items->count()">
                @foreach ($venue->items as $item)
                    <div class="card-grid-slide">
                        <x-cards.menu-item-card :item="$item->toMenuItemArray()" />
                    </div>
                @endforeach
            </x-sections.card-grid>
        </div>
        <div class="barak-cocktails-photo">
            <img src="{{ \App\Support\CmsImage::url($featureImage?->image) }}" alt="{{ $featureImage?->alt_text ?: $venue->name }}" class="barak-cocktails-photo-img" data-reveal="image">
        </div>
    </div>

    @if ($testimonials->isNotEmpty())
        <x-sections.testimonials
            :items="$testimonials->map(fn ($testimonial) => [
                'quote' => $testimonial->quote,
                'name' => $testimonial->name,
                'rating' => $testimonial->rating,
            ])->all()"
        />
    @endif

    {{-- Closing gallery is a carousel (built last session) — reuses <x-sections.card-grid>
         + initCardGrids() in app.js, unchanged. Only the data source changed: images now
         come from dining_venue_images (group=gallery_1) instead of a hardcoded array. --}}
    <x-sections.card-grid :dot-count="$venue->imagesByGroup('gallery_1')->count()">
        @foreach ($venue->imagesByGroup('gallery_1') as $image)
            <div class="card-grid-slide">
                <div class="gallery-strip-image-wrap">
                    <img src="{{ \App\Support\CmsImage::url($image->image) }}" alt="{{ $image->alt_text ?: $venue->name }}" class="gallery-strip-image" data-reveal="image">
                </div>
            </div>
        @endforeach
    </x-sections.card-grid>

    <x-banner page="barak-rooftop-and-bar" />
@endsection
