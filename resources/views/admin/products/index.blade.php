@extends('layouts.admin')
@section('title', 'Produkty')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-zinc-100">Produkty</h1>
        <p class="mt-1 text-sm text-zinc-500">Zarządzanie produktami w sklepie.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black hover:bg-yellow-300 transition-colors">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Dodaj produkt
    </a>
</div>

<div class="rounded-lg border border-zinc-800 bg-zinc-900">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-800 text-left text-xs uppercase text-zinc-500">
                    <th class="px-5 py-3 font-medium">Nazwa</th>
                    <th class="px-5 py-3 font-medium">Kategoria</th>
                    <th class="px-5 py-3 font-medium">Cena</th>
                    <th class="px-5 py-3 font-medium">Stan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 text-right font-medium">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($products as $product)
                    <tr class="hover:bg-zinc-800/50">
                        <td class="px-5 py-3">
                            <span class="font-medium text-zinc-200">{{ $product->name }}</span>
                            @if($product->filterOptions->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($product->filterOptions->take(4) as $option)
                                        <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[11px] font-semibold text-zinc-300">{{ $option->group?->name }}: {{ $option->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($product->images)
                                <div class="mt-1 flex -space-x-1">
                                    @foreach(array_slice($product->images, 0, 3) as $img)
                                        <img src="{{ \App\Support\MediaStorage::url($img) }}" alt="" class="h-6 w-6 rounded-full border border-zinc-700 object-cover">
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-400">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-zinc-200">{{ number_format($product->grossPriceGrosze() / 100, 2, ',', '') }} zł</td>
                        <td class="px-5 py-3">
                            @if($product->stock_qty > 10)
                                <span class="text-green-400">{{ $product->stock_qty }} szt.</span>
                            @elseif($product->stock_qty > 0)
                                <span class="text-yellow-400">{{ $product->stock_qty }} szt.</span>
                            @else
                                <span class="text-red-400">Brak</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($product->is_published)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                                    Opublikowany
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-500/10 px-2 py-0.5 text-xs font-medium text-zinc-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                    Szkic
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 text-sm text-yellow-400 hover:text-yellow-300 transition-colors">
                                Edytuj
                                <i data-lucide="arrow-right" class="h-3 w-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-zinc-500">
                            Brak produktów. <a href="{{ route('admin.products.create') }}" class="text-yellow-400 hover:underline">Dodaj pierwszy</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="border-t border-zinc-800 px-5 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection

