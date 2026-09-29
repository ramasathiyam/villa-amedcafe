<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * The single place availability is calculated. check_in is the first night, check_out is
 * the departure day (not itself a night) — a booking item occupies a room on each night
 * from check_in up to but not including check_out, so back-to-back stays never conflict.
 */
class AvailabilityService
{
    public function nights(Carbon $checkIn, Carbon $checkOut): int
    {
        return $checkIn->diffInDays($checkOut);
    }

    /**
     * total_units minus the MAXIMUM units booked on any single night in the range — not a
     * sum of overlapping items, which would double-count non-conflicting back-to-back
     * bookings (e.g. 1–3 Oct and 3–5 Oct must not reduce a 1–5 Oct search by 2). One query
     * fetches every booking_item overlapping the range; the per-night max is then computed
     * in memory (the range is capped at 30 nights by validation, so this stays cheap).
     */
    public function availableUnits(Room $room, Carbon $checkIn, Carbon $checkOut): int
    {
        $items = $room->bookingItems()
            ->where('check_in', '<', $checkOut->toDateString())
            ->where('check_out', '>', $checkIn->toDateString())
            ->whereHas('booking', function ($query) {
                $query->where('status', 'confirmed')
                    ->orWhere(function ($query) {
                        $query->where('status', 'pending')->where('expires_at', '>', now());
                    });
            })
            ->get(['check_in', 'check_out', 'units']);

        if ($items->isEmpty()) {
            return max(0, $room->total_units);
        }

        $maxBookedPerNight = 0;

        foreach (CarbonPeriod::create($checkIn, '1 day', $checkOut->copy()->subDay()) as $night) {
            $bookedThisNight = $items
                ->filter(fn ($item) => $item->check_in->lte($night) && $item->check_out->gt($night))
                ->sum('units');

            $maxBookedPerNight = max($maxBookedPerNight, $bookedThisNight);
        }

        return max(0, $room->total_units - $maxBookedPerNight);
    }
}
