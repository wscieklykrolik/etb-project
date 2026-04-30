@extends('layouts.main')

@section('title', 'Edytuj mecz')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold mb-6">Edytuj mecz</h1>

        <form method="POST" action="{{ route('matches.update', $match) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input name="opponent" type="text" placeholder="Przeciwnik" class="w-full border rounded p-2" value="{{ old('opponent', $match->opponent) }}" required>
            <input name="match_date" type="datetime-local" class="w-full border rounded p-2" value="{{ old('match_date', $match->match_date->format('Y-m-d\\TH:i')) }}" required>
            <input name="location" type="text" placeholder="Miejsce" class="w-full border rounded p-2" value="{{ old('location', $match->location) }}" required>
            <input name="result" type="text" placeholder="Wynik (opcjonalnie)" class="w-full border rounded p-2" value="{{ old('result', $match->result) }}">
            <button type="submit" class="px-4 py-2 bg-black text-white rounded">Aktualizuj</button>
        </form>
    </div>
@endsection
