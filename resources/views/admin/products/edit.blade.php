@extends('layouts.admin')
@section('title', 'Edycja produktu')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1 text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Powrót do produktów
    </a>
    <h1 class="mt-2 text-2xl font-bold text-zinc-100">{{ $product->name }}</h1>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-zinc-800 bg-zinc-900 p-6">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-zinc-300">Nazwa produktu</label>
                <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-zinc-300">Opis</label>
                <textarea id="description" name="description" rows="4"
                          class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price_grosze" class="block text-sm font-medium text-zinc-300">Cena (gr)</label>
                    <input id="price_grosze" type="number" name="price_grosze" value="{{ old('price_grosze', $product->price_grosze) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    @error('price_grosze') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="stock_qty" class="block text-sm font-medium text-zinc-300">Stan magazynowy</label>
                    <input id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    @error('stock_qty') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-zinc-300">Kategoria</label>
                <select id="category_id" name="category_id"
                        class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                    <option value="">Brak kategorii</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            @if($filterGroups->isNotEmpty())
                <div class="rounded-lg border border-zinc-800 bg-zinc-950/70 p-4">
                    <h2 class="text-sm font-semibold text-zinc-200">Etykiety i filtry produktu</h2>
                    <p class="mt-1 text-xs text-zinc-500">Zaznaczone opcje pojawią się jako filtry w sklepie.</p>
                    <div class="mt-4 space-y-4">
                        @foreach($filterGroups as $group)
                            <fieldset>
                                <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">{{ $group->name }}</legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($group->options as $option)
                                        <label class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-3 py-2 text-sm text-zinc-300 hover:border-yellow-400">
                                            <input type="checkbox" name="filter_options[]" value="{{ $option->id }}" @checked(in_array($option->id, old('filter_options', $selectedFilterOptions))) class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
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
                           class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
                    <span class="text-sm text-zinc-300">Opublikowany</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="is_physical" value="0">
                    <input type="checkbox" name="is_physical" value="1" {{ old('is_physical', $product->is_physical) ? 'checked' : '' }}
                           class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
                    <span class="text-sm text-zinc-300">Produkt fizyczny</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-300">Zdjęcia</label>
                @if($product->images)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($product->images as $img)
                            <div class="relative">
                                <img src="{{ \App\Support\MediaStorage::url($img) }}" alt="" class="h-16 w-16 rounded-lg border border-zinc-700 object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="images[]" multiple accept="image/*"
                       class="mt-2 block w-full text-sm text-zinc-400 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-800 file:px-3 file:py-1.5 file:text-sm file:text-zinc-200 hover:file:bg-zinc-700">
                @error('images.*') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-yellow-400 px-5 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                    Zapisz zmiany
                </button>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">Anuluj</a>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-zinc-800 bg-zinc-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-zinc-200">Warianty / Rozmiary</h2>
            </div>

            @if($variants->isNotEmpty())
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="text-left text-xs uppercase text-zinc-500">
                            <th class="pb-2 pr-2 font-medium">Rozmiar</th>
                            <th class="pb-2 pr-2 font-medium">Stan</th>
                            <th class="pb-2 pr-2 font-medium">Dopłata</th>
                            <th class="pb-2 text-right font-medium">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach($variants as $variant)
                            <tr>
                                <td class="py-2 pr-2 text-zinc-200">{{ $variant->size_label }}</td>
                                <td class="py-2 pr-2">
                                    @if($variant->stock_qty > 0)
                                        <span class="text-green-400">{{ $variant->stock_qty }} szt.</span>
                                    @else
                                        <span class="text-red-400">Brak</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-2 text-zinc-400">{{ number_format($variant->extra_price_grosze / 100, 2, ',', '') }} zł</td>
                                <td class="py-2 text-right">
                                    <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" onsubmit="return confirm('Usunąć ten rozmiar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-300">Usuń</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mb-4 text-sm text-zinc-500">Brak wariantów rozmiarów.</p>
            @endif

            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="flex flex-wrap items-end gap-3 border-t border-zinc-800 pt-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1">Rozmiar</label>
                    <input type="text" name="size_label" placeholder="Np. S, M, L, XL" required
                           class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1">Stan</label>
                    <input type="number" name="stock_qty" value="0" min="0" required
                           class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 w-20 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-400 mb-1">Dopłata (gr)</label>
                    <input type="number" name="extra_price_grosze" value="0" min="0" required
                           class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 w-24 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <button type="submit" class="rounded-lg bg-yellow-400 px-3 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
                    Dodaj
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-zinc-800 bg-zinc-900 p-6">
            <h2 class="text-sm font-semibold text-zinc-200 mb-3">Usuń produkt</h2>
            <p class="mb-3 text-sm text-zinc-500">Tej operacji nie można cofnąć.</p>
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

