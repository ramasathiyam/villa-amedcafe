<?php

namespace App\Models;

use App\Services\StayDateValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'label', 'title', 'description', 'background_image', 'link_url',
    'display_from', 'display_until', 'is_active', 'sort_order',
])]
class Event extends Model
{
    protected function casts(): array
    {
        return [
            'display_from' => 'date',
            'display_until' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The single source of "should this show on Home right now" — used by the public
     * page, the Dashboard EVENTS count, and (for the boundary cases) the Admin Status
     * column, so the rule is never duplicated. "Today" is the hotel's own calendar date
     * (Asia/Makassar), same source as the stay-date validation — app.timezone itself
     * stays UTC.
     */
    public function scopeVisibleOnHome(Builder $query): Builder
    {
        $today = self::todayForMin();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('display_from')->orWhere('display_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('display_until')->orWhere('display_until', '>=', $today))
            ->orderBy('sort_order')
            ->orderByRaw('display_until IS NULL, display_until ASC')
            ->orderBy('id');
    }

    /**
     * Admin index's Status column — Active/Scheduled/Ended/Inactive. Deliberately built
     * from the same is_active + display window facts as scopeVisibleOnHome() above
     * (rather than checking "is this row IN the visibleOnHome() result set"), since it
     * also needs to distinguish *why* a row isn't visible (not yet vs. already past vs.
     * switched off), which a single boolean can't.
     */
    public function status(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        $today = self::todayForMin();

        if ($this->display_from && $this->display_from->toDateString() > $today) {
            return 'Scheduled';
        }

        if ($this->display_until && $this->display_until->toDateString() < $today) {
            return 'Ended';
        }

        return 'Active';
    }

    private static function todayForMin(): string
    {
        return Carbon::now(StayDateValidator::HOTEL_TIMEZONE)->toDateString();
    }
}
