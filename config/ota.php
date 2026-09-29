<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTA logo directory
    |--------------------------------------------------------------------------
    |
    | Where PageController checks for expedia.png / booking-com.png before
    | falling back to a text tile on the room card / Family Room OTA buttons.
    | Defaults to public/images/ota. Tests override this to a temporary
    | directory so they can exercise both the logo and text-fallback cases
    | without ever touching the real files.
    */
    'logo_path' => public_path('images/ota'),

];
