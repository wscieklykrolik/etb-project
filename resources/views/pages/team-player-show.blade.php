@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('team.players') }}" class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">← Wróć do zawodników</a>

    <article class="mt-8 grid overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl lg:grid-cols-[0.9fr_1.1fr]">
        <div class="bg-zinc-900">
            @if ($player->photo_path)
                <img src="{{ \App\Support\MediaStorage::url($player->photo_path) }}" alt="{{ $player->full_name }}" class="h-full min-h-[28rem] w-full object-cover object-top">
            @else
                <div class="flex min-h-[28rem] items-center justify-center text-5xl font-black text-zinc-700">ETB</div>
            @endif
        </div>

        <div class="p-8 sm:p-10">
            <p class="text-6xl font-black text-yellow-400">#{{ $player->number }}</p>
            <h1 class="mt-4 text-4xl font-black text-white">{{ $player->full_name }}</h1>
            <p class="mt-2 text-lg font-bold uppercase tracking-wide text-zinc-400">{{ $player->positionLabel() }}</p>

            <dl class="mt-8 grid grid-cols-2 gap-4">
                <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                    <dt class="text-xs uppercase tracking-widest text-zinc-500">Wzrost</dt>
                    <dd class="mt-1 text-2xl font-black text-white">{{ $player->height ? $player->height.' cm' : '-' }}</dd>
                </div>
                <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                    <dt class="text-xs uppercase tracking-widest text-zinc-500">Waga</dt>
                    <dd class="mt-1 text-2xl font-black text-white">{{ $player->weight ? $player->weight.' kg' : '-' }}</dd>
                </div>
            </dl>

            @if ($player->description)
                <div class="prose prose-invert mt-8 max-w-none text-zinc-300">
                    {!! nl2br(e($player->description)) !!}
                </div>
            @endif
        </div>
    </article>
</section>
@endsection

