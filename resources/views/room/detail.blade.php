@extends('layouts.app')

@section('title', $room->name . ' — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    @php
        // Main image first, then room_images (already sort_order-ordered by the relation),
        // de-duplicated in case the same path was ever used for both.
        $heroImages = collect([$room->image])
            ->merge($room->images->pluck('image'))
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($src) => ['src' => $src, 'alt' => $room->name])
            ->all();

        $searchParams = $bookingEnabled ? array_filter([
            'check_in' => $checkIn, 'check_out' => $checkOut, 'guest' => $guest, 'promo_code' => $promoCode,
        ]) : [];
        $allCartMessages = $bookingEnabled ? array_unique(array_merge($cartMessages, session('cart_messages', []))) : [];
    @endphp

    <x-sections.hero-slider
        title="Booking Now"
        subtitle="The Balinese Style Hotel in Amed Bali"
        title-align="left"
        :images="$heroImages"
    />

    @if ($bookingEnabled)
        @if (session('status') || session('error') || !empty($allCartMessages))
            <div class="room-detail-flash-wrap">
                @if (session('status'))
                    <p class="room-detail-flash room-detail-flash-status">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p class="room-detail-flash room-detail-flash-error">{{ session('error') }}</p>
                @endif
                @foreach ($allCartMessages as $message)
                    <p class="room-detail-flash room-detail-flash-status">{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <div class="room-search-wrap">
            <form class="room-search-panel room-search-panel-detail" method="GET" action="{{ route('room.detail', $room->slug) }}">
                <div class="room-search-field">
                    <label class="room-search-label" for="search-check-in">Check-In</label>
                    <input id="search-check-in" class="room-search-input" type="date" name="check_in" value="{{ $checkIn }}" min="{{ $todayForMin }}">
                    @if ($availabilityErrors?->has('check_in'))
                        <p class="room-search-error">{{ $availabilityErrors->first('check_in') }}</p>
                    @endif
                </div>
                <div class="room-search-field">
                    <label class="room-search-label" for="search-check-out">Check-Out</label>
                    <input id="search-check-out" class="room-search-input" type="date" name="check_out" value="{{ $checkOut }}" min="{{ $todayForMin }}">
                    @if ($availabilityErrors?->has('check_out'))
                        <p class="room-search-error">{{ $availabilityErrors->first('check_out') }}</p>
                    @endif
                </div>
                <div class="room-search-field">
                    <label class="room-search-label" for="search-guest">Guest</label>
                    <input id="search-guest" class="room-search-input" type="number" name="guest" min="1" value="{{ $guest }}">
                </div>
                <div class="room-search-field">
                    <label class="room-search-label" for="search-promo-code">Promo Code</label>
                    <input id="search-promo-code" class="room-search-input" type="text" name="promo_code" value="{{ $promoCode }}">
                    @if ($promoError)
                        <p class="room-search-error">{{ $promoError }}</p>
                    @endif
                </div>
                <x-ui.button type="submit" variant="solid" class="room-search-submit">Search</x-ui.button>
            </form>
        </div>
    @endif

    <div class="room-detail-showcase">
        <div class="room-detail-showcase-photo">
            <img src="{{ \App\Support\CmsImage::url($room->image) }}" alt="{{ $room->name }}" class="room-detail-showcase-img">
        </div>

        <div class="room-detail-showcase-info">
            <h1 class="room-detail-showcase-name">{{ $room->name }}</h1>

            <div class="room-detail-showcase-meta">
                <p class="room-detail-showcase-meta-item">Max {{ $room->max_guests }} {{ \Illuminate\Support\Str::plural('guest', $room->max_guests) }} per room</p>
                <p class="room-detail-showcase-meta-item">Bedding: {{ $room->bedding }}</p>
            </div>

            @if ($room->hotel_information)
                <details class="room-detail-more">
                    <summary class="room-detail-more-trigger">
                        <span class="rule-link-bar" aria-hidden="true"></span>More Information
                    </summary>
                    <div class="room-detail-more-body">
                        <p>{{ $room->hotel_information }}</p>
                    </div>
                </details>
            @endif
        </div>

        <div class="room-detail-showcase-price">
            <p class="room-detail-price-label">Start From</p>
            <p class="room-detail-price-amount">{{ $room->currency }}{{ number_format($room->startingPricePerNight()) }} <span>/ Night</span></p>
            <p class="room-detail-price-unit">Price for 1 Night</p>

            @if ($bookingEnabled && $availabilityResult)
                @if ($availabilityResult['units'] >= 1)
                    <p class="room-detail-availability">
                        {{ $availabilityResult['nights'] }} {{ \Illuminate\Support\Str::plural('night', $availabilityResult['nights']) }}
                        &middot;
                        {{ $availabilityResult['units'] }} {{ \Illuminate\Support\Str::plural('room', $availabilityResult['units']) }} left
                    </p>
                @else
                    <p class="room-detail-availability room-detail-availability-none">Not available for these dates</p>
                @endif
            @endif

            <x-ui.button variant="solid" disabled disabled-reason="Booking flow not defined yet" class="room-detail-price-cta">Book Now</x-ui.button>

            {{-- Booking Feature Flag (Part 2): the enquiry fallback while the real flow is
                 off. Same wa.me mechanism/prefill pattern as room-card's icon link;
                 falls back to Contact so this is never a broken link. --}}
            @unless ($bookingEnabled)
                <x-ui.button href="{{ $enquiryUrl }}" variant="solid" target="_blank" rel="noopener noreferrer" class="room-detail-price-cta">
                    Enquire {{ str_contains($enquiryUrl, 'wa.me') ? 'on WhatsApp' : '' }}
                </x-ui.button>
            @endunless
        </div>
    </div>

    @if ($bookingEnabled || $ratePlanRows->isNotEmpty())
        <div class="room-detail-layout @unless($bookingEnabled) room-detail-layout-solo @endunless">
            @if ($ratePlanRows->isNotEmpty())
                <div class="room-rate-plans @unless($bookingEnabled) room-rate-plans-solo @endunless" aria-label="Rate plans">
                    @foreach ($ratePlanRows as $row)
                        @php $plan = $row['plan']; @endphp
                        <div class="room-rate-plan-row @unless($bookingEnabled) room-rate-plan-row-solo @endunless">
                            <div class="room-rate-plan-guests">
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="room-rate-plan-guest-icon">
                                    <path fill="currentColor" d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12Zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8Z"/>
                                </svg>
                                <span>{{ $plan->max_guests }} &times; (Incl)</span>
                            </div>

                            <div class="room-rate-plan-name">
                                <span>{{ $plan->name }}</span>
                                @if ($plan->description)
                                    <details class="room-rate-plan-info">
                                        <summary aria-label="Package details for {{ $plan->name }}">i</summary>
                                        <div class="room-rate-plan-info-body">{{ $plan->description }}</div>
                                    </details>
                                @endif
                                @if ($bookingEnabled && $row['minUnitsNeeded'])
                                    <p class="room-card-capacity-hint">{{ $guest }} {{ \Illuminate\Support\Str::plural('guest', $guest) }} need at least {{ $row['minUnitsNeeded'] }} {{ \Illuminate\Support\Str::plural('room', $row['minUnitsNeeded']) }}</p>
                                @endif
                            </div>

                            <div class="room-rate-plan-price">
                                @if ($plan->compare_at_price_per_night)
                                    <span class="room-rate-plan-price-compare">{{ $room->currency }}{{ number_format($plan->compare_at_price_per_night) }}</span>
                                @endif
                                <span class="room-rate-plan-price-current">{{ $room->currency }}{{ number_format($plan->price_per_night) }}</span>
                                <span class="room-rate-plan-price-unit">Price for 1 Night</span>
                            </div>

                            @if ($bookingEnabled)
                                <form method="POST" action="{{ route('booking.cart.items.store') }}" class="room-rate-plan-book">
                                    @csrf
                                    <input type="hidden" name="rate_plan_id" value="{{ $plan->id }}">
                                    @foreach ($searchParams as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach

                                    @if ($row['datesChosen'] && $row['maxSelectable'] < 1)
                                        <select class="room-search-input" disabled>
                                            <option>0 Unit</option>
                                        </select>
                                        <x-ui.button type="submit" variant="solid" disabled disabled-reason="Not available for these dates">Book Now</x-ui.button>
                                        <p class="room-detail-availability room-detail-availability-none">Not available for these dates</p>
                                    @else
                                        <select name="units" class="room-search-input">
                                            @for ($i = 1; $i <= $row['maxSelectable']; $i++)
                                                <option value="{{ $i }}" @selected(($row['preselect'] ?? 1) === $i)>{{ $i }} {{ \Illuminate\Support\Str::plural('Unit', $i) }}</option>
                                            @endfor
                                        </select>
                                        <x-ui.button type="submit" variant="solid">Book Now</x-ui.button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($bookingEnabled)
                <aside class="booking-summary" id="booking-summary">
                    <h2 class="booking-summary-heading">Booking Summary</h2>

                    @if (empty($cartItems))
                        <p class="booking-summary-empty">No rooms selected yet.</p>
                    @else
                        @if ($cartDatesDiffer)
                            <div class="booking-summary-notice">
                                <p>Your selection is for {{ \Carbon\Carbon::parse($cart->checkIn())->format('j F Y') }} – {{ \Carbon\Carbon::parse($cart->checkOut())->format('j F Y') }}.</p>
                                <form method="POST" action="{{ route('booking.cart.dates.update') }}">
                                    @csrf
                                    <input type="hidden" name="check_in" value="{{ $checkIn }}">
                                    <input type="hidden" name="check_out" value="{{ $checkOut }}">
                                    <input type="hidden" name="guest" value="{{ $guest }}">
                                    <button type="submit" class="admin-btn admin-btn-outline-dark admin-btn-sm">Update selection to {{ \Carbon\Carbon::parse($checkIn)->format('j F Y') }} – {{ \Carbon\Carbon::parse($checkOut)->format('j F Y') }}</button>
                                </form>
                            </div>
                        @endif

                        @foreach ($cartItems as $item)
                            <div class="booking-summary-row">
                                <div class="booking-summary-row-info">
                                    <p class="booking-summary-row-dates">
                                        {{ \Carbon\Carbon::parse($item['check_in'])->format('l, j F Y') }}
                                        &rarr;
                                        {{ \Carbon\Carbon::parse($item['check_out'])->format('l, j F Y') }}
                                    </p>
                                    <p class="booking-summary-row-nights">{{ $item['nights'] }} {{ \Illuminate\Support\Str::plural('night', $item['nights']) }}</p>
                                    <p class="booking-summary-row-name">{{ $item['room']->name }} &mdash; {{ $item['rate_plan']->name }}</p>
                                    <p class="booking-summary-row-units">{{ $item['units'] }} {{ \Illuminate\Support\Str::plural('unit', $item['units']) }}</p>
                                </div>

                                <div class="booking-summary-row-price">
                                    <p>{{ $item['room']->currency }}{{ number_format($item['subtotal']) }}</p>
                                    <form method="POST" action="{{ route('booking.cart.items.destroy', $item['rate_plan']->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="booking-summary-remove" aria-label="Remove {{ $item['room']->name }} – {{ $item['rate_plan']->name }}">&times;</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach

                        <div class="booking-summary-total">
                            <span>Grand Total</span>
                            <span>{{ $cartItems[0]['room']->currency }}{{ number_format($cartGrandTotal) }}</span>
                        </div>

                        <form method="POST" action="{{ route('booking.cart.checkout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="solid" class="booking-summary-checkout">Make This Booking</x-ui.button>
                        </form>
                    @endif
                </aside>
            @endif
        </div>
    @endif
@endsection
