@extends('layouts.app')

@section('content')
<section class="max-w-5xl mx-auto px-6 py-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-yellow-400">Panel dodawania meczu</h1>
            <p class="text-zinc-400 mt-2">Spójny widok administracyjny dla ról admin i pracownik.</p>
        </div>
        <a href="{{ route('profile.edit') }}#akcje" class="text-sm px-4 py-2 rounded border border-zinc-600 hover:bg-zinc-800">← Wróć do konta</a>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 text-emerald-300 px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 text-red-300 px-4 py-3">
            <p class="font-semibold mb-2">Popraw błędy formularza:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.matches.store') }}" enctype="multipart/form-data" class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 md:p-8 space-y-6">
        @csrf

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="opponent" class="block text-sm text-zinc-300 mb-2">Przeciwnik</label>
                <input id="opponent" type="text" name="opponent" value="{{ old('opponent') }}" required class="w-full rounded border border-zinc-700 bg-zinc-950 px-3 py-2">
            </div>
            <div>
                <label for="match_date" class="block text-sm text-zinc-300 mb-2">Data i godzina meczu</label>
                <input id="match_date" type="datetime-local" name="match_date" value="{{ old('match_date') }}" required class="w-full rounded border border-zinc-700 bg-zinc-950 px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="location" class="block text-sm text-zinc-300 mb-2">Miejsce (nazwa skrócona)</label>
                <input id="location" type="text" name="location" value="{{ old('location') }}" required class="w-full rounded border border-zinc-700 bg-zinc-950 px-3 py-2">
            </div>
            <div>
                <label for="exact_address" class="block text-sm text-zinc-300 mb-2">Dokładny adres (widoczny w szczegółach)</label>
                <input id="exact_address" type="text" name="exact_address" value="{{ old('exact_address') }}" class="w-full rounded border border-zinc-700 bg-zinc-950 px-3 py-2">
            </div>
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_home" value="1" {{ old('is_home', '1') ? 'checked' : '' }}>
            <span>Mecz u siebie</span>
        </label>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-lg border border-zinc-700 p-4">
                <h2 class="font-semibold mb-2">Logo ETB (domyślne)</h2>
                <p class="text-xs text-zinc-400 mb-3">To logo zapisuje się globalnie i jest podpinane automatycznie do kolejnych meczów. Możesz je nadpisać w każdej chwili.</p>
                @if ($defaultHomeLogo)
                    <img src="{{ asset('storage/' . $defaultHomeLogo) }}" alt="Domyślne logo ETB" class="h-20 w-20 object-contain bg-zinc-950 border border-zinc-700 rounded mb-3">
                @else
                    <div class="h-20 w-20 flex items-center justify-center text-xs text-zinc-500 bg-zinc-950 border border-dashed border-zinc-700 rounded mb-3">Brak logo</div>
                @endif
                <input type="file" name="default_home_logo" accept="image/*" class="w-full text-sm text-zinc-300">
            </div>

            <div class="rounded-lg border border-zinc-700 p-4">
                <h2 class="font-semibold mb-2">Logo przeciwnika</h2>
                <p class="text-xs text-zinc-400 mb-3">Pole startowo puste — dodaj logo dla konkretnego meczu.</p>
                <div class="h-20 w-20 flex items-center justify-center text-xs text-zinc-500 bg-zinc-950 border border-dashed border-zinc-700 rounded mb-3">Dodaj logo</div>
                <input type="file" name="away_logo" accept="image/*" class="w-full text-sm text-zinc-300">
            </div>
        </div>

        <div>
            <label for="image" class="block text-sm text-zinc-300 mb-2">Grafika meczu (opcjonalna)</label>
            <input id="image" type="file" name="image" accept="image/*" class="w-full text-sm text-zinc-300">
        </div>

        <button class="bg-yellow-400 text-black px-5 py-2 rounded font-bold hover:bg-yellow-300 transition">
            Dodaj mecz
        </button>
    </form>
</section>
@endsection
