<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningVenue;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DiningVenueController extends Controller
{
    /**
     * There is no update() here — Resto Amed Café and Barak Rooftop & Bar are fixed
     * venues with nothing left to edit at the venue level: identity (name/slug) has
     * always been backend-locked, hero/intro content moved to Site Pages (App\Models\Page,
     * same slug), `is_active` is no longer admin-controlled (both venues stay permanently
     * active — see the class-level note below), and there's no venue-level sort_order
     * concern since there's no venue listing to order. The dining_venues columns for all
     * of this still exist (unused going forward, not dropped) — see PageSeeder for the
     * one-time copy of hero/intro into Page. Item-level sort_order (gallery images,
     * Signature Cocktails) is untouched — see DiningVenueImageController/DiningItemController.
     *
     * is_active: both fixed venues must always stay active. Since there is no admin
     * control for this anymore, nothing ever writes it false — it simply keeps whatever
     * value is already in the database (true, verified for both rows).
     */
    public function edit(DiningVenue $dining): View
    {
        $dining->load(['images', 'items']);

        return view('admin.dining.edit', ['dining' => $dining]);
    }

    public function destroy(DiningVenue $dining): RedirectResponse
    {
        $dining->load(['images', 'items']);
        $name = $dining->name;

        // Delete managed files first (hero + every gallery image + every item photo)
        // while the model/relations are still loaded — the child DB rows themselves are
        // removed via cascadeOnDelete() on both dining_venue_images and dining_items once
        // the DiningVenue row is deleted below.
        CmsImage::deleteIfManaged($dining->hero_image);
        foreach ($dining->images as $image) {
            CmsImage::deleteIfManaged($image->image);
        }
        foreach ($dining->items as $item) {
            CmsImage::deleteIfManaged($item->image);
        }

        $dining->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('status', "Dining venue \"{$name}\" deleted.");
    }
}
