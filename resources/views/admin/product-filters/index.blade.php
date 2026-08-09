@extends('layouts.admin')
@section('title', 'Filtry sklepu')

@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-zinc-100">Filtry sklepu</h1>
        <p class="mt-1 text-sm text-zinc-500">Grupy i etykiety, które można przypisywać do produktów oraz pokazywać w sklepie.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-200 hover:border-yellow-400 hover:text-yellow-400">
        <i data-lucide="package" class="h-4 w-4"></i>
        Produkty
    </a>
</div>

<div class="grid gap-6 xl:grid-cols-[26rem_1fr]">
    <section class="rounded-lg border border-zinc-800 bg-zinc-900 p-6">
        <h2 class="text-lg font-bold text-zinc-100">Dodaj grupę filtrów</h2>
        <p class="mt-1 text-sm text-zinc-500">Przykłady grup: Typ produktu, Przeznaczenie, Kolekcja, Materiał.</p>

        <form method="POST" action="{{ route('admin.product-filters.groups.store') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="is_active" value="0">
            <div>
                <label for="group_name" class="block text-sm font-medium text-zinc-300">Nazwa grupy</label>
                <input id="group_name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
            </div>
            <div>
                <label for="group_slug" class="block text-sm font-medium text-zinc-300">Slug</label>
                <input id="group_slug" name="slug" value="{{ old('slug') }}" placeholder="Może zostać puste" class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
            </div>
            <div>
                <label for="group_description" class="block text-sm font-medium text-zinc-300">Opis</label>
                <textarea id="group_description" name="description" rows="3" class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="group_sort_order" class="block text-sm font-medium text-zinc-300">Kolejność</label>
                    <input id="group_sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" required class="mt-1 block w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                </div>
                <label class="mt-7 inline-flex items-center gap-2 text-sm text-zinc-300">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
                    Aktywna
                </label>
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black hover:bg-yellow-300">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Dodaj grupę
            </button>
        </form>
    </section>

    <section class="space-y-5">
        @forelse($groups as $group)
            <article class="rounded-lg border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 p-5">
                    <form method="POST" action="{{ route('admin.product-filters.groups.update', $group) }}" class="grid gap-3 lg:grid-cols-[1fr_12rem_8rem_7rem_auto] lg:items-end">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="0">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Grupa</label>
                            <input name="name" value="{{ old('name', $group->name) }}" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Slug</label>
                            <input name="slug" value="{{ old('slug', $group->slug) }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Kolejność</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $group->sort_order) }}" min="0" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <label class="inline-flex items-center gap-2 pb-2 text-sm text-zinc-300">
                            <input type="checkbox" name="is_active" value="1" @checked($group->is_active) class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
                            Aktywna
                        </label>
                        <button class="rounded-lg border border-zinc-700 px-3 py-2 text-sm font-semibold text-zinc-200 hover:border-yellow-400 hover:text-yellow-400">Zapisz</button>
                        <div class="lg:col-span-5">
                            <label class="block text-xs font-medium text-zinc-500">Opis grupy</label>
                            <textarea name="description" rows="2" placeholder="Opis widoczny tylko w panelu" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">{{ old('description', $group->description) }}</textarea>
                        </div>
                    </form>
                </div>

                <div class="p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-zinc-100">Opcje w grupie</h3>
                            <p class="text-sm text-zinc-500">{{ $group->options_count }} opcji</p>
                        </div>
                        <form method="POST" action="{{ route('admin.product-filters.groups.destroy', $group) }}" onsubmit="return confirm('Usunąć całą grupę filtrów razem z opcjami?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm font-semibold text-red-400 hover:text-red-300">Usuń grupę</button>
                        </form>
                    </div>

                    <div class="space-y-2">
                        @foreach($group->options as $option)
                            <form method="POST" action="{{ route('admin.product-filters.options.update', [$group, $option]) }}" class="grid gap-2 rounded-lg border border-zinc-800 bg-zinc-950/60 p-3 lg:grid-cols-[1fr_10rem_7rem_7rem_auto_auto] lg:items-center">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="0">
                                <input name="name" value="{{ old('name', $option->name) }}" required class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                                <input name="slug" value="{{ old('slug', $option->slug) }}" class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                                <input type="number" name="sort_order" value="{{ old('sort_order', $option->sort_order) }}" min="0" required class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                                <label class="inline-flex items-center gap-2 text-sm text-zinc-300">
                                    <input type="checkbox" name="is_active" value="1" @checked($option->is_active) class="rounded border-zinc-700 bg-zinc-950 text-yellow-400 focus:ring-yellow-400">
                                    Aktywna
                                </label>
                                <span class="text-xs text-zinc-500">{{ $option->products_count }} produktów</span>
                                <button class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">Zapisz</button>
                            </form>
                            <form method="POST" action="{{ route('admin.product-filters.options.destroy', [$group, $option]) }}" onsubmit="return confirm('Usunąć tę opcję filtra?')" class="pl-3">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs font-semibold text-red-400 hover:text-red-300">Usuń opcję: {{ $option->name }}</button>
                            </form>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.product-filters.options.store', $group) }}" class="mt-5 grid gap-3 rounded-lg border border-dashed border-zinc-700 p-4 lg:grid-cols-[1fr_8rem_7rem_auto] lg:items-end">
                        @csrf
                        <input type="hidden" name="is_active" value="1">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Nowa opcja</label>
                            <input name="name" placeholder="Np. Koszulka, Dla dzieci, Bawełna" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Slug</label>
                            <input name="slug" placeholder="Opcjonalnie" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500">Kolejność</label>
                            <input type="number" name="sort_order" value="0" min="0" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400">
                        </div>
                        <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black hover:bg-yellow-300">Dodaj opcję</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-700 bg-zinc-900/60 p-10 text-center">
                <p class="font-semibold text-zinc-300">Brak grup filtrów.</p>
                <p class="mt-2 text-sm text-zinc-500">Dodaj pierwszą grupę, aby zacząć przypisywać etykiety do produktów.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
