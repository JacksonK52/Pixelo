<?php

namespace pixelo\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static pixelo\Services\AvatarGenerator make(string $name)
 * @method static pixelo\Services\AvatarGenerator size(int $size)
 * @method static pixelo\Services\AvatarGenerator background(string $color)
 * @method static pixelo\Services\AvatarGenerator color(string $color)
 * @method static pixelo\Services\AvatarGenerator shape(string $shape)
 * @method static pixelo\Services\AvatarGenerator fontSize(float $ratio)
 * @method static pixelo\Services\AvatarGenerator bold(bool $bold)
 * @method static pixelo\Services\AvatarGenerator toBase64()
 * @method static pixelo\Services\AvatarGenerator toResponse()
 * @method static pixelo\Services\AvatarGenerator save(string $path)
 * @method static string url(string $name, array $options = [])
 *
 * @see pixelo\Services\AvatarGenerator
 */
class Pixelo extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'avatar';
    }
}