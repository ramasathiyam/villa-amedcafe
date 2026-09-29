@props([
    'roomName',
    'expediaUrl' => null,
    'expediaLogoPath' => null,
    'bookingComUrl' => null,
    'bookingComLogoPath' => null,
    'agodaUrl' => null,
    'agodaLogoPath' => null,
    'rowClass' => 'room-card-ota-row',
])

@if ($expediaUrl || $bookingComUrl || $agodaUrl)
    <div class="{{ $rowClass }}">
        @if ($expediaUrl)
            <a
                class="room-card-ota-button"
                href="{{ $expediaUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Book {{ $roomName }} on Expedia (opens in a new tab)"
            >
                @if ($expediaLogoPath)
                    <img src="{{ asset($expediaLogoPath) }}" alt="" class="room-card-ota-image">
                @else
                    <span class="room-card-ota-text" aria-hidden="true">Expedia</span>
                @endif
            </a>
        @endif

        @if ($bookingComUrl)
            <a
                class="room-card-ota-button"
                href="{{ $bookingComUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Book {{ $roomName }} on Booking.com (opens in a new tab)"
            >
                @if ($bookingComLogoPath)
                    <img src="{{ asset($bookingComLogoPath) }}" alt="" class="room-card-ota-image">
                @else
                    <span class="room-card-ota-text" aria-hidden="true">Booking.com</span>
                @endif
            </a>
        @endif

        @if ($agodaUrl)
            <a
                class="room-card-ota-button"
                href="{{ $agodaUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Book {{ $roomName }} on Agoda (opens in a new tab)"
            >
                @if ($agodaLogoPath)
                    <img src="{{ asset($agodaLogoPath) }}" alt="" class="room-card-ota-image">
                @else
                    <span class="room-card-ota-text" aria-hidden="true">Agoda</span>
                @endif
            </a>
        @endif
    </div>
@endif
