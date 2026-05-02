<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-white" x-data>
@include('partials.navbar')
<main id="app-main">@yield('content')</main>
@include('partials.footer')
@php
    $flashType = collect(['success', 'error', 'warning', 'info'])->first(fn ($type) => session()->has($type));
    $flashMessage = $flashType ? session($flashType) : null;
    if (! $flashMessage && session('status')) { $flashType = 'success'; $flashMessage = session('status'); }
@endphp
@if ($flashMessage)
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity.duration.300ms class="fixed top-4 right-4 z-[60]">
    <div @class([
        'flex items-start gap-3 rounded-lg px-4 py-3 text-white shadow-lg',
        'bg-green-600' => $flashType === 'success',
        'bg-red-600' => $flashType === 'error',
        'bg-yellow-500' => $flashType === 'warning',
        'bg-blue-600' => $flashType === 'info',
    ])>
        <p class="text-sm">{{ $flashMessage }}</p>
        <button type="button" class="text-white/90 hover:text-white" @click="show = false">✕</button>
    </div>
</div>
@endif
</body>
</html>
