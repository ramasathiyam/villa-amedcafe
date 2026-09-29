<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningVenue;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::with('venue')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.testimonials.index', ['testimonials' => $testimonials]);
    }

    public function create(): View
    {
        return view('admin.testimonials.create', [
            'testimonial' => new Testimonial(),
            'venues' => DiningVenue::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTestimonial($request);

        $data['is_active'] = $request->boolean('is_active');

        $testimonial = Testimonial::create($data);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', "Testimonial from \"{$testimonial->name}\" created.");
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', [
            'testimonial' => $testimonial,
            'venues' => DiningVenue::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $this->validateTestimonial($request);

        $data['is_active'] = $request->boolean('is_active');

        $testimonial->update($data);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', "Testimonial from \"{$testimonial->name}\" updated.");
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $name = $testimonial->name;

        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', "Testimonial from \"{$name}\" deleted.");
    }

    private function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quote' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'dining_venue_id' => ['nullable', 'integer', 'exists:dining_venues,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
