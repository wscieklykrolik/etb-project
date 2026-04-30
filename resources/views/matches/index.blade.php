@extends('layouts.main')

@section('title', 'Mecze')

@section('content')
    <div class="max-w-5xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold mb-6">Terminarz i wyniki</h1>

        <ul class="space-y-3">
            @forelse($matches as $match)
                <li class="p-4 border rounded bg-white">
                    <a class="font-semibold hover:underline" href="{{ route('matches.show', $match) }}">
                        {{ $match->opponent }}
                    </a>
                    <p class="text-sm text-gray-600">{{ $match->match_date->format('Y-m-d H:i') }}</p>
                </li>
            @empty
                <li>Brak zaplanowanych meczów.</li>
            @endforelse
        </ul>
    </div>
@endsection
