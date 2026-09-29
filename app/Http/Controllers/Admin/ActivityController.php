<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(): View
    {
        $activities = Activity::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.activities.index', ['activities' => $activities]);
    }

    public function create(): View
    {
        return view('admin.activities.create', ['activity' => new Activity()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateActivity($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('activities', 'public');
        }

        $data['includes'] = $this->parseIncludes($request->input('includes'));
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['show_on_activity_page'] = $request->boolean('show_on_activity_page');
        $data['is_active'] = $request->boolean('is_active');

        $activity = Activity::create($data);

        return redirect()
            ->route('admin.activities.edit', $activity)
            ->with('status', "Activity \"{$activity->name}\" created.");
    }

    public function edit(Activity $activity): View
    {
        return view('admin.activities.edit', ['activity' => $activity]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $data = $this->validateActivity($request, $activity);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('activities', 'public');
            CmsImage::deleteIfManaged($activity->image);
            $data['image'] = $newPath;
        }

        $data['includes'] = $this->parseIncludes($request->input('includes'));
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['show_on_activity_page'] = $request->boolean('show_on_activity_page');
        $data['is_active'] = $request->boolean('is_active');

        $activity->update($data);

        return redirect()
            ->route('admin.activities.edit', $activity)
            ->with('status', "Activity \"{$activity->name}\" updated.");
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $name = $activity->name;

        CmsImage::deleteIfManaged($activity->image);
        $activity->delete();

        return redirect()
            ->route('admin.activities.index')
            ->with('status', "Activity \"{$name}\" deleted.");
    }

    private function validateActivity(Request $request, ?Activity $activity = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_label' => ['nullable', 'string', 'max:255'],
            'includes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    /**
     * Textarea (one bullet per line) → the `includes` json array column. Empty lines
     * dropped, empty input becomes null (matches the column's nullable, not `[]`).
     */
    private function parseIncludes(?string $raw): ?array
    {
        if (! filled($raw)) {
            return null;
        }

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $raw)),
            fn ($line) => $line !== ''
        ));

        return $lines ?: null;
    }
}
