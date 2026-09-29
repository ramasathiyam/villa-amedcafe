<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningItem;
use App\Models\DiningVenue;
use App\Support\CmsImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the dining_items relation (DiningVenue hasMany DiningItem) — menu/cocktail
 * items (currently only Barak's "Signature Cocktails" use this). Nested-managed from the
 * venue's Edit page, same UI pattern as Room Images.
 */
class DiningItemController extends Controller
{
    public function store(Request $request, DiningVenue $dining): RedirectResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store("dining/{$dining->id}/items", 'public');
        }

        $data['dining_venue_id'] = $dining->id;
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? ((int) $dining->items()->max('sort_order') + 1);

        DiningItem::create($data);

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Dining item added.');
    }

    public function update(Request $request, DiningVenue $dining, DiningItem $item): RedirectResponse
    {
        abort_unless($item->dining_venue_id === $dining->id, 404);

        $data = $this->validateItem($request);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store("dining/{$dining->id}/items", 'public');
            CmsImage::deleteIfManaged($item->image);
            $data['image'] = $newPath;
        }

        $data['is_active'] = $request->boolean('is_active');

        $item->update($data);

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Dining item updated.');
    }

    public function destroy(DiningVenue $dining, DiningItem $item): RedirectResponse
    {
        abort_unless($item->dining_venue_id === $dining->id, 404);

        CmsImage::deleteIfManaged($item->image);
        $item->delete();

        return redirect()
            ->route('admin.dining.edit', $dining)
            ->with('status', 'Dining item deleted.');
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }
}
