@extends('layouts.app')

@section('title', 'Spa — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    <x-sections.hero
        :title="$page->hero_title"
        :subtitle="$page->hero_subtitle"
        :image="['src' => $page->hero_image, 'alt' => 'Candles and hot stones beside a spa massage treatment']"
    />

    <x-sections.intro-section
        :heading="$page->intro_heading"
        :body="$page->intro_body"
        link-label="Book Now"
        :link-disabled="true"
        link-disabled-reason="Booking flow not defined yet"
    />

    {{-- Reference shows this exact eyebrow/heading/paragraph twice (once per FeatureBlock)
         — that's genuinely what design/SPA.png shows, not a transcription mistake. Only
         one treatment photo was provided, so both blocks reuse it too. --}}
    <x-sections.feature-block
        variant="overlap"
        eyebrow="Amed · Bali"
        heading="Amed Café & Hotel Kebun Wayan"
        body="Amed Café & Hotel Kebun Wayan is a heritage beachfront hotel located on Jemeluk Beach, Amed, Karangasem. As one of the oldest and most respected accommodations in Amed, it offers a unique charm that blends Balinese tradition, oceanfront relaxation, and community warmth."
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No About page built yet"
        image-side="right"
        :image="['src' => '/images/spa/spa-treatment.png', 'alt' => 'Guest receiving a shoulder massage at the spa']"
    />

    <x-sections.feature-block
        variant="overlap"
        eyebrow="Amed · Bali"
        heading="Amed Café & Hotel Kebun Wayan"
        body="Amed Café & Hotel Kebun Wayan is a heritage beachfront hotel located on Jemeluk Beach, Amed, Karangasem. As one of the oldest and most respected accommodations in Amed, it offers a unique charm that blends Balinese tradition, oceanfront relaxation, and community warmth."
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No About page built yet"
        image-side="left"
        :image="['src' => '/images/spa/spa-treatment.png', 'alt' => 'Guest receiving a shoulder massage at the spa']"
    />

    <x-banner page="spa" />
@endsection
