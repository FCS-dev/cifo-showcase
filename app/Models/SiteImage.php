<?php

namespace App\Models;

use App\Models\Concerns\HasManagedImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['key'])]
class SiteImage extends Model implements HasMedia
{
    use HasManagedImage;
    use InteractsWithMedia;

    public const SLOTS = [
        'header_logo' => [
            'fallback' => 'img/logo-placeholder.svg',
            'generic' => 'img/logo-placeholder.svg',
            'conversion' => null,
        ],
        'home_about' => [
            'fallback' => 'https://picsum.photos/seed/cifo-building/800/600',
            'generic' => 'img/image-placeholder.svg',
            'conversion' => null,
        ],
        'about_mission' => [
            'fallback' => 'https://picsum.photos/seed/cifo-mission/800/600',
            'generic' => 'img/image-placeholder.svg',
            'conversion' => null,
        ],
        'about_vision' => [
            'fallback' => 'https://picsum.photos/seed/cifo-vision/800/600',
            'generic' => 'img/image-placeholder.svg',
            'conversion' => null,
        ],
    ];

    protected static ?Collection $cached = null;

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/svg+xml',
            ]);
    }

    public static function cached(): Collection
    {
        return static::$cached ??= static::query()->with('media')->get()->keyBy('key');
    }

    public static function url(string $key): string
    {
        return static::cached()->get($key)?->imageUrl() ?? static::slotFallback($key);
    }

    public static function slotFallback(string $key): string
    {
        return static::resolveUrl(self::SLOTS[$key]['fallback'] ?? 'img/image-placeholder.svg');
    }

    public static function slotGeneric(string $key): string
    {
        return static::resolveUrl(self::SLOTS[$key]['generic'] ?? 'img/image-placeholder.svg');
    }

    public function imageUrl(): string
    {
        $slot = self::SLOTS[$this->key] ?? [];

        return $this->managedImageUrl(
            'image',
            static::slotFallback($this->key),
            $slot['conversion'] ?? null,
            static::slotGeneric($this->key),
        );
    }

    private static function resolveUrl(string $path): string
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : asset($path);
    }
}
