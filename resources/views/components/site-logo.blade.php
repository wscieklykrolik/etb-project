@props([
    'url' => null,
    'imageClass' => 'h-full w-full object-contain',
    'fallbackClass' => 'font-black',
    'alt' => 'Logo ETB',
    'fallback' => 'ETB',
])

@php($logoUrl = $url ?? ($siteLogoUrl ?? null))

@if (! empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="{{ $alt }}" class="{{ $imageClass }}">
@else
    <span class="{{ $fallbackClass }}">{{ $fallback }}</span>
@endif
