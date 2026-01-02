<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GMC Feed Debug Key
    |--------------------------------------------------------------------------
    |
    | If set, you can request debug output from the feed URL with:
    | /gmc/products.xml?debug=1&key=YOUR_KEY
    |
    | Keep this secret. Leave empty to disable debug mode entirely.
    |
    */
    'debug_key' => env('GMC_DEBUG_KEY', ''),
];

