@extends('layouts.main')

@section('title', 'Szczegóły meczu')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold mb-4">{{ $match->opponent }}</h1>
        <p><strong>Data:</strong> {{ $match->match_date->format('Y-m-d H:i') }}</p>
        <p><strong>Miejsce:</strong> {{ $match->location }}</p>
        <p><strong>Wynik:</strong> {{ $match->result ?? 'Do rozegrania' }}</p>
    </div>
@endsection
