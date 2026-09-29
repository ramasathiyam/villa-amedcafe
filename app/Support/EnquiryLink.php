<?php

namespace App\Support;

/**
 * Shared wa.me link builder for room enquiries — used by the room detail page's
 * booking-flag-off CTA and, identically, by the /room listing's BOOK button (room-card
 * and Family Room composite). Falls back to the Contact page (never a broken wa.me link)
 * when no WhatsApp number is set in Site Settings.
 */
class EnquiryLink
{
    public static function build(string $roomName, ?string $waNumber, string $contactFallbackUrl): string
    {
        if (blank($waNumber)) {
            return $contactFallbackUrl;
        }

        return 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Hello, I am interested in the {$roomName}.");
    }
}
