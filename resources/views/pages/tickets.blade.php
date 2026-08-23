@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-12">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-lg bg-white p-3">
            <x-site-logo image-class="max-h-full max-w-full object-contain" fallback-class="text-3xl font-black text-black" />
        </div>
        <div>
            <h1 class="text-3xl font-bold text-yellow-400">Bilety</h1>
            <p class="mt-3 text-zinc-300">Sekcja gotowa do dodawania treści, tekstu, zdjęć i materiałów wideo.</p>
        </div>
    </div>

    <div class="mt-8 min-h-[220px] rounded-lg border border-zinc-700 bg-zinc-900 p-6">
        <h2 class="mb-2 font-semibold">Panel treści</h2>
        <p class="text-sm text-zinc-400">Tutaj można osadzać artykuły, galerie, listy zawodników i inne moduły.</p>
    </div>
</section>
@endsection
