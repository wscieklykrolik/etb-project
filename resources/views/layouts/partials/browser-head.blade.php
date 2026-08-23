@php
    $browserBrand = $browserBrandName ?? 'ETB Łódź';
    $sectionTitle = trim($__env->yieldContent('title', $browserPageTitle ?? ''));
    $browserTitle = $sectionTitle !== '' && $sectionTitle !== $browserBrand
        ? $sectionTitle.' | '.$browserBrand
        : $browserBrand;
@endphp
<title>{{ $browserTitle }}</title>
@if (! empty($browserIconUrl))
    <link rel="icon" href="{{ $browserIconUrl }}">
    <link rel="shortcut icon" href="{{ $browserIconUrl }}">
    <link rel="apple-touch-icon" href="{{ $browserIconUrl }}">
@endif