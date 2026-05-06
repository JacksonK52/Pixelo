<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Avatar Size
    |--------------------------------------------------------------------------
    | Default width and height in pixels for generated avatars.
    */
    'size' => 128,

    /*
    |--------------------------------------------------------------------------
    | Default Shape
    |--------------------------------------------------------------------------
    | Options: 'circle' | 'square' | 'rounded'
    */
    'shape' => 'circle',

    /*
    |--------------------------------------------------------------------------
    | Default Initials Length
    |--------------------------------------------------------------------------
    | Maximum number of initials characters to render (1–4).
    */
    'length' => 2,

    /*
    |--------------------------------------------------------------------------
    | Font Ratio
    |--------------------------------------------------------------------------
    | Font size as a fraction of the avatar size (0.1 – 0.9).
    */
    'font_ratio' => 0.40,

    /*
    |--------------------------------------------------------------------------
    | Bold Text
    |--------------------------------------------------------------------------
    | Whether to render initials in bold weight.
    */
    'bold' => true,

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | The URI prefix for the built-in avatar generation route.
    | Accessible at: GET /{route_prefix}?name=John+Doe
    */
    'route_prefix' => 'avatar',

    /*
    |--------------------------------------------------------------------------
    | Custom Color Palette
    |--------------------------------------------------------------------------
    | Override the built-in palette with your own [background, foreground] pairs.
    | Set to null to use the package default palette.
    |
    | Example:
    | 'palette' => [
    |     ['#0F172A', '#38BDF8'],
    |     ['#064E3B', '#6EE7B7'],
    | ],
    */
    'palette' => null,

];