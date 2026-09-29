@extends('layouts.app')

@section('title', 'Resto Amed Cafe — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    @php
        // Out of scope for this DB migration (documented decision from the DB-design
        // phase): this FeatureBlock copy is shared boilerplate reused on Home/Spa too,
        // not a per-venue DiningVenue field.
        $hotelBlurb = 'Amed Café & Hotel Kebun Wayan is a heritage beachfront hotel located on Jemeluk Beach, Amed, Karangasem. As one of the oldest and most respected accommodations in Amed, it offers a unique charm that blends Balinese tradition, oceanfront relaxation, and community warmth.';
        $featureImages = $venue->imagesByGroup('feature');
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
        :link-disabled="true"
        link-disabled-reason="Booking flow not defined yet"
    />

    <x-sections.gallery-strip
        :columns="3"
        :items="$venue->imagesByGroup('gallery_1')->map(fn ($img) => [
            'image' => ['src' => $img->image, 'alt' => $img->alt_text ?: $venue->name],
            'title' => $img->title,
            'description' => $img->description,
        ])->all()"
    />

    <x-sections.feature-block
        eyebrow="Amed · Bali"
        heading="Amed Café & Hotel Kebun Wayan"
        :body="$hotelBlurb"
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No About page built yet"
        image-side="left"
        :image="['src' => $featureImages->get(0)?->image, 'alt' => $featureImages->get(0)?->alt_text ?: $venue->name]"
    />

    <x-sections.feature-block
        eyebrow="Amed · Bali"
        heading="Amed Café & Hotel Kebun Wayan"
        :body="$hotelBlurb"
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No About page built yet"
        image-side="right"
        :image="['src' => $featureImages->get(1)?->image, 'alt' => $featureImages->get(1)?->alt_text ?: $venue->name]"
    />

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
         come from dining_venue_images (group=gallery_2) instead of a hardcoded array. --}}
    <x-sections.card-grid :dot-count="$venue->imagesByGroup('gallery_2')->count()">
        @foreach ($venue->imagesByGroup('gallery_2') as $image)
            <div class="card-grid-slide">
                <div class="gallery-strip-image-wrap">
                    <img src="{{ \App\Support\CmsImage::url($image->image) }}" alt="{{ $image->alt_text ?: $venue->name }}" class="gallery-strip-image" data-reveal="image">
                </div>
            </div>
        @endforeach
    </x-sections.card-grid>

    <x-banner page="resto-amed-cafe" />
@endsection
