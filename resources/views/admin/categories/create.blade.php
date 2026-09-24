@extends('layouts.admin')
@section('title', 'Dodaj kategorię')

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Powrót do kategorii
    </a>
    <h1 class="mt-2 text-2xl font-bold text-slate-950">Dodaj kategorię</h1>
</div>

<div class="max-w-lg rounded-lg border border-slate-200 bg-white p-6">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nazwa kategorii</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
            @error('name') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Opis (opcjonalny)</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-yellow-400 px-5 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                Dodaj kategorię
            </button>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">Anuluj</a>
        </div>
    </form>
</div>
@endsection
