@extends('layouts.admin')
@section('title', 'Edycja produktu')

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Powrót do produktów
    </a>
    <h1 class="mt-2 text-2xl font-bold text-slate-950">{{ $product->name }}</h1>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nazwa produktu</label>
                <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                @error('name') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Opis</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 placeholder-slate-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price_grosze" class="block text-sm font-medium text-slate-700">Cena (gr)</label>
                    <input id="price_grosze" type="number" name="price_grosze" value="{{ old('price_grosze', $product->price_grosze) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    @error('price_grosze') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="stock_qty" class="block text-sm font-medium text-slate-700">Stan magazynowy</label>
                    <input id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    @error('stock_qty') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">Kategoria</label>
                <select id="category_id" name="category_id"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    <option value="">Brak kategorii</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
            </div>

            @if($filterGroups->isNotEmpty())
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <h2 class="text-sm font-semibold text-slate-900">Etykiety i filtry produktu</h2>
                    <p class="mt-1 text-xs text-slate-500">Zaznaczone opcje pojawią się jako filtry w sklepie.</p>
                    <div class="mt-4 space-y-4">
                        @foreach($filterGroups as $group)
                            <fieldset>
                                <legend class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $group->name }}</legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($group->options as $option)
                                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:border-yellow-400">
                                            <input type="checkbox" name="filter_options[]" value="{{ $option->id }}" @checked(in_array($option->id, old('filter_options', $selectedFilterOptions))) class="rounded border-slate-300 bg-white text-yellow-800 focus:ring-yellow-400">
                                            {{ $option->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $product->is_published) ? 'checked' : '' }}
                           class="rounded border-slate-300 bg-white text-yellow-800 focus:ring-yellow-400">
                    <span class="text-sm text-slate-700">Opublikowany</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="is_physical" value="0">
                    <input type="checkbox" name="is_physical" value="1" {{ old('is_physical', $product->is_physical) ? 'checked' : '' }}
                           class="rounded border-slate-300 bg-white text-yellow-800 focus:ring-yellow-400">
                    <span class="text-sm text-slate-700">Produkt fizyczny</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Zdjęcia</label>
                @if($product->images)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($product->images as $img)
                            <div class="relative">
                                <img src="{{ \App\Support\MediaStorage::url($img) }}" alt="" class="h-16 w-16 rounded-lg border border-slate-300 object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="images[]" multiple accept="image/*"
                       class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:text-slate-900 hover:file:bg-slate-200">
                @error('images.*') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-yellow-400 px-5 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                    Zapisz zmiany
                </button>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">Anuluj</a>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Warianty / Rozmiary</h2>
            </div>

            @if($variants->isNotEmpty())
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-500">
                            <th class="pb-2 pr-2 font-medium">Rozmiar</th>
                            <th class="pb-2 pr-2 font-medium">Stan</th>
                            <th class="pb-2 pr-2 font-medium">Dopłata</th>
                            <th class="pb-2 text-right font-medium">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($variants as $variant)
                            <tr>
                                <td class="py-2 pr-2 text-slate-900">{{ $variant->size_label }}</td>
                                <td class="py-2 pr-2">
                                    @if($variant->stock_qty > 0)
                                        <span class="text-emerald-700">{{ $variant->stock_qty }} szt.</span>
                                    @else
                                        <span class="text-red-700">Brak</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-2 text-slate-600">{{ number_format($variant->extra_price_grosze / 100, 2, ',', '') }} zł</td>
                                <td class="py-2 text-right">
                                    <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" onsubmit="return confirm('Usunąć ten rozmiar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-700 hover:text-red-800">Usuń</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mb-4 text-sm text-slate-500">Brak wariantów rozmiarów.</p>
            @endif

            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="flex flex-wrap items-end gap-3 border-t border-slate-200 pt-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Rozmiar</label>
                    <input type="text" name="size_label" placeholder="Np. S, M, L, XL" required
                           class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Stan</label>
                    <input type="number" name="stock_qty" value="0" min="0" required
                           class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 w-20 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dopłata (gr)</label>
                    <input type="number" name="extra_price_grosze" value="0" min="0" required
                           class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 w-24 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <button type="submit" class="rounded-lg bg-yellow-400 px-3 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                    Dodaj
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Usuń produkt</h2>
            <p class="mb-3 text-sm text-slate-500">Tej operacji nie można cofnąć.</p>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Na pewno usunąć produkt „{{ $product->name }}"?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 transition-colors">
                    Usuń produkt
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

