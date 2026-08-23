@php
    $browserBrand = $browserBrandName ?? 'ETB Łódź';
    $sectionTitle = trim($__env->yieldContent('title', $browserPageTitle ?? ''));
    $browserTitle = $sectionTitle !== '' && $sectionTitle !== $browserBrand
        ? $sectionTitle.' | '.$browserBrand
        : $browserBrand;
    $staticLightThemeBrowserIconUrl = asset('images/browser/etb-logo-jasny-motyw.png');
    $staticDarkThemeBrowserIconUrl = asset('images/browser/etb-logo-ciemny-motyw.png');
    $lightThemeBrowserIconUrl = $browserLightLogoUrl ?: $staticLightThemeBrowserIconUrl;
    $darkThemeBrowserIconUrl = $browserDarkLogoUrl ?: $staticDarkThemeBrowserIconUrl;
    $fallbackBrowserIconUrl = $browserIconUrl ?: $lightThemeBrowserIconUrl;
@endphp
<title>{{ $browserTitle }}</title>
<link rel="icon" href="{{ $fallbackBrowserIconUrl }}">
<link rel="icon" href="{{ $lightThemeBrowserIconUrl }}" media="(prefers-color-scheme: light)">
<link rel="icon" href="{{ $darkThemeBrowserIconUrl }}" media="(prefers-color-scheme: dark)">
<link rel="shortcut icon" href="{{ $fallbackBrowserIconUrl }}">
<link rel="apple-touch-icon" href="{{ $fallbackBrowserIconUrl }}">