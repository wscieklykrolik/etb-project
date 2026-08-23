<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('layouts.partials.browser-head')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-black text-white">
        @php
            $authImages = [
                'images/auth/tlo_ETB0.jpeg',
                'images/auth/tlo_ETB1.jpeg',
                'images/auth/tlo_ETB2.jpeg',
                'images/auth/tlo_ETB3.jpeg',
                'images/auth/tlo_ETB4.jpeg',
                'images/auth/tlo_ETB5.jpeg',
                'images/auth/tlo_ETB6.jpeg',
                'images/auth/tlo_ETB7.jpeg',
                'images/auth/tlo_ETB8.jpeg',
                'images/auth/tlo_ETB9.jpeg',
                'images/auth/tlo_ETB10.jpeg',
            ];

            $authImage = $authImages[array_rand($authImages)];
        @endphp

        <div class="relative min-h-screen overflow-hidden">
            <img
                src="{{ asset($authImage) }}"
                alt="Zdjęcie drużyny ETB"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-black/45"></div>

            <div class="relative z-10 flex min-h-screen items-center justify-center p-6">
            <div class="w-full max-w-5xl grid lg:grid-cols-2 overflow-hidden rounded-xl border-4 border-yellow-400 shadow-2xl shadow-black/60">
                <div class="hidden lg:flex bg-zinc-900 border-r-4 border-yellow-400 p-10 items-center justify-center">
                    <div class="w-full h-full min-h-96 flex items-center justify-center px-6">
                        <x-site-logo :url="$authLogoUrl" alt="Logo panelu logowania" image-class="max-h-72 max-w-full object-contain" fallback="" />
                    </div>
                </div>

                <div class="bg-yellow-400 p-6 sm:p-10">
                    <div class="mb-6 flex items-center gap-3">
                        <a href="/" class="flex h-10 w-10 shrink-0 items-center justify-center">
                            <x-site-logo :url="$authLogoUrl" alt="Logo panelu logowania" image-class="max-h-10 max-w-10 object-contain" fallback="" />
                        </a>
                        <a href="/" class="text-black text-lg font-black tracking-wide">
                            Logowanie ETB Łódź
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
            </div>
        </div>
    </body>
</html>
