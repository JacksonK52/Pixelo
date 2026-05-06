<?php

use pixelo\Services\AvatarGenerator;

if(!function_exists('avatar')) {
    /**
     * Generate an avatar and return a data URI.
     * 
     * @param string    $name The name (or any string) to derive initials from
     * @param array     $options Optional: size, shape, bg, fg, length
     * @return string   data:iamge/png:base64,...
    */
    function avatar(string $name, array $options = []): string
    {
        /** @var AvatarGenerator $generator */
        $generator = app(AvatarGenerator::class)->make($name);

        if(isset($options['size'])) $generator = $generator->size((int)$options['size']);
        if(isset($options['shape'])) $generator = $generator->shape($options['shape']);
        if(isset($options['bg'])) $generator = $generator->background($options['bg']);
        if(isset($options['fg'])) $generator = $generator->color($options['fg']);
        if(isset($options['length'])) $generator = $generator->length((int)$options['length']);
        if(isset($options['bold'])) $generator = $generator->bold((bool)$options['bold']);

        return $generator->toBase64();
    }
}

if (!function_exists('avatar_url')) {
    /**
     * Return the URL to the built-in avatar route.
     *
     * @param  string  $name
     * @param  array   $options
     * @return string
     */
    function avatar_url(string $name, array $options = []): string
    {
        return route('avatar.generate', array_merge(['name' => $name], $options));
    }
}