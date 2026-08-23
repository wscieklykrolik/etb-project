@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB Basket</p>
        <h1 class="mt-2 text-4xl font-black text-white">Rozgrywki</h1>
        <p class="mt-4 max-w-3xl text-zinc-300">Terminarze, tabele i sekcje 3x3 są teraz na jednej stronie. Przewiń całość albo wybierz konkretny blok.</p>
    </div>

    <nav class="mb-12 flex flex-wrap gap-2" aria-label="Sekcje rozgrywek">
        @foreach ([
            [route('schedule'), 'Terminarz'],
            [route('schedule.third-league'), 'III liga ŁZKosz'],
            [route('schedule.lzkosz'), 'Terminarz ŁZKosz'],
            [route('schedule.table'), 'Tabela'],
            [route('schedule.3x3'), 'Terminarz 3x3'],
            [route('schedule.3x3.tournaments'), 'Turnieje 3x3'],
        ] as [$url, $label])
            <a href="{{ $url }}" class="rounded border border-zinc-700 bg-zinc-950 px-4 py-2 text-sm font-bold text-white transition hover:border-yellow-400 hover:bg-yellow-400 hover:text-black">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="space-y-20">
        @include('pages.partials.schedule-list-section', [
            'sectionId' => 'matches',
            'headingLevel' => 2,
        ])

        @include('pages.partials.static-content-section', [
            'sectionId' => 'third-league',
            'eyebrow' => 'Rozgrywki',
            'title' => 'III liga mężczyzn ŁZKosz',
            'description' => 'Sekcja z odnośnikiem do rozgrywek ligowych ŁZKosz.',
            'panelText' => 'Po uzupełnieniu treści można tu dodać opis ligi, najważniejsze komunikaty i materiały dla kibiców.',
            'actionUrl' => 'https://www.lzkosz.pl/liga/215.html',
            'actionLabel' => 'Otwórz stronę ŁZKosz',
        ])

        @include('pages.partials.schedule-lzkosz-section', [
            'sectionId' => 'lzkosz',
            'headingLevel' => 2,
        ])

        @include('pages.partials.league-table-section', [
            'sectionId' => 'table',
            'headingLevel' => 2,
        ])

        @include('pages.partials.tournament-list-section', [
            'sectionId' => 'three-x-three-schedule',
            'headingLevel' => 2,
            'upcomingTournaments' => $participatingUpcomingTournaments,
            'finishedTournaments' => $participatingFinishedTournaments,
            'pageTitle' => 'Terminarz turniejów 3x3',
            'pageEyebrow' => 'Turnieje, w których gramy',
            'emptyMessage' => 'Brak turniejów w tej sekcji.',
        ])

        @include('pages.partials.tournament-list-section', [
            'sectionId' => 'three-x-three-tournaments',
            'headingLevel' => 2,
            'upcomingTournaments' => $organizedUpcomingTournaments,
            'finishedTournaments' => $organizedFinishedTournaments,
            'pageTitle' => 'Turnieje 3x3',
            'pageEyebrow' => 'Organizowane przez ETB',
            'emptyMessage' => 'Brak turniejów organizowanych przez ETB w tej sekcji.',
        ])
    </div>
</section>
@endsection
