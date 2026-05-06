# Pixelo — Laravel Avatar Generator

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-red)](https://laravel.com)

Generate dynamic, colorful profile placeholder images from any name string.  
No external services, no heavy dependencies — just PHP's built-in GD extension.

---

## Features

- 🎨 **Deterministic colors** — same name always maps to the same color
- 🔤 **Smart initials** — extracts 1–4 initials from any name string
- 🔵 **Three shapes** — `circle`, `square`, `rounded`
- 📐 **Any size** — 16 px to 1024 px
- 🌐 **HTTP route** — use as a plain `<img src>` URL
- 🖼️ **Blade directives** — `@pixeloAvatar`, `@pixeloUrl`
- ⚡ **ETag caching** — proper `Cache-Control` + `ETag` headers
- 🎨 **Full color control** — override background + foreground per call
- 🧩 **Custom palette** — swap the default 18-color palette in config
- 0️⃣ **Zero extra dependencies** — GD only

---

## Requirements

| | |
|---|---|
| PHP | ^8.0 |
| Laravel | ^10, ^11, ^12 |
| ext-gd | * |

---

## Installation

```bash
composer require jackson/pixelo
```

Laravel's package auto-discovery registers the service provider and `Pixelo` facade automatically.

Publish the config (optional):

```bash
php artisan vendor:publish --tag=pixelo-config
```

---

## Usage

### Facade

```php
use pixelo\Facades\Pixelo;

// Embed in <img src>
$dataUri = Pixelo::make('Jackson Konjengbam')->toBase64();

// Laravel HTTP response (for controller actions)
return Pixelo::make('Jane Smith')->size(256)->shape('rounded')->toResponse();

// Save PNG to disk
Pixelo::make('Alice')->save(storage_path('app/public/avatars/alice.png'));

// Route URL via facade
$url = Pixelo::make('Bob')->url('Bob', ['size' => 64, 'shape' => 'circle']);

// Full chain — all options
$png = Pixelo::make('Bob')
    ->size(200)
    ->shape('circle')
    ->background('#1E88E5')
    ->color('#FFFFFF')
    ->fontSize(0.45)    // font size as fraction of image size
    ->bold(true)
    ->length(2)
    ->toPng();
```

### Helper functions

```php
// Returns a data URI
$src = pixelo('Jackson Konjengbam');

// With options
$src = pixelo('Jane Smith', [
    'size'   => 64,
    'shape'  => 'rounded',
    'bg'     => '5E35B1',     // hex without #
    'fg'     => 'FFFFFF',
    'length' => 2,
    'bold'   => false,
]);

// Returns a route URL
$url = pixelo_url('Jackson Konjengbam', ['size' => 128, 'shape' => 'circle']);
```

### Blade templates

```blade
{{-- Data URI (inline, no HTTP request) --}}
<img src="{{ pixelo('Jackson Konjengbam') }}" width="64" height="64" alt="JD">

{{-- Blade directive --}}
<img src="@pixeloAvatar('Jackson Konjengbam')" width="64" alt="JD">
<img src="@pixeloAvatar('Jane', ['size' => 96, 'shape' => 'rounded'])" width="96" alt="J">

{{-- Route URL --}}
<img src="@pixeloUrl('Jackson Konjengbam')" width="64" alt="JD">
<img src="{{ pixelo_url('Jane Smith', ['size' => 128]) }}" width="128">
```

### HTTP endpoint

```
GET /pixelo/avatar?name=Jackson+Konjengbam
GET /pixelo/avatar?name=Jane+Smith&size=256&shape=rounded
GET /pixelo/avatar?name=Bob&size=64&shape=square&bg=1E88E5&fg=FFFFFF
GET /pixelo/avatar?name=Alice&size=128&length=1
```

**Query parameters:**

| Parameter | Type    | Default       | Description                             |
|-----------|---------|---------------|-----------------------------------------|
| `name`    | string  | `?`           | Name to extract initials from           |
| `size`    | integer | `128`         | Image size in pixels (16–1024)          |
| `shape`   | string  | `circle`      | `circle`, `square`, or `rounded`        |
| `bg`      | string  | auto          | Background hex (without `#`)            |
| `fg`      | string  | auto          | Foreground/text hex (without `#`)       |
| `length`  | integer | `2`           | Number of initials to render (1–4)      |

---

## API Reference

All methods on `AvatarGenerator` are fluent (chainable). `make()` returns a fresh clone so the base instance is never mutated.

### Builder methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `make` | `make(string $name): static` | Set the name to generate initials from. Always call this first. |
| `size` | `size(int $size): static` | Image width/height in pixels. Clamped to 16–1024. |
| `shape` | `shape(string $shape): static` | `'circle'`, `'square'`, or `'rounded'`. Throws on invalid value. |
| `background` | `background(string $color): static` | Background hex color (with or without `#`). |
| `color` | `color(string $color): static` | Foreground/text hex color (with or without `#`). |
| `fontSize` | `fontSize(float $ratio): static` | Font size as a fraction of image size (0.1–0.9). Default: `0.40`. |
| `bold` | `bold(bool $bold = true): static` | Whether to use a bold font weight. Default: `true`. |
| `length` | `length(int $length): static` | Number of initials to render (1–4). Default: `2`. |

### Output methods

| Method | Returns | Description |
|--------|---------|-------------|
| `toPng()` | `string` | Raw PNG binary. |
| `toBase64()` | `string` | Data URI: `data:image/png;base64,…` — embed directly in `<img src>`. |
| `toResponse()` | `Illuminate\Http\Response` | Laravel HTTP response with `Content-Type: image/png` and caching headers. |
| `save(string $path)` | `string` | Write PNG to disk, returns the path. |
| `url(string $name, array $options = [])` | `string` | Build a URL to the built-in `pixelo.avatar` route. |

---

## Configuration

`config/pixelo.php` after publishing:

```php
return [
    'size'         => 128,
    'shape'        => 'circle',      // circle | square | rounded
    'length'       => 2,
    'font_ratio'   => 0.40,          // font size as fraction of image size
    'bold'         => true,
    'route_prefix' => 'pixelo/avatar',

    // Override the color palette — [background_hex, foreground_hex]
    'palette' => null,
    // Example:
    // 'palette' => [
    //     ['#0F172A', '#38BDF8'],
    //     ['#064E3B', '#6EE7B7'],
    // ],
];
```

---

## Custom font

Drop any `.ttf` file into `src/Fonts/` and name it `Roboto-Bold.ttf` (or `NotoSans-Bold.ttf`).  
Pixelo picks it up automatically. Without a custom font it falls back to GD's built-in bitmap font.

---

## Patterns

### User model accessor

```php
// app/Models/User.php
public function getAvatarAttribute(): string
{
    return pixelo($this->name, ['size' => 128]);
}
```

### API resource

```php
// app/Http/Resources/UserResource.php
'avatar' => pixelo_url($this->name, ['size' => 64, 'shape' => 'circle']),
```

### Consistent per-user avatars (no storage)

The color selection is purely deterministic — `nameToIndex()` hashes the name string to a stable palette index. The same name input always produces identical output. No database column or file storage needed.

---

## Running Tests

```bash
composer install
vendor/bin/phpunit
```

---

## License

MIT © Jackson K
