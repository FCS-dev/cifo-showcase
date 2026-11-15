<?php

namespace App\Models;

use App\Models\Concerns\HasManagedImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

#[Fillable(['slug', 'name', 'badge', 'short_description'])]
class Category extends Model implements HasMedia
{
    use HasFactory;
    use HasManagedImage;
    use HasTranslatableSlug;
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    public const FRONT_FALLBACKS = [
        'programacion-y-desarrollo' => 'https://picsum.photos/seed/cifo-pd/800/600',
        'ciberseguridad' => 'https://picsum.photos/seed/cifo-cs/800/600',
        'tecnologias-emergentes' => 'https://picsum.photos/seed/cifo-te/800/600',
        'competencias-digitales' => 'https://picsum.photos/seed/cifo-cd/800/600',
    ];

    public array $translatable = ['name', 'slug', 'badge', 'short_description'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::createWithLocales(['es', 'ca'])
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * SubstituteBindings runs before the `locale` middleware, so getLocale() still returns
     * the app default. Search across all translatable locales to find the correct model.
     */
    public function resolveRouteBinding($value, $field = null): ?static
    {
        $slugField = $this->getSlugOptions()->slugField;

        if ($field === $slugField) {
            return $this->where(function ($q) use ($slugField, $value) {
                foreach (['es', 'ca'] as $locale) {
                    $q->orWhere("{$slugField}->{$locale}", $value);
                }
            })->first();
        }

        return parent::resolveRouteBinding($value, $field);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('card')
            ->width(800)
            ->height(600)
            ->sharpen(8)
            ->nonQueued();
    }

    public function imageUrl(string $fallback, ?string $conversion = null): string
    {
        return $this->managedImageUrl('image', $fallback, $conversion);
    }

    public function fallbackImage(): string
    {
        $slug = $this->getTranslation('slug', 'es', false);

        return self::FRONT_FALLBACKS[$slug] ?? asset('img/image-placeholder.svg');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function projects()
    {
        return $this->hasManyThrough(Project::class, Course::class);
    }
}
