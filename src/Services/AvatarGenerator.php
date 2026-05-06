<?php

namespace pixelo\Services;

use Illuminate\Http\Response;
use InvalidArgumentException;

class AvatarGenerator
{
    protected array $config;

    // Current Build options
    protected string $name = '';
    protected int $size = 128;
    protected ?string $background = null;
    protected ?string $foreground = null;
    protected string $shape = 'circle';
    protected float $fontRatio = 0.40;
    protected bool $bold = true;
    protected int $length = 2;

    /**
     * A curated palette of visually-distinct, accessible background colors.
     * Each entry is [background_hex, foreground_hex].
     */
    protected array $palette = [
        ['#E53935', '#FFFFFF'], // Red
        ['#D81B60', '#FFFFFF'], // Pink
        ['#8E24AA', '#FFFFFF'], // Purple
        ['#5E35B1', '#FFFFFF'], // Deep Purple
        ['#3949AB', '#FFFFFF'], // Indigo
        ['#1E88E5', '#FFFFFF'], // Blue
        ['#039BE5', '#FFFFFF'], // Light Blue
        ['#00ACC1', '#FFFFFF'], // Cyan
        ['#00897B', '#FFFFFF'], // Teal
        ['#43A047', '#FFFFFF'], // Green
        ['#7CB342', '#FFFFFF'], // Light Green
        ['#F4511E', '#FFFFFF'], // Deep Orange
        ['#6D4C41', '#FFFFFF'], // Brown
        ['#757575', '#FFFFFF'], // Grey
        ['#546E7A', '#FFFFFF'], // Blue Grey
        ['#F9A825', '#212121'], // Amber (dark text)
        ['#FFB300', '#212121'], // Amber 600 (dark text)
        ['#FB8C00', '#FFFFFF'], // Orange
    ];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->size   = $config['size']       ?? 128;
        $this->shape  = $config['shape']      ?? 'circle';
        $this->bold   = $config['bold']       ?? true;
        $this->length = $config['length']     ?? 2;
        $this->fontRatio = $config['font_ratio'] ?? 0.40;
    }

    public function make(string $name): static
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function size(int $size): static
    {
        $this->size = max(16, min(1024, $size));
        return $this;
    }

    public function background(string $color): static
    {
        $this->background = $this->normalizeHex($color);
        return $this;
    }

    public function color(string $color): static
    {
        $this->foreground = $this->normalizeHex($color);
        return $this;
    }

    public function shape(string $shape): static
    {
        if(!in_array($shape, ['circle', 'square', 'rounded'])) {
            throw new InvalidArgumentException("Shape must be 'circle', 'square', or 'rounded'.");
        }
        $this->shape = $shape;
        return $this;
    }

    public function fontSize(float $ratio): static
    {
        $this->fontRatio = max(0.1, min(0.9, $ratio));
        return $this;
    }

    public function bold(bool $bold = true): static
    {
        $this->bold = $bold;
        return $this;
    }

    public function length(int $length): static
    {
        $this->length = max(1, min(4, $length));
        return $this;
    }

    /**
     * toPng
     * =====================================
     * Return raw PNG binary
     */
    public function toPng(): string
    {
        return $this->renderPng();
    }

    /**
     * toBase64
     * =====================================
     * Return data URI suitable for <img src="...">
     */
    public function toBase64(): string
    {
        return 'data:image/png;base64,'.base64_encode($this->renderPng());
    }

    /**
     * toResponse
     * =====================================
     * Return a Laravel HTTP response with proper headers
     */
    public function toRespose(): Response
    {
        $etag = md5($this->cacheKey());

        return response($this->renderPng(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
            'ETag' => '"'.$etag.'"',
        ]);
    }

    /**
     * Save
     * =====================================
     * Save the PNG to disk and return the path
     */
    public function save(string $path): string
    {
        file_put_contents($path, $this->renderPng());
        return $path;
    }

    /**
     * URL
     * =====================================
     * Build a signed URL to the build-in avatar route.
     */
    public function url(string $name, array $options = []): string
    {
        $params = array_merge(['name' => $name], $options);
        return route('pixelo.avatar', $params);
    }

    /**
     * renderPng
     * =====================================
     * Core Rendering
     */
    protected function renderPng(): string
    {
        $initials = $this->extractInitials($this->name);
        [$bg, $fg] = $this->resolveColors($this->name);

        $size = $this->size;

        // Careate a square true-color canvas
        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);

        // Transparent fill first (needed for circle mask)
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        [$bgR, $bgG, $bgB] = $this->hextoRgb($bg);
        [$fgR, $fgG, $fgB] = $this->hextoRgb($fg);

        $bgColor = imagecolorallocate($canvas, $bgR, $bgG, $bgB);
        $fgColor = imagecolorallocate($canvas, $fgR, $fgG, $fgB);

        // Draw shape background
        match ($this->shape) {
            'circle' => $this->drawCircle($canvas, $bgColor, $size),
            'rounded' => $this->drawRounded($canvas, $bgColor, $size),
            default => imagefilledrectangle($canvas, 0, 0, $size - 1, $size - 1, $bgColor),
        };

        // Draw initials text
        $this->drawInitials($canvas, $initials, $fgColor, $size);

        // Capture output
        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }

    protected function drawCircle($canvas, $color, int $size): void
    {
        imagefilledellipse($canvas, (int)($size / 2), (int)($size / 2), $size, $size, $color);
    }

    protected function drawRounded($canvas, $color, int $size): void
    {
        $radius = (int)($size * 0.2);
        $r2 = $radius * 2;

        imagefilledrectangle($canvas, $radius, 0, $size - $radius, $size, $color);
        imagefilledrectangle($canvas, 0, $radius, $size, $size - $radius, $color);
        imagefilledellipse($canvas, $radius, $radius, $r2, $r2, $color);
        imagefilledellipse($canvas, $size - $radius, $radius, $r2, $r2, $color);
        imagefilledellipse($canvas, $radius, $size - $radius, $r2, $r2, $color);
        imagefilledellipse($canvas, $size - $radius, $size - $radius, $r2, $r2, $color);
    }

    protected function drawInitials($canvas, string $initials, $fgColor, int $size): void
    {
        $fontSize = (int)($size * $this->fontRatio);
 
        // Try to use a bundled TTF font; fall back to GD built-in fonts
        $fontPath = $this->findFont();
 
        if ($fontPath) {
            $this->drawWithTtf($canvas, $initials, $fgColor, $size, $fontSize, $fontPath);
        } else {
            $this->drawWithGdFont($canvas, $initials, $fgColor, $size);
        }
    }

    protected function drawWithTtf($canvas, string $text, $fgColor, int $size, int $fontSize, string $fontPath): void
    {
        // Measure bounding box
        $box = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textW = abs($box[4] - $box[0]);
        $textH = abs($box[5] - $box[1]);
 
        $x = (int)(($size - $textW) / 2);
        $y = (int)(($size + $textH) / 2);
 
        imagettftext($canvas, $fontSize, 0, $x, $y, $fgColor, $fontPath, $text);
    }
 
    protected function drawWithGdFont($canvas, string $text, $fgColor, int $size): void
    {
        // GD built-in font 5 (largest)
        $gdFont  = 5;
        $charW   = imagefontwidth($gdFont);
        $charH   = imagefontheight($gdFont);
        $textW   = $charW * mb_strlen($text);
        $x       = (int)(($size - $textW) / 2);
        $y       = (int)(($size - $charH) / 2);
 
        imagestring($canvas, $gdFont, $x, $y, $text, $fgColor);
    }


    protected function extractInitials(string $name): string
    {
        $name  = trim($name);
        $words = preg_split('/[\s\-_]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
 
        if (empty($words)) {
            return '?';
        }
 
        if (count($words) === 1) {
            // Single word: take first N chars
            return mb_strtoupper(mb_substr($words[0], 0, $this->length));
        }
 
        // Multiple words: first letter of first $length words
        $initials = '';
        foreach (array_slice($words, 0, $this->length) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
 
        return $initials;
    }
 
    protected function resolveColors(string $name): array
    {
        if ($this->background && $this->foreground) {
            return [$this->background, $this->foreground];
        }
 
        $index = $this->nameToIndex($name);
        $pair  = $this->palette[$index % count($this->palette)];
 
        return [
            $this->background ?? $pair[0],
            $this->foreground ?? $pair[1],
        ];
    }
 
    protected function nameToIndex(string $name): int
    {
        $hash = 0;
        foreach (mb_str_split($name) as $char) {
            $hash = (($hash << 5) - $hash) + mb_ord($char);
            $hash &= 0x7FFFFFFF;
        }
        return abs($hash);
    }
 
    protected function findFont(): ?string
    {
        // Look for a bundled font in the package
        $paths = [
            __DIR__ . '/../Fonts/Roboto-Bold.ttf',
            __DIR__ . '/../Fonts/Roboto-Regular.ttf',
            // Common system fonts as fallback
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            'C:\\Windows\\Fonts\\Arial.ttf',
        ];
 
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
 
        return null;
    }
 
    protected function normalizeHex(string $color): string
    {
        $color = ltrim($color, '#');
        if (strlen($color) === 3) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $color)) {
            throw new InvalidArgumentException("Invalid hex color: #{$color}");
        }
        return '#' . strtoupper($color);
    }
 
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
 
    protected function cacheKey(): string
    {
        return implode('|', [
            $this->name,
            $this->size,
            $this->background ?? '',
            $this->foreground ?? '',
            $this->shape,
            $this->fontRatio,
            $this->bold ? '1' : '0',
            $this->length,
        ]);
    }
}