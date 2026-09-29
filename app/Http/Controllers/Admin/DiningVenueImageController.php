<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningVenue;
use App\Models\DiningVenueImage;
use App\Support\CmsImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the dining_venue_images relation (DiningVenue hasMany DiningVenueImage) —
 * gallery photos grouped by `group` (gallery_1, gallery_2, feature, menu_highlight).
 * Mirrors RoomImageController's pattern; kept out of DiningVenueController to avoid
 * bloating the main venue CRUD controller with a second, differently-shaped resource.
 */
class DiningVenueImageController extends Controller
{
    public function store(Request $request, DiningVenue $dining): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'group' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $nextSortOrder = (int) $dining->images()
            ->where('group', $validated['group'])
            ->max('sort_order') + 1;

        foreach ($validated['images'] as $file) {
            $path = $file->store("dining/{$dining->id}", 'public');

            DiningVenueImage::create([
                'dining_venue_id' => $dining->id,
                'image' => $path,
                'alt_text' => $validated['alt_text'] ?? null,
                'group' => $validated['group'],
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'sort_order' => $nextSortOrder++,
            ]);
        }

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Gallery image(s) uploaded.');
    }

    /**
     * Two rows use this same endpoint with different shapes:
     *  - Menu Highlights (group=menu_highlight): title/description/sort_order/group all
     *    sent, plus optionally a replacement photo.
     *  - Plain gallery rows (gallery_1, gallery_2, feature, ...): ONLY a replacement photo
     *    is ever sent — no title/description/sort/group fields exist in that form at all.
     * Only fields actually present in the request are touched, so a plain-gallery photo
     * swap never blanks out title/description/sort/group it never sent. A new photo
     * always replaces the existing row's `image` column — it never creates a new row.
     */
    public function update(Request $request, DiningVenue $dining, DiningVenueImage $image): RedirectResponse
    {
        abort_unless($image->dining_venue_id === $dining->id, 404);

        $validated = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = collect($validated)
            ->except('image')
            ->filter(fn ($value, $key) => $request->has($key))
            ->all();

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store("dining/{$dining->id}", 'public');
            CmsImage::deleteIfManaged($image->image);
            $data['image'] = $newPath;
        }

        $image->update($data);

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Gallery image updated.');
    }

    public function destroy(DiningVenue $dining, DiningVenueImage $image): RedirectResponse
    {
        abort_unless($image->dining_venue_id === $dining->id, 404);

        CmsImage::deleteIfManaged($image->image);
        $image->delete();

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Gallery image deleted.');
    }
}
