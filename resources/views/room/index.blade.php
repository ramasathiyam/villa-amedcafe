@extends('layouts.app')

@section('title', 'Rooms — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    <x-sections.hero
        :title="$page->hero_title"
        :subtitle="$page->hero_subtitle"
        :image="['src' => $page->hero_image, 'alt' => 'Close-up of a Balinese-carved bed headboard with patterned cushions']"
    />

    @if ($bookingEnabled)
        <x-sections.room-search-bar
            :check-in="$checkIn"
            :check-out="$checkOut"
            :guest="$guest"
            :errors="$availabilityErrors"
            :today-for-min="$todayForMin"
        />
    @endif

    <x-sections.intro-section
        :heading="$page->intro_heading"
        :body="$page->intro_body"
        link-label="Book Now"
        :link-href="route('room')"
    />

    @if ($rooms->isNotEmpty())
        <div class="room-grid" data-reveal-group>
            @foreach ($rooms as $index => $room)
                <div data-reveal-item style="--stagger-i: {{ min($index, 6) }}">
                    <x-cards.room-card
                        :room="$room"
                        :whatsapp-number="$whatsappNumber"
                        :booking-enabled="$bookingEnabled"
                        :expedia-url="$expediaUrl"
                        :expedia-logo-path="$expediaLogoPath"
                        :booking-com-url="$bookingComUrl"
                        :booking-com-logo-path="$bookingComLogoPath"
                        :agoda-url="$agodaUrl"
                        :agoda-logo-path="$agodaLogoPath"
                    />
                </div>
            @endforeach
        </div>
    @endif

    {{-- Family Room + Hotel Information composite — one-off layout from design/ROOM.png,
         not worth abstracting into a shared component for a single occurrence.
         Database-driven: $featuredRoom is the Room where is_featured=true (PageController),
         its detail photos come from the room_images relation (room_images.sort_order),
         not hardcoded paths. Guarded with @if since is_featured is optional CMS data,
         not something the page can assume will always be set. --}}
    @if ($featuredRoom)
        @php
            // Carries the current search dates through to the detail page, same as each
            // room-card's title link — see room-card.blade.php.
            $featuredRoomDetailUrl = route('room.detail', array_filter([
                'room' => $featuredRoom->slug,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guest' => $guest,
            ]));
        @endphp
        <div class="room-family">
            <div class="room-family-photo">
                <img src="{{ \App\Support\CmsImage::url($featuredRoom->image) }}" alt="{{ $featuredRoom->name }} bed with carved wooden headboard and folded towel elephants" class="room-family-photo-img" data-reveal="image">
                <a href="{{ $featuredRoomDetailUrl }}" class="room-family-label">{{ $featuredRoom->name }}</a>
                <x-ui.price-badge :amount="$featuredRoom->currency . number_format($featuredRoom->startingPricePerNight())" unit="Night" class="room-family-badge" />
                <x-cards.book-button
                    :room-name="$featuredRoom->name"
                    :detail-url="$featuredRoomDetailUrl"
                    :whatsapp-number="$whatsappNumber"
                    :booking-enabled="$bookingEnabled"
                    button-class="room-family-book"
                />
            </div>

            <div class="room-family-detail-col">
                @foreach ($featuredRoom->images as $detailImage)
                    <div class="room-family-detail-photo">
                        <img src="{{ \App\Support\CmsImage::url($detailImage->image) }}" alt="{{ $detailImage->alt_text }}" class="room-family-detail-img" data-reveal="image">
                    </div>
                @endforeach
            </div>

            <div class="room-family-info" data-reveal="fade-up">
                <x-ui.section-heading as="h2" align="left" :uppercase="false">Hotel Information</x-ui.section-heading>
                <p>{{ $featuredRoom->hotel_information }}</p>

                <div class="room-family-meta">
                    <p class="room-family-meta-item">Max {{ $featuredRoom->max_guests }} {{ \Illuminate\Support\Str::plural('guest', $featuredRoom->max_guests) }} per room</p>
                    <p class="room-family-meta-item">Room Size: {{ $featuredRoom->size_sqm }} m²</p>
                    <p class="room-family-meta-item">Bedding: {{ $featuredRoom->bedding }}</p>
                </div>

                @if ($featuredMinUnitsNeeded)
                    <p class="room-card-capacity-hint">
                        {{ $guest }} {{ \Illuminate\Support\Str::plural('guest', $guest) }} need at least {{ $featuredMinUnitsNeeded }} {{ \Illuminate\Support\Str::plural('room', $featuredMinUnitsNeeded) }}
                    </p>
                @endif

                @if ($featuredAvailability)
                    @if ($featuredAvailability['units'] >= 1)
                        <p class="room-detail-availability">
                            {{ $featuredAvailability['nights'] }} {{ \Illuminate\Support\Str::plural('night', $featuredAvailability['nights']) }}
                            &middot;
                            {{ $featuredAvailability['units'] }} {{ \Illuminate\Support\Str::plural('room', $featuredAvailability['units']) }} left
                        </p>
                    @else
                        <p class="room-detail-availability room-detail-availability-none">Not available for these dates</p>
                    @endif
                @endif

                <div class="room-family-footer">
                    <x-cards.room-booking-buttons
                        :room-name="$featuredRoom->name"
                        :expedia-url="$expediaUrl"
                        :expedia-logo-path="$expediaLogoPath"
                        :booking-com-url="$bookingComUrl"
                        :booking-com-logo-path="$bookingComLogoPath"
                        :agoda-url="$agodaUrl"
                        :agoda-logo-path="$agodaLogoPath"
                        row-class="room-family-ota-row"
                    />
                    <x-ui.rule-link :href="$featuredRoomDetailUrl" aria-label="More information about {{ $featuredRoom->name }}">More Information</x-ui.rule-link>
                </div>
            </div>
        </div>
    @endif

    <x-banner page="rooms" />
@endsection
