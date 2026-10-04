@extends('layouts.app')

@section('title', 'Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    <x-sections.hero
        :title="$page->hero_title"
        :subtitle="$page->hero_subtitle"
        :image="['src' => $page->hero_image, 'alt' => 'Amed Café & Hotel Kebun Wayan pool and villa, Amed, Bali']"
        image-position="center 40%"
    />

    <x-sections.intro-section
        :heading="$page->intro_heading"
        :body="$page->intro_body"
        link-label="Book Now"
        :link-href="route('room')"
    />

    <x-sections.feature-block
        eyebrow="Amed · Bali"
        heading="Amed Café & Hotel Kebun Wayan"
        body="Amed Café & Hotel Kebun Wayan is a heritage beachfront hotel located on Jemeluk Beach, Amed, Karangasem. As one of the oldest and most respected accommodations in Amed, it offers a unique charm that blends Balinese tradition, oceanfront relaxation, and community warmth."
        link-label="Discover More"
        :link-disabled="true"
        link-disabled-reason="No About page built yet"
        image-side="right"
        :image="['src' => '/images/home/feature-about.png', 'alt' => 'Bedroom with sunset ocean view at Amed Café & Hotel Kebun Wayan']"
    />

    {{-- Events (Admin > Events) — stacked, Home only. A 0-item @foreach renders nothing,
         satisfying "no visible events -> no markup"; a 1-item one renders identically to
         before (the wrapper adds gap BETWEEN children only, contributing nothing around a
         single child and no margin/padding of its own around the group). --}}
    @if ($visibleEvents->isNotEmpty())
        <div class="events-stack">
            @foreach ($visibleEvents as $event)
                <x-sections.promo-banner
                    :eyebrow="$event->label"
                    :heading="$event->title"
                    :body="$event->description"
                    :image="['src' => $event->background_image, 'alt' => $event->title]"
                    image-position="left center"
                    copy-class="home-halloween-copy"
                    :link-label="$event->link_url ? 'Discover More' : null"
                    :link-href="$event->link_url"
                />
            @endforeach
        </div>
    @endif

    @if (count($homeActivities) > 0)
        <x-sections.intro-section
            heading="Activity"
            body=""
        />

        <x-sections.card-grid :dot-count="count($homeActivities)">
            @foreach ($homeActivities as $activity)
                <div class="card-grid-slide">
                    <x-cards.activity-card :activity="$activity" />
                </div>
            @endforeach
        </x-sections.card-grid>
    @endif

    <x-banner page="home" />
@endsection
