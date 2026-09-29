<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves/deletes image paths stored on Room/RoomImage (and future CMS-managed models)
 * while keeping a hard line between two kinds of values that can live in the same
 * `image` column:
 *
 *  - Legacy/frontend-reference paths seeded from the Next.js migration, e.g.
 *    "/images/rooms/room-card.png" — these live in public/images, are NOT managed by
 *    this app's Storage disk, and must NEVER be deleted or rewritten by CMS code.
 *  - CMS-uploaded paths, e.g. "rooms/3/abcd1234.png" — stored on the `public` disk
 *    (storage/app/public/...), fully owned by the CMS, safe to delete on replace/remove.
 *
 * The distinguishing rule is simple and deterministic: anything starting with
 * "/images/" is a legacy public asset; everything else is a Storage disk path.
 */
class CmsImage
{
    public static function isManaged(?string $path): bool
    {
        return filled($path) && ! str_starts_with($path, '/images/');
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return static::isManaged($path)
            ? Storage::disk('public')->url($path)
            : asset($path);
    }

    public static function deleteIfManaged(?string $path): void
    {
        if (static::isManaged($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
