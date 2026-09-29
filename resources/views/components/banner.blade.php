{{--
    One banner per page (Admin > Banner). Usage: <x-banner page="home" /> — page keys match
    Site Pages' own slugs where one exists (home, rooms, activities, spa, resto-amed-cafe,
    barak-rooftop-and-bar), plus "contact". One query per render; renders nothing if the
    row is missing or inactive.
--}}
@props(['page'])

@php
    $banner = \App\Models\Banner::where('page', $page)->first();
@endphp
@if ($banner && $banner->is_active)
    <x-sections.promo-banner
        :eyebrow="$banner->eyebrow"
        :heading="$banner->heading"
        :body="$banner->body"
        :image="['src' => $banner->background_image, 'alt' => $banner->heading]"
        :content-panel="true"
        :link-label="$banner->link_url ? 'Discover More' : null"
        :link-href="$banner->link_url"
    />
@endif
