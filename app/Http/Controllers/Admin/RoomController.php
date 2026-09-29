<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::withCount('images')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.rooms.index', ['rooms' => $rooms]);
    }

    public function create(): View
    {
        return view('admin.rooms.create', ['room' => new Room()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRoom($request);

        $data['image'] = $request->file('image')->store('rooms', 'public');
        $data['includes_breakfast'] = $request->boolean('includes_breakfast');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        $room = Room::create($data);

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', "Room \"{$room->name}\" created.");
    }

    public function edit(Room $room): View
    {
        $room->load(['images', 'ratePlans']);

        return view('admin.rooms.edit', ['room' => $room]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $data = $this->validateRoom($request, $room);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('rooms', 'public');
            CmsImage::deleteIfManaged($room->image);
            $data['image'] = $newPath;
        }

        $data['includes_breakfast'] = $request->boolean('includes_breakfast');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        $room->update($data);

        return redirect()
            ->route('admin.rooms.edit', $room)
            ->with('status', "Room \"{$room->name}\" updated.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->load('images');
        $name = $room->name;

        // Delete the DB row first: booking_items.room_id restricts delete, so a room with
        // booking history throws here and must be caught before any file is touched. Only
        // on success do we delete the actual managed files (main image + every detail
        // photo) — room_images' own rows are removed via its cascadeOnDelete().
        try {
            $room->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', "Room \"{$name}\" cannot be deleted because it has existing bookings.");
        }

        CmsImage::deleteIfManaged($room->image);
        foreach ($room->images as $image) {
            CmsImage::deleteIfManaged($image->image);
        }

        return redirect()
            ->route('admin.rooms.index')
            ->with('status', "Room \"{$name}\" deleted.");
    }

    private function validateRoom(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate_per_night' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'size_sqm' => ['required', 'integer', 'min:1'],
            'max_guests' => ['required', 'integer', 'min:1'],
            'bedding' => ['required', 'string', 'max:255'],
            'hotel_information' => ['nullable', 'string'],
            'total_units' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => [$room ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }
}
