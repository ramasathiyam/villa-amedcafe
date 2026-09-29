<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Fixed 7-row list (see PAGE_LABELS), one per public page that has a banner — no create/
 * destroy, only index/edit/update, same "fixed set" shape as PageContentController and
 * DiningVenueController.
 */
class BannerController extends Controller
{
    /**
     * Keys match Site Pages' own `pages.slug` values where one already exists (home,
     * rooms, activities, spa, resto-amed-cafe, barak-rooftop-and-bar) — Banner is
     * structurally the same "one CMS row per page" shape as Page, so reusing its key
     * vocabulary keeps the two features consistent instead of inventing a second one.
     * "contact" is new (Site Pages doesn't cover Contact), added the same way. Order here
     * is also the Admin index's row order (navbar order).
     */
    private const PAGE_LABELS = [
        'home' => 'Home',
        'rooms' => 'Rooms',
        'activities' => 'Activity',
        'spa' => 'Spa',
        'contact' => 'Contact',
        'resto-amed-cafe' => 'Resto Amed Café',
        'barak-rooftop-and-bar' => 'Barak Rooftop & Bar',
    ];

    public function index(): View
    {
        $banners = Banner::whereIn('page', array_keys(self::PAGE_LABELS))->get()->keyBy('page');

        $rows = collect(self::PAGE_LABELS)->map(fn ($label, $page) => [
            'page' => $page,
            'label' => $label,
            'banner' => $banners->get($page),
        ])->values();

        return view('admin.banners.index', ['rows' => $rows]);
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'banner' => $banner,
            'pageLabel' => self::PAGE_LABELS[$banner->page] ?? $banner->page,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'eyebrow' => ['required', 'string', 'max:255'],
            'heading' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'link_url' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($request->hasFile('background_image')) {
            $oldPath = $banner->background_image;
            $data['background_image'] = $request->file('background_image')->store('banners', 'public');

            // Never delete a file another banner row still references (e.g. Activity was
            // seeded with the same image path as Home) — legacy /images/* paths are never
            // deleted by CmsImage anyway, but a Storage-managed path genuinely could be
            // shared, so this checks the DB rather than assuming it can't happen.
            $stillUsedElsewhere = Banner::where('id', '!=', $banner->id)
                ->where('background_image', $oldPath)
                ->exists();

            if (! $stillUsedElsewhere) {
                CmsImage::deleteIfManaged($oldPath);
            }
        }

        $data['is_active'] = $request->boolean('is_active');

        $banner->update($data);

        return redirect()
            ->route('admin.banners.edit', $banner)
            ->with('status', 'Banner updated.');
    }
}
