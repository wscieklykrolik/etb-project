@props(['active' => ''])

@php
$groups = [
    'Panel' => [
        ['label' => 'Pulpit', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'active' => 'admin.dashboard'],
    ],
    'Sklep' => [
        ['label' => 'Zamówienia', 'route' => 'admin.orders.index', 'icon' => 'shopping-cart', 'active' => 'admin.orders.*'],
        ['label' => 'Produkty', 'route' => 'admin.products.index', 'icon' => 'package', 'active' => 'admin.products.*'],
        ['label' => 'Kategorie', 'route' => 'admin.categories.index', 'icon' => 'tags', 'active' => 'admin.categories.*'],
        ['label' => 'Filtry sklepu', 'route' => 'admin.product-filters.index', 'icon' => 'sliders-horizontal', 'active' => 'admin.product-filters.*'],
    ],
    'Terminarz' => [
        ['label' => 'Dodaj mecz', 'route' => 'admin.matches.create', 'icon' => 'calendar-plus', 'active' => 'admin.matches.*'],
    ],
    'Zarządzanie' => [
        ['label' => 'Sponsorzy', 'route' => 'profile.edit', 'params' => ['section' => 'sponsors'], 'icon' => 'star', 'active' => null],
    ],
];

if (Auth::user()->isAdmin()) {
    $groups['Zarządzanie'][] = ['label' => 'Użytkownicy', 'route' => 'admin.users.search', 'icon' => 'users', 'active' => 'admin.users.*'];
}
@endphp

<aside class="flex w-64 flex-col border-r border-zinc-800 bg-zinc-900">
    <div class="flex h-14 items-center gap-3 border-b border-zinc-800 px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <x-site-logo image-class="h-9 w-9 rounded-full bg-white object-contain p-1 ring-1 ring-yellow-300" fallback-class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-400 text-xs font-black text-black" />
            <span class="text-xl font-black text-yellow-400 tracking-tight">ETB</span>
        </a>
        <span class="text-xs text-zinc-500 uppercase tracking-wider">Admin</span>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto p-4">
        @foreach ($groups as $groupLabel => $items)
            <div>
                <p class="px-3 text-xs font-bold uppercase tracking-widest text-zinc-500">{{ $groupLabel }}</p>
                <div class="mt-2 space-y-1">
                    @foreach ($items as $item)
                        @php
                            $url = route($item['route'], $item['params'] ?? []);
                            $isActive = $item['active'] ? request()->routeIs($item['active']) : request()->fullUrl() === $url;
                        @endphp
                        <a href="{{ $url }}"
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                               'bg-yellow-400/10 text-yellow-400' => $isActive,
                               'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' => !$isActive,
                           ])>
                            <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-zinc-800 p-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-800 text-xs font-semibold text-zinc-300">{{ Str::upper(substr(Auth::user()->name, 0, 1)) }}</span>
            <div class="flex-1 min-w-0">
                <p class="truncate text-sm font-medium text-zinc-200">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-zinc-500">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-zinc-500 hover:text-zinc-300 transition-colors" title="Wyloguj">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
