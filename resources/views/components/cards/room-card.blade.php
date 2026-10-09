@props([
    'room',
    'whatsappNumber' => null,
    'bookingEnabled' => false,
    'expediaUrl' => null,
    'expediaLogoPath' => null,
    'bookingComUrl' => null,
    'bookingComLogoPath' => null,
    'agodaUrl' => null,
    'agodaLogoPath' => null,
])

@php
    // Falls back to resolving its own copy only if a caller ever uses this component
    // without passing whatsappNumber (PageController::room() normally resolves this once
    // for the whole listing — Part 4 cleanup, was previously one query per card here).
    $waNumber = $whatsappNumber ?? \App\Models\Setting::phoneDigits('whatsapp');
    // Carries the current search (dates + guests, if any) through to the detail page so
    // it opens already showing the same search — but only when booking is enabled; the
    // flag being off must mean no search params leak into this link even if they're
    // still sitting in the current URL's query string.
    $detailUrl = $bookingEnabled
        ? route('room.detail', array_filter([
            'room' => $room['id'],
            'check_in' => request('check_in'),
            'check_out' => request('check_out'),
            'guest' => request('guest'),
        ]))
        : route('room.detail', ['room' => $room['id']]);
@endphp

<article class="room-card">
    <div class="room-card-image-wrap">
        <img src="{{ \App\Support\CmsImage::url($room['image']) }}" alt="{{ $room['name'] }}" class="room-card-image">
        <x-ui.price-badge :amount="$room['currency'] . number_format($room['ratePerNight'])" unit="Night" class="room-card-badge" />
        <x-cards.book-button
            :room-name="$room['name']"
            :detail-url="$detailUrl"
            :whatsapp-number="$waNumber"
            :booking-enabled="$bookingEnabled"
            button-class="room-card-book"
        />
    </div>

    <div class="room-card-body">
        <h3 class="room-card-name"><a href="{{ $detailUrl }}">{{ $room['name'] }}</a></h3>
        <p class="room-card-meta">Max {{ $room['maxGuests'] }} {{ \Illuminate\Support\Str::plural('guest', $room['maxGuests']) }} per room</p>
        <p class="room-card-meta">Room Size: {{ $room['sizeSqm'] }} m²</p>
        <p class="room-card-meta">Bedding: {{ $room['bedding'] }}</p>
        <x-ui.rule-link :href="$detailUrl" aria-label="More information about {{ $room['name'] }}">More Information</x-ui.rule-link>

        @if (!empty($room['minUnitsNeeded']))
            <p class="room-card-capacity-hint">
                {{ request('guest') }} {{ \Illuminate\Support\Str::plural('guest', (int) request('guest')) }} need at least {{ $room['minUnitsNeeded'] }} {{ \Illuminate\Support\Str::plural('room', $room['minUnitsNeeded']) }}
            </p>
        @endif

        @if (!empty($room['availability']))
            @if ($room['availability']['units'] >= 1)
                <p class="room-detail-availability">
                    {{ $room['availability']['nights'] }} {{ \Illuminate\Support\Str::plural('night', $room['availability']['nights']) }}
                    &middot;
                    {{ $room['availability']['units'] }} {{ \Illuminate\Support\Str::plural('room', $room['availability']['units']) }} left
                </p>
            @else
                <p class="room-detail-availability room-detail-availability-none">Not available for these dates</p>
            @endif
        @endif
    </div>

    <x-cards.room-booking-buttons
        :room-name="$room['name']"
        :expedia-url="$expediaUrl"
        :expedia-logo-path="$expediaLogoPath"
        :booking-com-url="$bookingComUrl"
        :booking-com-logo-path="$bookingComLogoPath"
        :agoda-url="$agodaUrl"
        :agoda-logo-path="$agodaLogoPath"
    />
</article>
