@extends('layouts.admin')

@section('title', 'Edit Dining Venue')

@section('content')
    @php
        // Only "menu_highlight" content (currently Barak only) gets Name/Description/Sort
        // — it's real caption content, not just a photo. Every other group (Resto's
        // gallery_1/feature/gallery_2, Barak's feature/gallery_1) is a plain photo strip:
        // Photo + Actions only, no Name/Description/Sort/Edit/Save. This is driven by the
        // GROUP itself, not the venue, so it stays correct if group usage changes later.
        $menuHighlightImages = $dining->imagesByGroup('menu_highlight');
        // $plainGroups = $dining->images->pluck('group')->unique()->reject(fn ($g) => $g === 'menu_highlight')->values();
        $plainGroups = $dining->images->pluck('group')->unique()->reject(fn ($g) => in_array($g, ['menu_highlight', 'feature']))->values();
        $venueGroupTitles = [
        'resto-amed-cafe' => [
                'gallery_1' => 'Resto Ambience & Dining Area', // Nama khusus Resto Amed Café
                'gallery_2' => 'Beverage & Bar Section',
            ],
            'barak-rooftop-and-bar' => [
                'gallery_1' => 'Rooftop Sunset & Night View',  // Nama khusus Barak Rooftop & Bar
                // 'gallery_2' => 'Cocktail Lounge',
            ],
        ];
    @endphp

    <x-admin.shell active="dining">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Edit {{ $dining->name }}</h1>
            </div>
        </div>

        <p class="admin-hint">
            Hero and Introduction content for this venue's public page is managed in
            <a href="{{ route('admin.pages.edit', $dining->slug) }}">Site Pages → {{ $dining->name }}</a>.
        </p>

        @if ($menuHighlightImages->isNotEmpty())
            @include('admin.dining._gallery-group-table', [
                'dining' => $dining,
                'group' => 'menu_highlight',
                'heading' => 'Menu Highlights (Food Strip)',
                'images' => $menuHighlightImages,
            ])
        @endif

        @if ($dining->slug === 'barak-rooftop-and-bar')
            @include('admin.dining._items-table', [
                'dining' => $dining,
                'heading' => 'Signature Cocktails',
            ])
        @endif

        {{-- @foreach ($plainGroups as $group)
            @include('admin.dining._gallery-photo-only-table', [
                'dining' => $dining,
                'group' => $group,
                'heading' => str($group)->replace('_', ' ')->title(),
                'images' => $dining->imagesByGroup($group),
            ])
        @endforeach --}}

        @foreach ($plainGroups as $group)
            @continue($group === 'feature')
            @php
                $customHeading = $venueGroupTitles[$dining->slug][$group] 
                    ?? str($group)->replace('_', ' ')->title();
            @endphp

            @include('admin.dining._gallery-photo-only-table', [
                'dining'  => $dining,
                'group'   => $group,
                'heading' => $customHeading,
                'images'  => $dining->imagesByGroup($group),
            ])
        @endforeach
    </x-admin.shell>
@endsection
