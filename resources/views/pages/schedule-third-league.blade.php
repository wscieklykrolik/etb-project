@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @include('pages.partials.static-content-section', [
        'sectionId' => 'third-league',
        'eyebrow' => 'Rozgrywki',
        'title' => 'III liga mężczyzn ŁZKosz',
        'description' => 'Wyniki, terminarz i szczegóły spotkań znajdziesz w oficjalnym serwisie rozgrywek.',
        'panelTitle' => 'III liga w oficjalnym serwisie ŁZKosz',
        'panelText' => 'Przejdź do aktualnych informacji o rozgrywkach i sprawdź, co czeka naszą drużynę.',
        'actionUrl' => 'https://www.lzkosz.pl/liga/215.html',
        'actionLabel' => 'Otwórz III ligę w ŁZKosz',
        'externalAction' => true,
    ])
</section>
@endsection
