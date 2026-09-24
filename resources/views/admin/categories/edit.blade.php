@extends('layouts.admin')
@section('title', 'Edycja kategorii')

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Powrót do kategorii
    </a>
    <h1 class="mt-2 text-2xl font-bold text-slate-950">{{ $category->name }}</h1>
</div>

<div class="max-w-lg rounded-lg border border-slate-200 bg-white p-6">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nazwa kategorii</label>
            <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
            @error('name') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Opis (opcjonalny)</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description', $category->description) }}</textarea>
            @error('description') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-yellow-400 px-5 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                Zapisz zmiany
            </button>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">Anuluj</a>

            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="ml-auto" onsubmit="return confirm('Usunąć kategorię „{{ $category->name }}"?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-700 hover:text-red-800 transition-colors">Usuń</button>
            </form>
        </div>
    </form>
</div>
@endsection
