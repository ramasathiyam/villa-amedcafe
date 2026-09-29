<?php

namespace App\Services;

use App\Models\RatePlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

/**
 * The only place cart state is read or written. Session-backed, one date range shared by
 * every item (Stage 3b decision — see room-detail.png's single Booking Summary). Prices
 * are never stored here: every total is computed fresh from RatePlan::price_per_night at
 * render time, so an admin changing a price is reflected immediately and Stage 4 can
 * snapshot the real price at booking time without this cart lying about it first.
 *
 * Shape kept in session under SESSION_KEY:
 *   ['check_in' => ?string, 'check_out' => ?string, 'guests' => ?int,
 *    'items' => [['rate_plan_id' => int, 'units' => int], ...]]
 */
class BookingCart
{
    private const SESSION_KEY = 'booking_cart';

    public function checkIn(): ?string
    {
        return $this->raw()['check_in'];
    }

    public function checkOut(): ?string
    {
        return $this->raw()['check_out'];
    }

    public function guests(): ?int
    {
        return $this->raw()['guests'];
    }

    public function isEmpty(): bool
    {
        return empty($this->raw()['items']);
    }

    public function itemCount(): int
    {
        return count($this->raw()['items']);
    }

    /**
     * Explicit date/guest move — the only method Part 2's "Update selection to {dates}"
     * POST action calls. Never invoked from a GET render.
     */
    public function moveToDates(string $checkIn, string $checkOut, ?int $guests): void
    {
        $cart = $this->raw();
        $cart['check_in'] = $checkIn;
        $cart['check_out'] = $checkOut;
        $cart['guests'] = $guests;
        $this->save($cart);
    }

    /**
     * Adds a rate plan to the cart, or — if it's already there — SETS its units to the
     * new value (never adds to the existing count). room_id is intentionally never taken
     * from input; it's derived from the rate plan wherever it's needed. If the submitted
     * dates differ from the cart's current ones, the whole cart moves to them first (Part
     * 2's "adding an item with different dates also moves the cart").
     */
    public function setItem(int $ratePlanId, int $units, string $checkIn, string $checkOut, ?int $guests): void
    {
        $cart = $this->raw();

        if ($cart['check_in'] !== $checkIn || $cart['check_out'] !== $checkOut) {
            $cart['check_in'] = $checkIn;
            $cart['check_out'] = $checkOut;
            $cart['guests'] = $guests;
        } elseif ($guests !== null) {
            $cart['guests'] = $guests;
        }

        $items = $cart['items'];
        $index = null;

        foreach ($items as $i => $item) {
            if ($item['rate_plan_id'] === $ratePlanId) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $items[$index]['units'] = $units;
        } else {
            $items[] = ['rate_plan_id' => $ratePlanId, 'units' => $units];
        }

        $cart['items'] = array_values($items);
        $this->save($cart);
    }

    public function removeItem(int $ratePlanId): void
    {
        $cart = $this->raw();
        $cart['items'] = collect($cart['items'])
            ->reject(fn ($item) => $item['rate_plan_id'] === $ratePlanId)
            ->values()
            ->all();
        $this->save($cart);
    }

    /**
     * Units of $roomId already committed in the cart, optionally excluding one rate plan
     * (so a plan's own row can compute "how much room is left for ME" without counting
     * itself twice). Used by the unit-selector range on /room/{slug}.
     */
    public function unitsInCartForRoom(int $roomId, ?int $excludingRatePlanId = null): int
    {
        $total = 0;

        foreach ($this->raw()['items'] as $item) {
            if ($excludingRatePlanId !== null && $item['rate_plan_id'] === $excludingRatePlanId) {
                continue;
            }

            $ratePlan = RatePlan::find($item['rate_plan_id']);

            if ($ratePlan && $ratePlan->room_id === $roomId) {
                $total += $item['units'];
            }
        }

        return $total;
    }

    /**
     * Re-validates every item against current DB state — rate plan exists/active, room
     * active, dates still in the future and within the max stay, and units per room within
     * that room's shared availability (Part 3). Runs on every add, update, checkout, and
     * plain render (called from PageController::roomDetail() each time). Only ever adjusts
     * *items*, never check_in/check_out itself (that's moveToDates()'s job alone, per
     * Part 2). Returns human-readable messages describing what changed; empty means
     * nothing needed to change.
     */
    public function validate(AvailabilityService $availability): array
    {
        $cart = $this->raw();
        $messages = [];

        if (empty($cart['items'])) {
            return $messages;
        }

        if (! $cart['check_in'] || ! $cart['check_out'] || ! $this->datesStillValid($cart['check_in'], $cart['check_out'])) {
            $this->save(['check_in' => $cart['check_in'], 'check_out' => $cart['check_out'], 'guests' => $cart['guests'], 'items' => []]);

            return ['Your selected dates are no longer valid, so your room selection was cleared. Please search again.'];
        }

        $checkInDate = Carbon::createFromFormat('Y-m-d', $cart['check_in'])->startOfDay();
        $checkOutDate = Carbon::createFromFormat('Y-m-d', $cart['check_out'])->startOfDay();

        $byRoom = [];

        foreach ($cart['items'] as $item) {
            $ratePlan = RatePlan::with('room')->find($item['rate_plan_id']);

            if (! $ratePlan || ! $ratePlan->is_active || ! $ratePlan->room || ! $ratePlan->room->is_active) {
                $messages[] = 'A room or rate plan in your selection is no longer available and was removed.';

                continue;
            }

            if ($item['units'] < 1) {
                $messages[] = "\"{$ratePlan->name}\" was removed from your selection.";

                continue;
            }

            $byRoom[$ratePlan->room_id]['room'] ??= $ratePlan->room;
            $byRoom[$ratePlan->room_id]['entries'][] = ['rate_plan' => $ratePlan, 'units' => $item['units']];
        }

        $validItems = [];

        foreach ($byRoom as $group) {
            $room = $group['room'];
            $available = $availability->availableUnits($room, $checkInDate, $checkOutDate);
            $remaining = $available;

            foreach ($group['entries'] as $entry) {
                $ratePlan = $entry['rate_plan'];
                $take = max(0, min($entry['units'], $remaining));
                $remaining -= $take;

                if ($take === 0) {
                    $messages[] = "\"{$ratePlan->name}\" is no longer available for these dates and was removed.";

                    continue;
                }

                if ($take < $entry['units']) {
                    $messages[] = "\"{$ratePlan->name}\" was reduced to {$take} unit(s) due to availability.";
                }

                $validItems[] = ['rate_plan_id' => $ratePlan->id, 'units' => $take];
            }
        }

        $cart['items'] = $validItems;
        $this->save($cart);

        return $messages;
    }

    /**
     * Hydrated rows for the Booking Summary — rate plan + room models, nights, subtotal.
     * Assumes validate() already ran this request, so every rate_plan_id here still
     * resolves; defensively skips any that don't rather than throwing.
     */
    public function hydratedItems(): array
    {
        $cart = $this->raw();

        if (empty($cart['items']) || ! $cart['check_in'] || ! $cart['check_out']) {
            return [];
        }

        $nights = Carbon::createFromFormat('Y-m-d', $cart['check_in'])
            ->diffInDays(Carbon::createFromFormat('Y-m-d', $cart['check_out']));

        return collect($cart['items'])
            ->map(function ($item) use ($nights, $cart) {
                $ratePlan = RatePlan::with('room')->find($item['rate_plan_id']);

                if (! $ratePlan || ! $ratePlan->room) {
                    return null;
                }

                return [
                    'rate_plan' => $ratePlan,
                    'room' => $ratePlan->room,
                    'units' => $item['units'],
                    'check_in' => $cart['check_in'],
                    'check_out' => $cart['check_out'],
                    'nights' => $nights,
                    'subtotal' => $ratePlan->price_per_night * $nights * $item['units'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function grandTotal(): int
    {
        return array_sum(array_column($this->hydratedItems(), 'subtotal'));
    }

    /**
     * Total guest capacity the current cart covers — sum over items of
     * (units × rate plan max_guests) — used by the Make This Booking capacity check
     * (Part 6). Reads the same hydrated data as the summary, so it's already
     * post-validation.
     */
    public function totalCapacity(): int
    {
        return array_sum(array_map(
            fn ($row) => $row['units'] * $row['rate_plan']->max_guests,
            $this->hydratedItems()
        ));
    }

    private function datesStillValid(string $checkIn, string $checkOut): bool
    {
        return app(StayDateValidator::class)->isValid($checkIn, $checkOut);
    }

    private function raw(): array
    {
        return Session::get(self::SESSION_KEY, [
            'check_in' => null,
            'check_out' => null,
            'guests' => null,
            'items' => [],
        ]);
    }

    private function save(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
