<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use App\Support\CmsImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the room_images relation (Room hasMany RoomImage) — additional/detail photos,
 * separate from the room's own single `image` column. Kept out of RoomController to avoid
 * bloating the main Room CRUD controller with a second, differently-shaped resource.
 */
class RoomImageController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $nextSortOrder = (int) $room->images()->max('sort_order') + 1;

        foreach ($validated['images'] as $file) {
            $path = $file->store("rooms/{$room->id}", 'public');

            RoomImage::create([
                'room_id' => $room->id,
                'image' => $path,
                'alt_text' => $validated['alt_text'] ?? null,
                'sort_order' => $nextSortOrder++,
            ]);
        }

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Room image(s) uploaded.');
    }

    public function update(Request $request, Room $room, RoomImage $image): RedirectResponse
    {
        abort_unless($image->room_id === $room->id, 404);

        $validated = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $image->update($validated);

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Room image updated.');
    }

    public function destroy(Room $room, RoomImage $image): RedirectResponse
    {
        abort_unless($image->room_id === $room->id, 404);

        CmsImage::deleteIfManaged($image->image);
        $image->delete();

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', 'Room image deleted.');
    }
}
