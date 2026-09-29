@extends('layouts.app')

@section('title', 'Activity — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    <x-sections.hero
        :title="$page->hero_title"
        :subtitle="$page->hero_subtitle"
        title-align="left"
        subtitle-variant="statement"
        :image="['src' => $page->hero_image, 'alt' => 'Guests relaxing in stretch pose beside the pool at Amed Café & Hotel Kebun Wayan']"
    />

    <x-sections.intro-section
        :heading="$page->intro_heading"
        :body="$page->intro_body"
        link-label="Book Now"
        :link-disabled="true"
        link-disabled-reason="Booking flow not defined yet"
    />

    <x-sections.activity-showcase
        heading="Get Active Outdoors or Try Something New"
        :main-image="['src' => '/images/activities/activity-feature.png', 'alt' => 'Divers wading into the sea from traditional Balinese boats in Amed']"
        main-caption="Amed Café & Hotel Kebun Wayan."
        main-heading="Discover and book activities with us."
        link-label="View"
        :link-disabled="true"
        link-disabled-reason="Booking flow not defined yet"
        :collage="[
            ['src' => '/images/activities/collage-wellness.png', 'alt' => 'Guests practicing yoga poolside at sunrise'],
            ['src' => '/images/activities/collage-connect.png', 'alt' => 'Writing on lontar leaves'],
            ['src' => '/images/activities/collage-fishing.png', 'alt' => 'Fisherman holding the day\'s catch'],
            ['src' => '/images/activities/collage-snorkeling.png', 'alt' => 'Snorkelers among reef fish'],
        ]"
    />

    <x-sections.card-grid heading="Exceptional Experiences" :dot-count="count($exceptionalExperiences)">
        @foreach ($exceptionalExperiences as $activity)
            <div class="card-grid-slide">
                <x-cards.activity-card :activity="$activity" :rounded-image="true" />
            </div>
        @endforeach
    </x-sections.card-grid>

    {{-- <x-sections.promo-banner
        eyebrow="Explore"
        heading="Create Memorable Moments"
        body="From underwater adventures to authentic Balinese experiences, there is always something to discover."
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No dedicated page built yet"
        :image="['src' => '/images/activities/promo-memorable-moments.png', 'alt' => 'Aerial view of the hotel\'s rooftop terrace and garden']"
        :content-panel="true"
    /> --}}

    <x-banner page="activities" />
@endsection
