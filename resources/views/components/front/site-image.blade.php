@props([
    'name',
    'alt' => '',
    'width' => null,
    'height' => null,
])

<img src="{{ \App\Models\SiteImage::url($name) }}" alt="{{ $alt }}"
    @if ($width !== null) width="{{ $width }}" @endif
    @if ($height !== null) height="{{ $height }}" @endif
    {{ $attributes }}>
