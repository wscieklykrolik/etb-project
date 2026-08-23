<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.browser-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex h-screen overflow-hidden">
        <x-admin.sidebar />

        <div class="flex flex-1 flex-col overflow-y-auto">
            <header class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950/90 backdrop-blur-sm">
                <div class="flex h-14 items-center justify-between px-6">
                    <div class="flex items-center gap-3 text-sm text-zinc-400">
                        <a href="{{ route('home') }}" class="hover:text-yellow-400 transition-colors">Strona</a>
                        <span class="text-zinc-600">/</span>
                        <span class="text-zinc-200">@yield('title', 'Panel administracyjny')</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm text-zinc-400 hover:text-yellow-400 transition-colors">
                            <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-zinc-800 text-xs font-semibold text-zinc-300">{{ Str::upper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6">
                @php
                    $flashType = collect(['success', 'error', 'warning', 'info'])->first(fn ($t) => session()->has($t));
                    $flashMessage = $flashType ? session($flashType) : null;
                    if (!$flashMessage && session('status')) { $flashType = 'success'; $flashMessage = session('status'); }
                @endphp
                @if ($flashMessage)
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity.duration.300ms class="mb-6">
                        <div @class([
                            'flex items-start gap-3 rounded-lg px-4 py-3 text-white shadow-lg',
                            'bg-green-600' => $flashType === 'success',
                            'bg-red-600' => $flashType === 'error',
                            'bg-yellow-500' => $flashType === 'warning',
                            'bg-blue-600' => $flashType === 'info',
                        ])>
                            <p class="text-sm">{{ $flashMessage }}</p>
                            <button type="button" class="ml-auto text-white/90 hover:text-white" @click="show = false">✕</button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
