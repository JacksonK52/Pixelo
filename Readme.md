# Pixelo

A zero-dependency Laravel package for generating dynamic profile placeholder images.  
Initials are extracted from any name string, colored from a curated palette, and rendered as a crisp PNG — all via PHP's built-in GD extension.

---

## Features

- 🎨 Auto-picks a distinct background color per name (consistent across requests)
- 🔤 Extracts 1–4 initials from any name or string
- 🔵 Three shapes: **circle**, **square**, **rounded**
- 📐 Configurable size (16–1024 px)
- 🌐 Built-in HTTP route (`GET /avatar?name=John+Doe`)
- 🖼️ Blade directive `@avatar`, helper `avatar()`, and Facade
- ⚡ ETag-based HTTP caching
- 🎨 Full color override support
- Zero external dependencies (GD only)

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | ^8.1    |
| Laravel     | ^10, ^11, ^12 |
| ext-gd      | *       |

---

## Installation

```bash
composer require yourvendor/laravel-avatar
```

Laravel's auto-discovery registers the service provider and `Avatar` facade automatically.

Optionally publish the config:

```bash
php artisan vendor:publish --tag=avatar-config
```

---

## Usage

### Via Facade

```php
use YourVendor\LaravelAvatar\Facades\Avatar;

// Data URI (embed directly in <img>)
$dataUri = Avatar::make('John Doe')->toBase64();

// Laravel HTTP response
return Avatar::make('Jane Smith')->size(256)->shape('rounded')->toResponse();

// Save to disk
Avatar::make('Alice')->save(storage_path('app/public/avatars/alice.png'));

// Full chain
$png = Avatar::make('Bob')
    ->size(200)
    ->shape('circle')
    ->background('#1E88E5')
    ->color('#FFFFFF')
    ->length(2)
    ->toPng();
```

### Via helper function

```php
// Returns data URI
$src = avatar('John Doe');

// With options
$src = avatar('Jane Smith', [
    'size'   => 64,
    'shape'  => 'rounded',
    'bg'     => '5E35B1',
    'fg'     => 'FFFFFF',
]);

// Route URL
$url = avatar_url('John Doe', ['size' => 128, 'shape' => 'circle']);
```

### In Blade templates

```blade
{{-- Embed as data URI --}}
<img src="{{ avatar('John Doe') }}" width="64" height="64" alt="JD">

{{-- Blade directive (outputs data URI string) --}}
<img src="@avatar('John Doe')" width="64" alt="JD">

{{-- Route URL --}}
<img src="@avatarUrl('John Doe')" width="64" alt="JD">
<img src="{{ avatar_url('John Doe', ['size' => 128]) }}" width="128" alt="JD">
```

### Via HTTP route

The package registers a route at `GET /avatar`:

```
GET /avatar?name=John+Doe
GET /avatar?name=Jane+Smith&size=256&shape=rounded
GET /avatar?name=Bob&size=64&shape=square&bg=1E88E5&fg=FFFFFF
GET /avatar?name=Alice&size=128&length=1
```

**Query parameters:**

| Parameter | Type    | Default  | Description                            |
|-----------|---------|----------|----------------------------------------|
| `name`    | string  | `?`      | Name to extract initials from          |
| `size`    | integer | `128`    | Image size in pixels (16–1024)         |
| `shape`   | string  | `circle` | `circle`, `square`, or `rounded`       |
| `bg`      | string  | auto     | Background hex color (without `#`)     |
| `fg`      | string  | auto     | Foreground/text hex color (without `#`)|
| `length`  | integer | `2`      | Number of initials to render (1–4)     |

---

## Configuration

After publishing, edit `config/avatar.php`:

```php
return [
    'size'         => 128,
    'shape'        => 'circle',   // circle | square | rounded
    'length'       => 2,
    'font_ratio'   => 0.40,       // font size as fraction of image size
    'bold'         => true,
    'route_prefix' => 'avatar',   // URI: GET /avatar

    // Optional: override the default color palette
    // Each entry: [background_hex, foreground_hex]
    'palette' => null,
];
```

---

## Advanced Examples

### In a User model / resource

```php
// app/Models/User.php
public function getAvatarAttribute(): string
{
    return avatar($this->name, ['size' => 128]);
}

// In API resource
'avatar' => avatar_url($this->name, ['size' => 128, 'shape' => 'circle']),
```

### Consistent per-user color

The package deterministically maps any name string to a palette color — the same name **always** produces the same avatar. No storage needed.

### Custom palette

```php
// config/avatar.php
'palette' => [
    ['#0F172A', '#38BDF8'],  // Dark navy / Sky blue
    ['#064E3B', '#6EE7B7'],  // Dark green / Mint
    ['#4C1D95', '#C4B5FD'],  // Deep purple / Lavender
],
```

---

## License

MIT