@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Drużyna {{ $publicTeamName }}</p>
        <h1 class="mt-2 text-4xl font-black text-white">Drużyna</h1>
        <p class="mt-4 max-w-3xl text-zinc-300">Zawodnicy, sztab szkoleniowy i drużyna 3x3 w jednym przewijanym widoku.</p>
    </div>

    <nav class="mb-12 flex flex-wrap gap-2" aria-label="Sekcje drużyny">
        @foreach ([
            [route('team.players'), 'Zawodnicy'],
            [route('team.staff'), 'Sztab szkoleniowy'],
            [route('team.3x3'), 'Drużyna 3x3'],
        ] as [$url, $label])
            <a href="{{ $url }}" class="rounded border border-zinc-700 bg-zinc-950 px-4 py-2 text-sm font-bold text-white transition hover:border-yellow-400 hover:bg-yellow-400 hover:text-black">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="space-y-20">
        @include('pages.partials.team-players-section', [
            'sectionId' => 'players',
            'headingLevel' => 2,
        ])

        @include('pages.partials.team-staff-section', [
            'sectionId' => 'staff',
            'headingLevel' => 2,
        ])

        @include('pages.partials.team-three-x-three-section', [
            'sectionId' => 'three-x-three',
            'headingLevel' => 2,
        ])
    </div>
</section>
@endsection
