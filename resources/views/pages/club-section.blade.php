@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <a href="{{ route('club') }}" class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">Wróć do klubu</a>
        <h1 class="mt-3 text-4xl font-black text-white">Klub / {{ $clubSection->title }}</h1>
    </div>

    @if ($clubSection->slug === 'sponsors')
        @include('pages.partials.sponsor-showcase', [
            'sponsorCategories' => $clubSponsorCategories,
        ])
    @else
        @include('pages.partials.club-section-content', ['clubSection' => $clubSection])
    @endif
</section>
@endsection
