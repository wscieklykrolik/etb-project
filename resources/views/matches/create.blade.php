@extends('layouts.main')

@section('title', 'Dodaj mecz')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold mb-6">Dodaj mecz</h1>

        <form method="POST" action="{{ route('matches.store') }}" class="space-y-4">
            @csrf
            <input name="opponent" type="text" placeholder="Przeciwnik" class="w-full border rounded p-2" value="{{ old('opponent') }}" required>
            <input name="match_date" type="datetime-local" class="w-full border rounded p-2" value="{{ old('match_date') }}" required>
            <input name="location" type="text" placeholder="Miejsce" class="w-full border rounded p-2" value="{{ old('location') }}" required>
            <input name="result" type="text" placeholder="Wynik (opcjonalnie)" class="w-full border rounded p-2" value="{{ old('result') }}">
            <button type="submit" class="px-4 py-2 bg-black text-white rounded">Zapisz</button>
        </form>
    </div>
@endsection
