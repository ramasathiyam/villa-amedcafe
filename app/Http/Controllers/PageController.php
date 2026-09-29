<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DiningVenue;
use App\Models\Event;
use App\Models\Page;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\AvailabilityService;
use App\Services\BookingCart;
use App\Services\PromoCodeValidator;
use App\Services\StayDateValidator;
use App\Support\EnquiryLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Static marketing pages migrated from the Next.js reference (villa-website). Frontend
 * migration stage only — no database, no booking logic. Section order and copy follow
 * that project's page.tsx files and the /design screenshots 1:1.
 */
class PageController extends Controller
{
    private const BOOKING_UNDEFINED = 'Booking flow not defined yet';

    /**
     * Database-driven — see App\Models\Activity::toHomeTeaserArray() (name/description/
     * image only, no price/includes — those belong to activity()'s "Exceptional
     * Experiences" grid below, a different card shape for the same Activity rows).
     * SiteData::homeActivities() is no longer called from here but is left in place
     * (unused, not deleted) in case anything else still depends on it.
     */
    public function home(): View
    {
        $homeActivities = Activity::where('is_active', true)
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->get();

        return view('home.index', [
            'homeActivities' => $homeActivities->map->toHomeTeaserArray(),
            'visibleEvents' => Event::visibleOnHome()->get(),
            'page' => Page::where('slug', 'home')->firstOrFail(),
        ]);
    }

    /**
     * Database-driven — see App\Models\Room::toRoomCardArray() for how Eloquent rows
     * are mapped onto the array shape the room-card component expects. spa() and
     * contact() below are still static (no model, unchanged).
     *
     * Single query with `images` eager-loaded, then split in PHP into the featured room
     * (the Family Room composite section) vs the regular grid — avoids N+1 and avoids a
     * second round-trip just to separate one row from the rest.
     */
    public function room(Request $request, AvailabilityService $availability, StayDateValidator $dates): View
    {
        $bookingEnabled = config('booking.enabled');

        // ratePlans eager-loaded (active only) alongside images — Room::startingPricePerNight()
        // reads this in-memory collection, so this stays one query for all rooms, not N+1.
        // Kept even when booking is off: "Start From" price still needs it.
        $allRooms = Room::with([
            'images',
            'ratePlans' => fn ($query) => $query->where('is_active', true),
        ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $checkIn = $checkOut = $guest = null;
        $availabilityErrors = null;
        $checkInDate = null;
        $checkOutDate = null;

        // Booking Feature Flag: search/availability query params are only read (and only
        // produce errors/labels/hints) when the flow is enabled — otherwise they're
        // silently ignored, exactly as if they weren't in the URL at all.
        if ($bookingEnabled) {
            $checkIn = $request->query('check_in');
            $checkOut = $request->query('check_out');
            $guest = $this->parseGuestCount($request->query('guest'));

            if ($checkIn !== null || $checkOut !== null) {
                $validator = $dates->validate($checkIn, $checkOut);

                if ($validator->fails()) {
                    $availabilityErrors = $validator->errors();
                } else {
                    [$checkInDate, $checkOutDate] = $dates->parse($checkIn, $checkOut);
                }
            }
        }

        // Stage 3b decision: no longer hides rooms below the requested guest count — every
        // room still shows, each with its own per-unit capacity and (when short) a "needs
        // at least N rooms" hint instead. Applied identically to the Family Room composite
        // below, since it's drawn from the same collection before splitting.
        $featuredRoom = $allRooms->firstWhere('is_featured', true);
        $rooms = $allRooms->where('is_featured', false)->values();

        // Resolved once here (not inside room-card.blade.php, which used to call this per
        // card — Part 4 cleanup) regardless of the flag, since the enquiry link on /room's
        // cards is unrelated to the booking flow itself.
        $whatsappNumber = Setting::phoneDigits('whatsapp');

        // OTA settings + their logo file checks are each resolved once per request here,
        // not per room card — mirrors $whatsappNumber above. File names are lowercase and
        // checked exactly as-is (production servers are case-sensitive). The directory
        // checked is config('ota.logo_path') (defaults to public/images/ota) rather than a
        // hardcoded public_path() call, so tests can point it at a temporary directory
        // instead of ever touching the real logo files. The web-facing asset path stays
        // fixed at images/ota/*.png regardless, since that's always where the file is
        // actually served from in production.
        $otaLogoDir = config('ota.logo_path');
        $expediaUrl = Setting::get('expedia_url');
        $bookingComUrl = Setting::get('booking_com_url');
        $agodaUrl = Setting::get('agoda_url');
        $expediaLogoPath = File::exists($otaLogoDir.'/expedia.png') ? 'images/ota/expedia.png' : null;
        $bookingComLogoPath = File::exists($otaLogoDir.'/booking-com.png') ? 'images/ota/booking-com.png' : null;
        $agodaLogoPath = File::exists($otaLogoDir.'/agoda.png') ? 'images/ota/agoda.png' : null;

        // At most one availability query per room (AvailabilityService::availableUnits()
        // is itself a single query) — never one per night.
        $roomCards = $rooms->map(function (Room $room) use ($checkInDate, $checkOutDate, $availability, $guest, $bookingEnabled) {
            $card = $room->toRoomCardArray();
            $card['minUnitsNeeded'] = $bookingEnabled ? $this->minUnitsNeeded($guest, $room->max_guests) : null;

            if ($bookingEnabled && $checkInDate && $checkOutDate) {
                $card['availability'] = [
                    'nights' => $availability->nights($checkInDate, $checkOutDate),
                    'units' => $availability->availableUnits($room, $checkInDate, $checkOutDate),
                ];
            }

            return $card;
        });

        $featuredAvailability = null;
        if ($bookingEnabled && $featuredRoom && $checkInDate && $checkOutDate) {
            $featuredAvailability = [
                'nights' => $availability->nights($checkInDate, $checkOutDate),
                'units' => $availability->availableUnits($featuredRoom, $checkInDate, $checkOutDate),
            ];
        }

        return view('room.index', [
            'rooms' => $roomCards,
            'featuredRoom' => $featuredRoom,
            'featuredMinUnitsNeeded' => ($bookingEnabled && $featuredRoom) ? $this->minUnitsNeeded($guest, $featuredRoom->max_guests) : null,
            'featuredAvailability' => $featuredAvailability,
            'page' => Page::where('slug', 'rooms')->firstOrFail(),
            'bookingEnabled' => $bookingEnabled,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'guest' => $guest,
            'availabilityErrors' => $availabilityErrors,
            'todayForMin' => $bookingEnabled ? $dates->todayForMin() : null,
            'whatsappNumber' => $whatsappNumber,
            'expediaUrl' => $expediaUrl,
            'expediaLogoPath' => $expediaLogoPath,
            'bookingComUrl' => $bookingComUrl,
            'bookingComLogoPath' => $bookingComLogoPath,
            'agodaUrl' => $agodaUrl,
            'agodaLogoPath' => $agodaLogoPath,
        ]);
    }

    /**
     * Manual slug lookup (not implicit {room:slug} route-model binding) so this route's
     * public-only is_active scope stays independent of Admin's {room} routes, which must
     * keep binding by id and allow inactive rooms — mirrors restoAmedCafe()/
     * barakRooftopAndBar()'s exact pattern above.
     */
    public function roomDetail(
        string $room,
        Request $request,
        AvailabilityService $availability,
        StayDateValidator $dates,
        BookingCart $cart,
        PromoCodeValidator $promoValidator
    ): View {
        $bookingEnabled = config('booking.enabled');

        $room = Room::with([
            'images',
            'ratePlans' => fn ($query) => $query->where('is_active', true),
        ])
            ->where('slug', $room)
            ->where('is_active', true)
            ->firstOrFail();

        $checkIn = $checkOut = $guest = $promoCode = null;
        $availabilityErrors = null;
        $availabilityResult = null;
        $checkInDate = null;
        $checkOutDate = null;
        $promoError = null;
        $cartMessages = [];
        $cartDatesDiffer = false;

        // Booking Feature Flag: when off, every search param is ignored (no validation, no
        // labels, no cart involvement at all) — the room's own information still renders
        // from the query above regardless.
        if ($bookingEnabled) {
            $checkIn = $request->query('check_in');
            $checkOut = $request->query('check_out');
            $guest = $this->parseGuestCount($request->query('guest'));
            $promoCode = $request->query('promo_code');

            if ($checkIn !== null || $checkOut !== null) {
                $validator = $dates->validate($checkIn, $checkOut);

                if ($validator->fails()) {
                    $availabilityErrors = $validator->errors();
                } else {
                    [$checkInDate, $checkOutDate] = $dates->parse($checkIn, $checkOut);

                    $availabilityResult = [
                        'nights' => $availability->nights($checkInDate, $checkOutDate),
                        'units' => $availability->availableUnits($room, $checkInDate, $checkOutDate),
                    ];
                }
            }

            // Invalid promo codes never block dates/rate-plan rendering — just their own
            // field's message (Part 0B).
            $promoError = (filled($promoCode) && ! $promoValidator->isValid($promoCode))
                ? 'This promo code is not valid.'
                : null;

            // Runs on every render (Part 3) — prunes/adjusts stale items, never touches the
            // cart's own dates (that's only ever the explicit "Update selection" POST, Part 2).
            $cartMessages = $cart->validate($availability);

            $cartDatesDiffer = ! $cart->isEmpty()
                && $checkInDate && $checkOutDate
                && ($cart->checkIn() !== $checkIn || $cart->checkOut() !== $checkOut);
        }

        $ratePlanRows = $room->ratePlans->map(function (RatePlan $plan) use ($room, $checkInDate, $checkOutDate, $availability, $guest, $cart, $bookingEnabled) {
            $row = ['plan' => $plan, 'minUnitsNeeded' => $bookingEnabled ? $this->minUnitsNeeded($guest, $plan->max_guests) : null];

            if (! $bookingEnabled || ! $checkInDate || ! $checkOutDate) {
                $row['maxSelectable'] = $room->total_units;
                $row['datesChosen'] = false;

                return $row;
            }

            $alreadyInCart = $cart->unitsInCartForRoom($room->id, $plan->id);
            $available = max(0, $availability->availableUnits($room, $checkInDate, $checkOutDate) - $alreadyInCart);

            $row['datesChosen'] = true;
            $row['maxSelectable'] = $available;
            $row['preselect'] = min($available, max(1, $this->minUnitsNeeded($guest, $plan->max_guests) ?? 1));

            return $row;
        });

        return view('room.detail', [
            'room' => $room,
            'bookingEnabled' => $bookingEnabled,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'guest' => $guest,
            'promoCode' => $promoCode,
            'promoError' => $promoError,
            'availabilityErrors' => $availabilityErrors,
            'availabilityResult' => $availabilityResult,
            'ratePlanRows' => $ratePlanRows,
            'cart' => $cart,
            'cartItems' => $bookingEnabled ? $cart->hydratedItems() : [],
            'cartGrandTotal' => $bookingEnabled ? $cart->grandTotal() : 0,
            'cartMessages' => $cartMessages,
            'cartDatesDiffer' => $cartDatesDiffer,
            // Client-side convenience only (native date-picker min); server-side validation
            // above is authoritative.
            'todayForMin' => $bookingEnabled ? $dates->todayForMin() : null,
            'enquiryUrl' => $this->enquiryUrl($room),
        ]);
    }

    /**
     * The Part 2 fallback CTA when booking is off — same wa.me construction as room-card's
     * icon link, prefilled with this room's name. Falls back to the Contact page (never a
     * broken tel:/wa.me link) when no WhatsApp number is set in Site Settings.
     */
    private function enquiryUrl(Room $room): string
    {
        return EnquiryLink::build($room->name, Setting::phoneDigits('whatsapp'), route('contact'));
    }

    /**
     * Shared by /room's Guest field and /room/{slug}'s (Part 0A) — a bare positive
     * integer, or null for anything blank/non-numeric/zero/negative (silently ignored,
     * not a validation error — Guest has always been optional and best-effort).
     */
    private function parseGuestCount(?string $guest): ?int
    {
        return ($guest !== null && is_numeric($guest) && (int) $guest > 0) ? (int) $guest : null;
    }

    /**
     * ceil(guests / capacity) — null when guests is unknown or already fits in one unit,
     * so callers can treat a non-null result as "show the hint".
     */
    private function minUnitsNeeded(?int $guests, int $capacity): ?int
    {
        if ($guests === null || $capacity < 1 || $guests <= $capacity) {
            return null;
        }

        return (int) ceil($guests / $capacity);
    }

    /**
     * Database-driven — see App\Models\Activity::toActivityCardArray(). The
     * "Exceptional Experiences" grid on this page filters by show_on_activity_page; Home's
     * teaser cards (home() above) filter the same table by show_on_home instead.
     */
    public function activity(): View
    {
        $exceptionalExperiences = Activity::where('is_active', true)
            ->where('show_on_activity_page', true)
            ->orderBy('sort_order')
            ->get();

        return view('activity.index', [
            'exceptionalExperiences' => $exceptionalExperiences->map->toActivityCardArray(),
            'page' => Page::where('slug', 'activities')->firstOrFail(),
        ]);
    }

    public function spa(): View
    {
        return view('spa.index', [
            'page' => Page::where('slug', 'spa')->firstOrFail(),
        ]);
    }

    public function contact(): View
    {
        return view('contact.index');
    }

    /**
     * Database-driven — see App\Models\DiningVenue::imagesByGroup() for how gallery
     * photos are split by section without extra queries. Eager loads images+items in
     * the same query set as firstOrFail(), so this is 3 total queries (venue, images,
     * items), not N+1. Home's Dining-related content (none currently) is unaffected —
     * this only touches the two /dining/* routes.
     *
     * Hero/intro content now comes from Page (Site Pages), not DiningVenue's own
     * hero_title/hero_subtitle/hero_image/intro_heading/intro_body columns — those
     * columns still exist on dining_venues (untouched) but are no longer read here;
     * $venue is still the source for images/items/gallery, which stay in Dining CMS.
     */
    public function restoAmedCafe(): View
    {
        $venue = DiningVenue::with(['images', 'items'])
            ->where('slug', 'resto-amed-cafe')
            ->where('is_active', true)
            ->firstOrFail();

        return view('dining.resto-amed-cafe', [
            'venue' => $venue,
            'testimonials' => $this->venueTestimonials($venue),
            'page' => Page::where('slug', 'resto-amed-cafe')->firstOrFail(),
        ]);
    }

    public function barakRooftopAndBar(): View
    {
        $venue = DiningVenue::with(['images', 'items'])
            ->where('slug', 'barak-rooftop-and-bar')
            ->where('is_active', true)
            ->firstOrFail();

        return view('dining.barak-rooftop-and-bar', [
            'venue' => $venue,
            'testimonials' => $this->venueTestimonials($venue),
            'page' => Page::where('slug', 'barak-rooftop-and-bar')->firstOrFail(),
        ]);
    }

    /**
     * Global (dining_venue_id NULL) testimonials plus this venue's own — see
     * Testimonial::scopeApplicableToVenue() for why this can't be a simple eager-loaded
     * HasMany off DiningVenue.
     */
    private function venueTestimonials(DiningVenue $venue)
    {
        return Testimonial::query()
            ->applicableToVenue($venue->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
