@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB Basket</p>
        <h1 class="mt-2 text-4xl font-black text-white">Klub</h1>
        <p class="mt-4 max-w-3xl text-zinc-300">Wszystkie informacje klubowe w jednym miejscu. Przewiń stronę albo wybierz sekcję poniżej.</p>
    </div>

    <nav class="mb-12 flex flex-wrap gap-2" aria-label="Sekcje klubu">
        @foreach ($clubSections as $clubSection)
            <a href="{{ route('club.'.$clubSection->slug) }}" class="rounded border border-zinc-700 bg-zinc-950 px-4 py-2 text-sm font-bold text-white transition hover:border-yellow-400 hover:bg-yellow-400 hover:text-black">{{ $clubSection->title }}</a>
        @endforeach
    </nav>

    <div class="space-y-16">
        @foreach ($clubSections as $clubSection)
            @if ($clubSection->slug === 'sponsors')
                @include('pages.partials.sponsor-showcase', [
                    'sponsorCategories' => $clubSponsorCategories,
                ])
            @else
                @include('pages.partials.club-section-content', ['clubSection' => $clubSection])
            @endif
        @endforeach
    </div>
</section>
@endsection
