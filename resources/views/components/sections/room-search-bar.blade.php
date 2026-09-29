@props([
    'checkIn' => null,
    'checkOut' => null,
    'guest' => null,
    'errors' => null,
    'todayForMin' => null,
])

<div class="room-search-wrap">
    <form class="room-search-panel room-search-panel-listing" method="GET" action="{{ route('room') }}">
        <div class="room-search-field">
            <label class="room-search-label" for="room-search-check-in">Check-In</label>
            <input id="room-search-check-in" class="room-search-input" type="date" name="check_in" value="{{ $checkIn }}" min="{{ $todayForMin }}">
            @if ($errors?->has('check_in'))
                <p class="room-search-error">{{ $errors->first('check_in') }}</p>
            @endif
        </div>
        <div class="room-search-field">
            <label class="room-search-label" for="room-search-check-out">Check-Out</label>
            <input id="room-search-check-out" class="room-search-input" type="date" name="check_out" value="{{ $checkOut }}" min="{{ $todayForMin }}">
            @if ($errors?->has('check_out'))
                <p class="room-search-error">{{ $errors->first('check_out') }}</p>
            @endif
        </div>
        <div class="room-search-field">
            <label class="room-search-label" for="room-search-guest">Guest</label>
            <input id="room-search-guest" class="room-search-input" type="number" name="guest" min="1" value="{{ $guest }}">
        </div>

        <x-ui.button type="submit" variant="solid" class="room-search-submit">Search</x-ui.button>
    </form>
</div>
