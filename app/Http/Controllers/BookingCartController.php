<?php

namespace App\Http\Controllers;

use App\Models\RatePlan;
use App\Services\AvailabilityService;
use App\Services\BookingCart;
use App\Services\StayDateValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The public-facing counterpart to BookingCart — every mutation goes through here as a
 * plain CSRF-protected form POST/DELETE (no JS required; see room/detail.blade.php's
 * per-rate-plan forms and the Booking Summary's remove/checkout forms). Every action ends
 * by re-running BookingCart::validate() so the session never holds a cart the DB
 * disagrees with, even momentarily.
 */
class BookingCartController extends Controller
{
    public function store(Request $request, BookingCart $cart, AvailabilityService $availability, StayDateValidator $dates): RedirectResponse
    {
        $data = $request->validate([
            'rate_plan_id' => ['required', 'integer', 'exists:rate_plans,id'],
            'units' => ['required', 'integer', 'min:1'],
            'check_in' => ['nullable', 'string'],
            'check_out' => ['nullable', 'string'],
            'guest' => ['nullable', 'integer', 'min:1'],
            'promo_code' => ['nullable', 'string'],
        ]);

        $ratePlan = RatePlan::with('room')->findOrFail($data['rate_plan_id']);
        $searchParams = array_filter([
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'guest' => $data['guest'] ?? null,
            'promo_code' => $data['promo_code'] ?? null,
        ]);

        if (empty($data['check_in']) || empty($data['check_out'])) {
            return redirect($this->detailUrl($ratePlan->room->slug, $searchParams).'#search-check-in')
                ->with('status', 'Please select your dates first.');
        }

        if (! $dates->isValid($data['check_in'], $data['check_out'])) {
            return redirect($this->detailUrl($ratePlan->room->slug, $searchParams))
                ->with('error', 'Your selected dates are no longer valid. Please search again.');
        }

        // Set as requested, then re-validate immediately — validate() clamps units to the
        // room's real shared availability (across every rate plan of that room) and
        // reports what changed, so this controller never duplicates that arithmetic.
        $cart->setItem((int) $data['rate_plan_id'], (int) $data['units'], $data['check_in'], $data['check_out'], $data['guest'] ?? null);
        $messages = $cart->validate($availability);

        $stillInCart = collect($cart->hydratedItems())->contains(fn ($row) => $row['rate_plan']->id === $ratePlan->id);

        return redirect($this->detailUrl($ratePlan->room->slug, $searchParams).'#booking-summary')
            ->with('status', $stillInCart ? 'Added to your selection.' : 'Not available for these dates.')
            ->with('cart_messages', $messages);
    }

    public function destroy(RatePlan $ratePlan, BookingCart $cart): RedirectResponse
    {
        $cart->removeItem($ratePlan->id);

        return redirect()->back()->with('status', 'Removed from your selection.');
    }

    public function updateDates(Request $request, BookingCart $cart, AvailabilityService $availability, StayDateValidator $dates): RedirectResponse
    {
        $data = $request->validate([
            'check_in' => ['required', 'string'],
            'check_out' => ['required', 'string'],
            'guest' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! $dates->isValid($data['check_in'], $data['check_out'])) {
            return redirect()->back()->with('error', 'Those dates are not valid.');
        }

        $cart->moveToDates($data['check_in'], $data['check_out'], $data['guest'] ?? null);
        $messages = $cart->validate($availability);

        return redirect()->back()
            ->with('status', 'Your selection has been updated to the new dates.')
            ->with('cart_messages', $messages);
    }

    /**
     * Stage 3b's final action: re-checks everything server-side (never trusts what the
     * page last rendered) and reports readiness. Does NOT create bookings/booking_items —
     * that write, inside a DB transaction with a row lock re-checking availability one
     * last time, plus guest details and Midtrans, is Stage 4. This method's success branch
     * is that integration point.
     */
    public function checkout(BookingCart $cart, AvailabilityService $availability): RedirectResponse
    {
        $messages = $cart->validate($availability);

        if ($cart->isEmpty()) {
            return redirect()->back()->with('error', 'Your selection is empty.')->with('cart_messages', $messages);
        }

        $guests = $cart->guests();

        if ($guests !== null) {
            $capacity = $cart->totalCapacity();

            if ($capacity < $guests) {
                return redirect()->back()
                    ->with('error', "Your selection fits {$capacity} guests. Please add rooms for {$guests} guests.")
                    ->with('cart_messages', $messages);
            }
        }

        // --- STAGE 4 INTEGRATION POINT ---
        // All checks passed. Stage 4 replaces this branch with: open a DB transaction,
        // lock/re-check availability one final time, create the `bookings` row (status
        // pending, expires_at set) and its `booking_items` (snapshotting each rate plan's
        // current price_per_night), then redirect into guest details / Midtrans. For now
        // it only reports readiness — no rows are written.
        return redirect()->back()
            ->with('status', 'Your selection is ready. Checkout will be added in the next step.')
            ->with('cart_messages', $messages);
    }

    private function detailUrl(string $roomSlug, array $searchParams): string
    {
        return route('room.detail', array_merge(['room' => $roomSlug], $searchParams));
    }
}
