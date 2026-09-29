@extends('layouts.admin')

@php
    // Central place mapping a Page's slug to its display label, public URL, and (where
    // relevant) a pointer to the Admin section that still owns other, non-hero/intro
    // content for that same public page — e.g. Dining still owns Menu Highlights/
    // Signature Cocktails/Photo Gallery for the two dining pages.
    $pageMeta = [
        'home' => ['label' => 'Home', 'publicPath' => '/'],
        'rooms' => ['label' => 'Rooms', 'publicPath' => '/room', 'otherContent' => 'individual rooms — managed in Rooms'],
        'activities' => ['label' => 'Activities', 'publicPath' => '/activity', 'otherContent' => 'individual activities — managed in Activities'],
        'spa' => ['label' => 'Spa', 'publicPath' => '/spa'],
        'resto-amed-cafe' => ['label' => 'Resto Amed Café', 'publicPath' => '/dining/resto-amed-cafe', 'otherContent' => 'Menu Highlights, Signature Cocktails, or the Photo Gallery — managed in Dining'],
        'barak-rooftop-and-bar' => ['label' => 'Barak Rooftop & Bar', 'publicPath' => '/dining/barak-rooftop-and-bar', 'otherContent' => 'Menu Highlights, Signature Cocktails, or the Photo Gallery — managed in Dining'],
    ];
    $meta = $pageMeta[$page->slug] ?? ['label' => ucfirst($page->slug), 'publicPath' => '/'.$page->slug];
@endphp

@section('title', 'Edit '.$meta['label'].' Page')

@section('content')
    <x-admin.shell active="pages">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Edit {{ $meta['label'] }} Page</h1>
            </div>
        </div>

        {{-- <p class="admin-hint">
            This manages the hero and introduction content shown on the public
            <code>{{ $meta['publicPath'] }}</code>
            page.
            @if (!empty($meta['otherContent']))
                It does not affect {{ $meta['otherContent'] }}.
            @endif
        </p> --}}

        <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @csrf
            @method('PUT')

            {{-- <h2 class="admin-subheading-strong">Hero</h2> --}}

            <div class="admin-form-row">
                <div class="admin-field">
                    <label class="admin-label" for="hero_title">Hero Title</label>
                    <input id="hero_title" class="admin-input" type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required>
                    @error('hero_title') <p class="admin-field-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="hero_subtitle">Hero Subtitle</label>
                    <input id="hero_subtitle" class="admin-input" type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $page->hero_subtitle) }}">
                    @error('hero_subtitle') <p class="admin-field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="admin-field">
                <label class="admin-label" for="hero_image">Hero Photo</label>

                @if ($page->hero_image)
                    <div class="admin-image-preview">
                        <img src="{{ \App\Support\CmsImage::url($page->hero_image) }}" alt="{{ $page->hero_title }}">
                    </div>
                @endif

                <input id="hero_image" class="admin-input" type="file" name="hero_image" accept="image/*">
                <p class="admin-hint">JPG, PNG, or WebP. Max 4MB. Leave empty to keep the current photo.</p>
                @error('hero_image') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <h2 class="admin-subheading-strong">Introduction</h2>

            <div class="admin-field">
                <label class="admin-label" for="intro_heading">Intro Heading</label>
                <input id="intro_heading" class="admin-input" type="text" name="intro_heading" value="{{ old('intro_heading', $page->intro_heading) }}" required>
                @error('intro_heading') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="intro_body">Intro Body</label>
                <textarea id="intro_body" class="admin-textarea" name="intro_body" rows="4" required>{{ old('intro_body', $page->intro_body) }}</textarea>
                @error('intro_body') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-solid">Save Changes</button>
            </div>
        </form>
    </x-admin.shell>
@endsection
