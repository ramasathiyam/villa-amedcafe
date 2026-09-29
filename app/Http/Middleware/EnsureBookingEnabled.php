<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Booking Feature Flag: the cart routes stay registered (nothing is deleted) but 404 while
 * config('booking.enabled') is false, so there's no way to reach cart state through the
 * back door while the public pages hide every link to it.
 */
class EnsureBookingEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('booking.enabled'), 404);

        return $next($request);
    }
}
