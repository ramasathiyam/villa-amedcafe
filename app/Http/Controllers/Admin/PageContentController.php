<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the "Site Pages" CMS (hero + intro content for /, /room, /activity, /spa) —
 * named PageContentController, not PageController, to avoid colliding with the existing
 * public App\Http\Controllers\PageController.
 *
 * Mirrors DiningVenueController's pattern: the 4 rows are fixed (home/rooms/activities/
 * spa), created only via PageSeeder, so — like dining venues — there's no create/store/
 * destroy here, only edit/update.
 */
class PageContentController extends Controller
{
    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
            'intro_heading' => ['required', 'string', 'max:255'],
            'intro_body' => ['required', 'string'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('hero_image')) {
            $newPath = $request->file('hero_image')->store('pages', 'public');
            CmsImage::deleteIfManaged($page->hero_image);
            $data['hero_image'] = $newPath;
        }

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', ucfirst($page->slug)." page updated.");
    }
}
