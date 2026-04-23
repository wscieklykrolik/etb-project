<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-black text-white">
        <div class="min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-5xl grid lg:grid-cols-2 overflow-hidden rounded-xl border-4 border-yellow-400 shadow-2xl shadow-yellow-700/30">
                <div class="hidden lg:flex bg-zinc-900 border-r-4 border-yellow-400 p-10 items-center justify-center">
                    <div class="w-full h-full min-h-96 rounded-lg border-2 border-dashed border-yellow-400/80 bg-black/40 flex items-center justify-center text-center text-yellow-300 px-6">
                        <p class="text-sm uppercase tracking-[0.2em]">Miejsce na zdjęcie / grafikę organizacji</p>
                    </div>
                </div>

                <div class="bg-yellow-400 p-6 sm:p-10">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <a href="/" class="flex items-center gap-3 text-black font-bold">
                            <x-application-logo class="w-10 h-10 fill-current text-black" />
                            <span>Panel ETB</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
