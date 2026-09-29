<?php

namespace App\Services;

/**
 * There is no promo/discount system yet. This is the single point a future one plugs
 * into — every caller asks isValid() rather than checking a code's shape itself, so
 * swapping this method's body for a real lookup is the only change a real system needs.
 */
class PromoCodeValidator
{
    public function isValid(string $code): bool
    {
        return false;
    }
}
