@extends('layouts.app')

@section('content')
@php
    $activeFiltersCount = (int) filled(request('category'))
        + count($selectedSizes)
        + count($selectedFilterOptions)
        + (int) (request('availability') === 'in_stock')
        + (int) filled(request('min_price'))
        + (int) filled(request('max_price'));
@endphp

<div class="bg-black text-white">
    <section class="border-b border-zinc-800/50 bg-zinc-950 py-12">
        <div class="mx-auto grid max-w-7xl gap-8 px-6 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Sklep</p>
                <h1 class="mt-2 text-4xl font-black uppercase md:text-5xl">Kupuj oficjalny merch Eat The Ball</h1>
                <p class="mt-4 max-w-2xl text-base text-zinc-400">Koszulki, akcesoria i gadżety klubowe z aktualną dostępnością oraz filtrami.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:w-[28rem]">
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center justify-center gap-2 rounded-lg bg-yellow-400 px-5 py-3 text-sm font-black uppercase text-black transition hover:bg-white">
                    <i data-lucide="shopping-cart" class="h-4 w-4"></i>
                    Koszyk
                    <span x-data="{ count: 0 }" x-init="fetch('{{ route('cart.badge') }}').then(r=>r.json()).then(d=>count=d.count); setInterval(()=>fetch('{{ route('cart.badge') }}').then(r=>r.json()).then(d=>count=d.count),30000)" x-show="count > 0" class="absolute -right-2 -top-2 flex h-6 min-w-6 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-black text-white" x-text="count"></span>
                </a>
                <a href="{{ auth()->check() ? route('profile.edit', ['section' => 'account']) : route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-700 px-5 py-3 text-sm font-black uppercase text-white transition hover:border-yellow-400 hover:bg-yellow-400 hover:text-black">
                    <i data-lucide="user-round" class="h-4 w-4"></i>
                    Konto
                </a>
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="mx-auto grid max-w-7xl gap-8 px-6 lg:grid-cols-[19rem_1fr]">
            <aside class="self-start rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:sticky lg:top-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-400">Filtry</p>
                        <h2 class="mt-1 text-lg font-black">Dopasuj produkty</h2>
                    </div>
                    @if($activeFiltersCount > 0)
                        <a href="{{ route('shop.index') }}" class="text-xs font-bold uppercase tracking-wide text-zinc-400 hover:text-yellow-400">Wyczyść</a>
                    @endif
                </div>

                <form method="GET" action="{{ route('shop.index') }}" class="mt-6 space-y-6">
                    <div>
                        <label for="sort" class="block text-xs font-bold uppercase tracking-wide text-zinc-500">Sortowanie</label>
                        <select id="sort" name="sort" class="mt-2 w-full rounded-lg border border-zinc-700 bg-black px-3 py-2 text-sm text-white focus:border-yellow-400 focus:ring-yellow-400">
                            <option value="newest" @selected($selectedSort === 'newest')>Najnowsze</option>
                            <option value="price_asc" @selected($selectedSort === 'price_asc')>Cena rosnąco</option>
                            <option value="price_desc" @selected($selectedSort === 'price_desc')>Cena malejąco</option>
                            <option value="name" @selected($selectedSort === 'name')>Nazwa A-Z</option>
                        </select>
                    </div>

                    @if($categories->isNotEmpty())
                        <fieldset>
                            <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">Kategorie</legend>
                            <div class="mt-3 space-y-2">
                                <label class="flex items-center justify-between gap-3 rounded-lg border border-zinc-800 px-3 py-2 text-sm hover:border-yellow-400">
                                    <span>Wszystkie</span>
                                    <input type="radio" name="category" value="" @checked(! $selectedCategory) class="border-zinc-700 bg-black text-yellow-400 focus:ring-yellow-400">
                                </label>
                                @foreach($categories as $category)
                                    <label class="flex items-center justify-between gap-3 rounded-lg border border-zinc-800 px-3 py-2 text-sm hover:border-yellow-400">
                                        <span>{{ $category->name }}</span>
                                        <input type="radio" name="category" value="{{ $category->id }}" @checked($selectedCategory === $category->id) class="border-zinc-700 bg-black text-yellow-400 focus:ring-yellow-400">
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    @if($availableSizes->isNotEmpty())
                        <fieldset>
                            <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">Rozmiary</legend>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                @foreach($availableSizes as $size)
                                    <label @class([
                                        'flex cursor-pointer items-center justify-center rounded-lg border px-3 py-2 text-sm font-black hover:border-yellow-400',
                                        'border-yellow-400 bg-yellow-400 text-black' => in_array($size, $selectedSizes, true),
                                        'border-zinc-800 text-white' => ! in_array($size, $selectedSizes, true),
                                    ])>
                                        <input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size, $selectedSizes, true)) class="sr-only">
                                        {{ $size }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    @foreach($filterGroups as $group)
                        <fieldset>
                            <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">{{ $group->name }}</legend>
                            <div class="mt-3 space-y-2">
                                @foreach($group->options as $option)
                                    <label class="flex items-center justify-between gap-3 rounded-lg border border-zinc-800 px-3 py-2 text-sm hover:border-yellow-400">
                                        <span>{{ $option->name }}</span>
                                        <span class="flex items-center gap-2">
                                            <span class="text-xs text-zinc-500">{{ $option->products_count }}</span>
                                            <input type="checkbox" name="filter_options[]" value="{{ $option->id }}" @checked(in_array($option->id, $selectedFilterOptions, true)) class="rounded border-zinc-700 bg-black text-yellow-400 focus:ring-yellow-400">
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <fieldset>
                        <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">Cena</legend>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" placeholder="Od" class="w-full rounded-lg border border-zinc-700 bg-black px-3 py-2 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-yellow-400">
                            <input type="number" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" placeholder="Do" class="w-full rounded-lg border border-zinc-700 bg-black px-3 py-2 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-yellow-400">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-xs font-bold uppercase tracking-wide text-zinc-500">Dostępność</legend>
                        <label class="mt-3 flex items-center gap-2 rounded-lg border border-zinc-800 px-3 py-2 text-sm hover:border-yellow-400">
                            <input type="checkbox" name="availability" value="in_stock" @checked($selectedAvailability === 'in_stock') class="rounded border-zinc-700 bg-black text-yellow-400 focus:ring-yellow-400">
                            Tylko dostępne
                        </label>
                    </fieldset>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-yellow-400 px-4 py-3 text-sm font-black uppercase text-black hover:bg-white">
                        <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                        Zastosuj filtry
                    </button>
                </form>
            </aside>

            <div>
                <div class="mb-6 flex flex-col gap-3 border-b border-zinc-900 pb-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm text-zinc-500">Znaleziono produktów</p>
                        <p class="text-2xl font-black">{{ $products->total() }}</p>
                    </div>
                    @if($activeFiltersCount > 0)
                        <p class="text-sm text-zinc-400">Aktywne filtry: <span class="font-bold text-yellow-400">{{ $activeFiltersCount }}</span></p>
                    @endif
                </div>

                @if($products->isEmpty())
                    <div class="rounded-lg border border-dashed border-zinc-700 bg-zinc-900/50 p-12 text-center">
                        <p class="text-lg font-bold text-zinc-400">Brak produktów w sklepie</p>
                        <p class="mt-2 text-sm text-zinc-500">Zmień filtry albo wróć do pełnej listy produktów.</p>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('shop.show', $product) }}" class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900 transition-all hover:-translate-y-1 hover:border-yellow-400/50 hover:shadow-xl hover:shadow-yellow-400/5">
                                <div class="aspect-square overflow-hidden bg-zinc-800 flex items-center justify-center">
                                    @if($product->images && $img = $product->images[0] ?? null)
                                        <img src="{{ asset('storage/'.$img) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <span class="text-4xl font-black text-zinc-700">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <div class="flex flex-wrap gap-2">
                                        @if($product->category)
                                            <span class="text-xs font-black uppercase tracking-wider text-yellow-400">{{ $product->category->name }}</span>
                                        @endif
                                        @foreach($product->filterOptions->take(2) as $option)
                                            <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-zinc-300">{{ $option->name }}</span>
                                        @endforeach
                                    </div>
                                    <h3 class="mt-2 text-lg font-black">{{ $product->name }}</h3>
                                    <p class="mt-2 text-sm text-zinc-400 line-clamp-2">{{ $product->description }}</p>
                                    <div class="mt-4 flex items-end justify-between gap-3">
                                        <p class="text-xl font-black text-yellow-400">{{ $product->displayPrice() }}</p>
                                        @if($product->variantSizes->isNotEmpty())
                                            <p class="text-xs font-bold text-zinc-500">{{ $product->variantSizes->pluck('size_label')->take(4)->join(' / ') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
