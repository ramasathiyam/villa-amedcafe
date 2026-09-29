<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

/**
 * The single place check_in/check_out rules live — Room Detail's search bar, the /room
 * listing's search bar, and BookingCart::validate() all call this instead of restating
 * the rules. "Today" is the hotel's own calendar date (Asia/Makassar), not the server's;
 * app.timezone stays UTC for timestamps generally, this is the one place a specific local
 * "today" matters.
 */
class StayDateValidator
{
    public const HOTEL_TIMEZONE = 'Asia/Makassar';
    public const MAX_STAY_NIGHTS = 30;

    public function validate(?string $checkIn, ?string $checkOut): ValidatorContract
    {
        $validator = Validator::make(
            ['check_in' => $checkIn, 'check_out' => $checkOut],
            [
                'check_in' => ['required', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date_format:Y-m-d'],
            ]
        );

        $validator->after(function ($validator) use ($checkIn, $checkOut) {
            if ($validator->errors()->has('check_in') || $validator->errors()->has('check_out')) {
                return;
            }

            [$checkInDate, $checkOutDate] = $this->parse($checkIn, $checkOut);
            $today = $this->today();

            if ($checkInDate->lt($today)) {
                $validator->errors()->add('check_in', 'Check-in date cannot be in the past.');
            }

            if ($checkOutDate->lte($checkInDate)) {
                $validator->errors()->add('check_out', 'Check-out date must be after check-in date.');
            } elseif ($checkInDate->diffInDays($checkOutDate) > self::MAX_STAY_NIGHTS) {
                $validator->errors()->add('check_out', 'Maximum stay is '.self::MAX_STAY_NIGHTS.' nights.');
            }
        });

        return $validator;
    }

    public function isValid(string $checkIn, string $checkOut): bool
    {
        return ! $this->validate($checkIn, $checkOut)->fails();
    }

    /**
     * Plain calendar dates (no timezone-instant conversion) so they compare consistently
     * with booking_items' DATE columns — see AvailabilityService's doc comment. Only ever
     * called after validate()/isValid() has confirmed both strings parse and are in order.
     */
    public function parse(string $checkIn, string $checkOut): array
    {
        return [
            Carbon::createFromFormat('Y-m-d', $checkIn)->startOfDay(),
            Carbon::createFromFormat('Y-m-d', $checkOut)->startOfDay(),
        ];
    }

    public function todayForMin(): string
    {
        return Carbon::now(self::HOTEL_TIMEZONE)->toDateString();
    }

    private function today(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->todayForMin())->startOfDay();
    }
}
