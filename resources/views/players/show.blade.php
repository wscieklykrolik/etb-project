@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <article class="rounded border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid gap-8 md:grid-cols-[240px_1fr] md:items-center">
                    @if ($player->photo_path)
                        <img src="{{ \App\Support\MediaStorage::url($player->photo_path) }}"
                             alt="{{ $player->first_name }} {{ $player->last_name }}"
                             class="h-80 w-full rounded object-cover">
                    @else
                        <div class="flex h-80 items-center justify-center rounded bg-gray-200 text-sm font-semibold text-gray-500">
                            Brak zdjęcia
                        </div>
                    @endif

                    <div>
                        <p class="text-5xl font-black text-yellow-500">#{{ $player->number }}</p>
                        <h1 class="mt-3 text-3xl font-bold">{{ $player->first_name }} {{ $player->last_name }}</h1>
                        <dl class="mt-6 grid gap-4 text-lg">
                            <div>
                                <dt class="text-sm font-semibold uppercase text-gray-500">Pozycja</dt>
                                <dd>{{ $player->positionLabel() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold uppercase text-gray-500">Wzrost</dt>
                                <dd>{{ $player->height }} cm</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold uppercase text-gray-500">Waga</dt>
                                <dd>{{ $player->weight }} kg</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </article>
        </div>
    </div>
@endsection

