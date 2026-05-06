<?php

namespace pixelo;

use Illuminate\Support\Facades\Blade;
use pixelo\Services\AvatarGenerator;

/**
 * Register a @avatar Blade directive.
 *
 * Usage in Blade:
 *   @avatar('Jackson Konjengbam')
 *   @avatar('Jackson Konjengbam', ['size' => 64, 'shape' => 'rounded'])
 */
class BladeDirective
{
    public static function register(): void
    {
        Blade::directive('avatar', function (string $expression) {
            return "<?php echo app(\\pixelo\\Services\\AvatarGenerator::class)->make({$expression})->toBase64(); ?>";
        });

        // @avatarUrl('Jackson Konjengbam') - outputs a route URL instead of data URI
        Blade::directive('avatarUrl', function (string $expression) {
            return "<?php echo route('pixelo.avatar', ['name' => {$expression}]); ?>";
        });
    }
}