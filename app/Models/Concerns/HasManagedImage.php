<?php

namespace App\Models\Concerns;

trait HasManagedImage
{
    public function managedImageUrl(
        string $collection,
        string $fallback,
        ?string $conversion = null,
        ?string $generic = null
    ): string {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return $fallback;
        }

        if (! is_file($media->getPath())) {
            return $generic ?? asset('img/image-placeholder.svg');
        }

        if ($conversion && $media->hasGeneratedConversion($conversion)) {
            return $media->getUrl($conversion);
        }

        return $media->getUrl();
    }
}
