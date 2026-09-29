<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Navbar renders once per public page load; resolving here (a view composer,
        // not inside navbar.blade.php itself) keeps it to one settings query per
        // request rather than depending on where in the layout the component sits.
        View::composer('components.navbar', function ($view) {
            $bookingEnabled = config('booking.enabled');

            if ($bookingEnabled) {
                $view->with([
                    'bookNowUrl' => null,
                    'bookNowDisabled' => true,
                    'bookNowReason' => 'Booking flow not defined yet',
                ]);

                return;
            }

            $waNumber = Setting::phoneDigits('whatsapp');

            $view->with([
                'bookNowUrl' => blank($waNumber)
                    ? route('contact')
                    : 'https://wa.me/'.$waNumber.'?text='.rawurlencode('Hello, I would like to make a reservation.'),
                'bookNowDisabled' => false,
                'bookNowReason' => null,
            ]);
        });
    }
}
