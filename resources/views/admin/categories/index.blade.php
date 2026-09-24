@extends('layouts.admin')
@section('title', 'Kategorie')

@section('admin-content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-950">Kategorie</h1>
        <p class="mt-1 text-sm text-slate-500">Kategoryzacja produktów w sklepie.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Dodaj kategorię
    </a>
</div>

<div class="rounded-lg border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase text-slate-500">
                    <th class="px-5 py-3 font-medium">Nazwa</th>
                    <th class="px-5 py-3 font-medium">Slug</th>
                    <th class="px-5 py-3 font-medium">Produkty</th>
                    <th class="px-5 py-3 text-right font-medium">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="font-medium text-slate-900">{{ $category->name }}</span>
                            @if($category->description)
                                <p class="mt-0.5 text-xs text-slate-500">{{ Str::limit($category->description, 60) }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $category->slug }}</td>
                        <td class="px-5 py-3 text-slate-900">{{ $category->products_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-1 text-sm text-yellow-800 hover:text-yellow-900 transition-colors">
                                Edytuj
                                <i data-lucide="arrow-right" class="h-3 w-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">
                            Brak kategorii. <a href="{{ route('admin.categories.create') }}" class="text-yellow-800 hover:underline">Dodaj pierwszą</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="border-t border-slate-200 px-5 py-3">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
