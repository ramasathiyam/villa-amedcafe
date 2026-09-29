<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Booking Flow
    |--------------------------------------------------------------------------
    |
    | The client is not ready to accept bookings through the website yet.
    | Everything built through Stage 3b (search, availability, rate plans,
    | cart) stays in the codebase, fully working and tested, gated behind
    | this single switch. Default is off. Every check reads
    | config('booking.enabled') — never env() directly outside this file.
    |
    */

    'enabled' => env('BOOKING_ENABLED', false),

];
