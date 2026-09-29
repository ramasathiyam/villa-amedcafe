<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RatePlan;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the rate_plans relation (Room hasMany RatePlan) — nested-managed from the
 * Room's Edit page, same pattern as Room Images / Dining Items.
 */
class RatePlanController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse
    {
        $data = $this->validatePlan($request, $room);

        $data['room_id'] = $room->id;
        $data['includes_breakfast'] = $request->boolean('includes_breakfast');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? ((int) $room->ratePlans()->max('sort_order') + 1);

        RatePlan::create($data);

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Rate plan added.');
    }

    public function update(Request $request, Room $room, RatePlan $ratePlan): RedirectResponse
    {
        abort_unless($ratePlan->room_id === $room->id, 404);

        $data = $this->validatePlan($request, $room);

        $data['includes_breakfast'] = $request->boolean('includes_breakfast');
        $data['is_active'] = $request->boolean('is_active');

        $ratePlan->update($data);

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Rate plan updated.');
    }

    public function destroy(Room $room, RatePlan $ratePlan): RedirectResponse
    {
        abort_unless($ratePlan->room_id === $room->id, 404);

        $ratePlan->delete();

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Rate plan deleted.');
    }

    private function validatePlan(Request $request, Room $room): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:'.$room->max_guests],
            'price_per_night' => ['required', 'integer', 'min:0'],
            'compare_at_price_per_night' => ['nullable', 'integer', 'gt:price_per_night'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
