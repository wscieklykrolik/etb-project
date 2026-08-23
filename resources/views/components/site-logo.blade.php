@props([
    'imageClass' => 'h-full w-full object-contain',
    'fallbackClass' => 'font-black',
    'alt' => 'Logo ETB',
    'fallback' => 'ETB',
])

@if (! empty($siteLogoUrl))
    <img src="{{ $siteLogoUrl }}" alt="{{ $alt }}" class="{{ $imageClass }}">
@else
    <span class="{{ $fallbackClass }}">{{ $fallback }}</span>
@endif
