@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white">
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-6">
            <a href="{{ route('shop.index') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-zinc-400 transition hover:text-yellow-400">
                <span aria-hidden="true">←</span> Powrót do sklepu
            </a>

            <div class="grid gap-12 lg:grid-cols-2">
                <div class="flex aspect-square items-center justify-center overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900">
                    @if($product->images && $img = $product->images[0] ?? null)
                        <img src="{{ \App\Support\MediaStorage::url($img) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-6xl font-black text-zinc-700">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">{{ $product->category?->name }}</p>
                    <h1 class="mt-3 text-4xl font-black uppercase">{{ $product->name }}</h1>
                    <p class="mt-6 text-sm leading-relaxed text-zinc-400">{{ $product->description }}</p>

                    <div class="mt-8 border-t border-zinc-800 pt-8">
                        <p class="text-3xl font-black text-yellow-400">{{ $product->displayPrice() }}</p>
                        <p class="mt-2 text-sm text-zinc-500">brutto (w tym 23% VAT)</p>
                    </div>

                    @if($product->variantSizes->isNotEmpty())
                        <div class="mt-8">
                            <p class="mb-4 text-sm font-bold uppercase tracking-wider text-zinc-300">Dostępne rozmiary</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($product->variantSizes as $variant)
                                    <div class="min-w-[80px] rounded-lg border border-zinc-700 bg-zinc-900 px-5 py-3 text-center">
                                        <p class="text-lg font-black text-white">{{ $variant->size_label }}</p>
                                        <p class="text-xs text-zinc-500">{{ $variant->stock_qty > 0 ? 'Dostępny' : 'Brak' }}</p>
                                        @if($variant->extra_price_grosze > 0)
                                            <p class="text-xs text-yellow-400">+{{ number_format($variant->extra_price_grosze / 100, 2, ',', '') }} zł</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($product->filterOptions->isNotEmpty())
                        <div class="mt-8">
                            <p class="mb-3 text-sm font-bold uppercase tracking-wider text-zinc-300">Etykiety produktu</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->filterOptions as $option)
                                    <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs font-bold uppercase tracking-wide text-zinc-300">{{ $option->group?->name }}: {{ $option->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-10">
                        <form action="{{ route('cart.add') }}" method="POST" class="flex flex-wrap items-end gap-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if($product->variantSizes->isNotEmpty())
                                <div>
                                    <label for="variant_size_id" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-400">Rozmiar</label>
                                    <select id="variant_size_id" name="variant_size_id" class="rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-3 text-sm font-bold text-white">
                                        @foreach($product->variantSizes as $variant)
                                            <option value="{{ $variant->id }}" @disabled($variant->stock_qty <= 0)>
                                                {{ $variant->size_label }}{{ $variant->stock_qty <= 0 ? ' - brak' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label for="qty" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-400">Ilość</label>
                                <input type="number" id="qty" name="qty" value="1" min="1" max="{{ $product->stock_qty > 0 ? $product->stock_qty : 99 }}" class="w-20 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-3 text-center text-lg font-bold text-white">
                            </div>
                            <button type="submit" class="rounded-lg bg-yellow-400 px-8 py-3 text-sm font-black uppercase text-black shadow-lg shadow-yellow-400/20 transition-all hover:bg-white">Dodaj do koszyka</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

