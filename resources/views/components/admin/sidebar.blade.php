@props(['active' => ''])

@php
$groups = [
    'Panel' => [
        ['label' => 'Pulpit', 'route' => 'profile.edit', 'params' => ['section' => 'dashboard'], 'icon' => 'layout-dashboard', 'active' => null],
        ['label' => 'Historia zmian', 'route' => 'profile.edit', 'params' => ['section' => 'notifications-history'], 'icon' => 'history', 'active' => null],
        ['label' => 'Pytania i odpowiedzi', 'route' => 'profile.edit', 'params' => ['section' => 'faq'], 'icon' => 'circle-help', 'active' => null],
    ],
    'Zarządzanie' => [
        ['label' => 'Klub', 'route' => 'profile.edit', 'params' => ['section' => 'club-content'], 'icon' => 'building-2', 'active' => null],
        ['label' => 'Aktualności', 'route' => 'profile.edit', 'params' => ['section' => 'news'], 'icon' => 'newspaper', 'active' => null],
        ['label' => 'Akademia', 'route' => 'profile.edit', 'params' => ['section' => 'academy'], 'icon' => 'graduation-cap', 'active' => null],
        ['label' => 'Sponsorzy', 'route' => 'profile.edit', 'params' => ['section' => 'sponsors'], 'icon' => 'handshake', 'active' => null],
    ],
    'Terminarz' => [
        ['label' => 'Mecze', 'route' => 'profile.edit', 'params' => ['section' => 'matches'], 'icon' => 'calendar-days', 'active' => null],
        ['label' => 'Terminarz ŁZKosz', 'route' => 'schedule.lzkosz', 'icon' => 'calendar', 'active' => 'schedule.lzkosz'],
        ['label' => 'Tabela ligi', 'route' => 'profile.edit', 'params' => ['section' => 'league-table'], 'icon' => 'table-2', 'active' => null],
        ['label' => 'Terminarz 3x3', 'route' => 'schedule.3x3', 'icon' => 'calendar-range', 'active' => 'schedule.3x3'],
        ['label' => 'Turnieje 3x3', 'route' => 'profile.edit', 'params' => ['section' => 'tournaments'], 'icon' => 'trophy', 'active' => null],
    ],
    'Skład' => [
        ['label' => 'Zawodnicy', 'route' => 'profile.edit', 'params' => ['section' => 'players'], 'icon' => 'user-round', 'active' => null],
        ['label' => 'Sztab szkoleniowy', 'route' => 'profile.edit', 'params' => ['section' => 'staff'], 'icon' => 'user-cog', 'active' => null],
        ['label' => 'Drużyna 3x3', 'route' => 'profile.edit', 'params' => ['section' => 'three-x-three'], 'icon' => 'circle-dot', 'active' => null],
    ],
    'Sklep' => [
        ['label' => 'Podsumowanie sklepu', 'route' => 'profile.edit', 'params' => ['section' => 'shop'], 'icon' => 'store', 'active' => null],
        ['label' => 'Zamówienia', 'route' => 'admin.orders.index', 'icon' => 'shopping-cart', 'active' => 'admin.orders.*'],
        ['label' => 'Produkty', 'route' => 'admin.products.index', 'icon' => 'package', 'active' => 'admin.products.*'],
        ['label' => 'Kategorie', 'route' => 'admin.categories.index', 'icon' => 'tags', 'active' => 'admin.categories.*'],
        ['label' => 'Filtry sklepu', 'route' => 'admin.product-filters.index', 'icon' => 'sliders-horizontal', 'active' => 'admin.product-filters.*'],
    ],
];

if (Auth::user()->isAdmin()) {
    array_unshift($groups['Zarządzanie'], ['label' => 'Użytkownicy', 'route' => 'profile.edit', 'params' => ['section' => 'users'], 'icon' => 'users', 'active' => null]);
}
@endphp

<aside class="flex w-64 flex-col border-r border-zinc-800 bg-zinc-900">
    <div class="flex h-14 items-center gap-3 border-b border-zinc-800 px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <x-site-logo :url="$adminLogoUrl" alt="Logo panelu admina" image-class="h-9 w-9 rounded-full bg-white object-contain p-1 ring-1 ring-yellow-300" fallback-class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-400 text-xs font-black text-black" />
            <span class="text-xl font-black text-yellow-400 tracking-tight">ETB Łódź</span>
        </a>
        <span class="text-xs text-zinc-500 uppercase tracking-wider">Admin</span>
    </div>

    <nav class="admin-side-nav min-h-0 flex-1 space-y-6 overflow-y-auto p-4">
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

    <div class="shrink-0 border-t border-zinc-800 p-4">
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
