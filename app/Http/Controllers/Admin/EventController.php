<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\CmsImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::orderBy('sort_order')
            ->orderByRaw('display_until IS NULL, display_until ASC')
            ->orderBy('id')
            ->get();

        return view('admin.events.index', ['events' => $events]);
    }

    public function create(): View
    {
        return view('admin.events.create', ['event' => new Event()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);

        $data['background_image'] = $request->file('background_image')->store('events', 'public');
        $data['is_active'] = $request->boolean('is_active');

        $event = Event::create($data);

        return redirect()
            ->route('admin.events.index')
            ->with('status', "Event \"{$event->title}\" created.");
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', ['event' => $event]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validateEvent($request, $event);

        if ($request->hasFile('background_image')) {
            $newPath = $request->file('background_image')->store('events', 'public');
            CmsImage::deleteIfManaged($event->background_image);
            $data['background_image'] = $newPath;
        }

        $data['is_active'] = $request->boolean('is_active');

        $event->update($data);

        return redirect()
            ->route('admin.events.index')
            ->with('status', "Event \"{$event->title}\" updated.");
    }

    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title;

        CmsImage::deleteIfManaged($event->background_image);
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', "Event \"{$title}\" deleted.");
    }

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'background_image' => [$event ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'link_url' => ['nullable', 'max:2048', function ($attribute, $value, $fail) {
                if (blank($value) || str_starts_with($value, '/')) {
                    return;
                }

                if (! filter_var($value, FILTER_VALIDATE_URL) || ! str_starts_with($value, 'http')) {
                    $fail('The link must be a full http(s) URL or an internal path starting with "/".');
                }
            }],
            'display_from' => ['nullable', 'date'],
            'display_until' => ['nullable', 'date', 'after_or_equal:display_from'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
