<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Slug is generated once on creation and never changes afterwards, so public URLs built
 * from it stay stable even when the record's name is later edited. A slug explicitly set
 * before the model is saved (seeders, factories, tests) is left as-is.
 */
trait HasAutoSlug
{
    protected static function bootHasAutoSlug(): void
    {
        static::creating(function (Model $model) {
            if (filled($model->slug)) {
                return;
            }

            $model->slug = static::generateUniqueSlug($model->name);
        });

        static::updating(function (Model $model) {
            if (blank($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });
    }

    protected static function generateUniqueSlug(?string $name): string
    {
        $base = Str::slug($name) ?: Str::slug(class_basename(static::class));
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
