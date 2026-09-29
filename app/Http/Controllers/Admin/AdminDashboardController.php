<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DiningItem;
use App\Models\DiningVenue;
use App\Models\Event;
use App\Models\Room;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index', [
            'roomCount' => Room::count(),
            'activityCount' => Activity::count(),
            'diningVenueCount' => DiningVenue::count(),
            'diningItemCount' => DiningItem::count(),
            'testimonialCount' => Testimonial::count(),
            // Same scope the public Home page uses (Event::scopeVisibleOnHome()) — not a
            // plain count(), so this matches "currently active on the website" exactly.
            'eventCount' => Event::visibleOnHome()->count(),
        ]);
    }
}
