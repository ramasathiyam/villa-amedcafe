<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Plain key/value store — no grouping, no typing, no cache layer. Deliberately minimal
 * per the "no complex settings system" instruction.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Phone/WhatsApp values are stored as typed locally (leading 0, e.g. "087715021995")
     * since that's the natural format for an Indonesian admin to read and edit. Dialable
     * tel:/wa.me links need the country code instead — this mirrors the substitution the
     * hardcoded links already performed by hand before these values were database-backed.
     */
    public static function phoneDigits(string $key): string
    {
        $digits = preg_replace('/\D+/', '', (string) static::get($key, ''));

        return preg_replace('/^0/', '62', $digits);
    }
}
