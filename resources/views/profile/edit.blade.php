@extends('layouts.app')

@php
    use App\Models\TeamMatch;
    use App\Models\ThreeXThreeTournament;
    use App\Services\ThreeXThreeTournamentFlowService;

    $isPanelUser = $isAdmin || $isEmployee;
    $publishedNewsCount = $publishedNews->count();
    $scheduledNewsCount = $scheduledNews->count();
    $matchesCount = $upcomingMatches->count() + $finishedMatches->count();
    $usersCount = method_exists($users, 'total') ? $users->total() : $users->count();
    $sectionUrl = fn (string $section): string => route('profile.edit', ['section' => $section]);
    $sectionClasses = fn (string $section): string => $activeSection === $section
        ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black'
        : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black';
    $notificationActionLabels = [
        'created' => 'Dodano',
        'updated' => 'Zmieniono',
        'deleted' => 'Usunięto',
        'published' => 'Opublikowano',
    ];
    $roleLabels = [
        \App\Models\User::ROLE_ADMIN => 'Administrator',
        \App\Models\User::ROLE_ATHLETE => 'Zawodnik',
        \App\Models\User::ROLE_FAN => 'Kibic',
        \App\Models\User::ROLE_EMPLOYEE => 'Pracownik',
    ];
    $tournamentFlow = app(ThreeXThreeTournamentFlowService::class);
@endphp

@section('hide_footer', $isPanelUser ? 'true' : 'false')

@section('content')
<div class="min-h-screen bg-slate-100 text-slate-950"
     x-data="adminPanel({
        currentAccount: @js(['name' => $user->name, 'email' => $user->email, 'role' => $user->role]),
        unreadCount: @js($unreadNotificationsCount),
     })"
     @keydown.escape.window="openModal = null">
    @if ($isPanelUser)
        <div class="grid min-h-screen bg-slate-950 lg:grid-cols-[18rem_1fr]">
            <aside class="bg-slate-950 text-white">
                <div class="sticky top-0 flex h-screen flex-col px-5 py-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <x-site-logo :url="$adminLogoUrl" alt="Logo panelu admina" image-class="h-11 w-11 rounded-full bg-white object-contain p-1 ring-1 ring-yellow-300" fallback-class="flex h-11 w-11 items-center justify-center rounded-full bg-yellow-400 text-xl font-black text-black" />
                        <span>
                            <span class="block text-xl font-black leading-5 text-yellow-400">ETB Łódź</span>
                            <span class="block text-xs font-bold uppercase tracking-[0.22em] text-white">Admin</span>
                        </span>
                    </a>

                    <nav class="mt-8 space-y-6 text-sm">
                        <div>
                            <a href="{{ $sectionUrl('dashboard') }}" class="{{ $activeSection === 'dashboard' ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-4 py-3 font-black text-black' : 'flex items-center gap-3 rounded-lg px-4 py-3 font-black text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}">
                                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                                Pulpit
                            </a>
                        </div>

                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Zarządzanie</p>
                            <div class="mt-3 space-y-1">
                                @if ($isAdmin)
                                    <a href="{{ $sectionUrl('users') }}" class="{{ $sectionClasses('users') }}"><i data-lucide="users" class="h-4 w-4"></i>Użytkownicy</a>
                                @endif
                                <a href="{{ $sectionUrl('matches') }}" class="{{ $sectionClasses('matches') }}"><i data-lucide="calendar-days" class="h-4 w-4"></i>Mecze</a>
                                <a href="{{ $sectionUrl('club-content') }}" class="{{ $sectionClasses('club-content') }}"><i data-lucide="building-2" class="h-4 w-4"></i>Klub</a>
                                <a href="{{ $sectionUrl('academy') }}" class="{{ $sectionClasses('academy') }}"><i data-lucide="graduation-cap" class="h-4 w-4"></i>Akademia</a>
                                <a href="{{ $sectionUrl('faq') }}" class="{{ $sectionClasses('faq') }}"><i data-lucide="circle-help" class="h-4 w-4"></i>Pytania i odpowiedzi</a>
                                <a href="{{ $sectionUrl('news') }}" class="{{ $sectionClasses('news') }}"><i data-lucide="newspaper" class="h-4 w-4"></i>Aktualności</a>
                                <a href="{{ $sectionUrl('players') }}" class="{{ $sectionClasses('players') }}"><i data-lucide="user-round" class="h-4 w-4"></i>Zawodnicy</a>
                                <a href="{{ $sectionUrl('staff') }}" class="{{ $sectionClasses('staff') }}"><i data-lucide="user-cog" class="h-4 w-4"></i>Sztab szkoleniowy</a>
                                <a href="{{ $sectionUrl('three-x-three') }}" class="{{ $sectionClasses('three-x-three') }}"><i data-lucide="circle-dot" class="h-4 w-4"></i>Drużyna 3x3</a>
                                <a href="{{ $sectionUrl('tournaments') }}" class="{{ $sectionClasses('tournaments') }}"><i data-lucide="trophy" class="h-4 w-4"></i>Turnieje 3x3</a>
                                <a href="{{ $sectionUrl('notifications-history') }}" class="{{ $sectionClasses('notifications-history') }}"><i data-lucide="history" class="h-4 w-4"></i>Historia zmian</a>
                            </div>
                        </div>

                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Terminarz</p>
                            <div class="mt-3 space-y-1">
                                <a href="{{ route('schedule.lzkosz') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="calendar" class="h-4 w-4"></i>Terminarz ŁZKosz</a>
                                <a href="{{ $sectionUrl('league-table') }}" class="{{ $sectionClasses('league-table') }}"><i data-lucide="table-2" class="h-4 w-4"></i>Tabela ligi</a>
                                <a href="{{ route('schedule.3x3') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="calendar-range" class="h-4 w-4"></i>Terminarz 3x3</a>
                                <a href="{{ $sectionUrl('sponsors') }}" class="{{ $sectionClasses('sponsors') }}"><i data-lucide="handshake" class="h-4 w-4"></i>Sponsorzy</a>
                            </div>
                        </div>
                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Sklep</p>
                            <div class="mt-3 space-y-1">
                                <a href="{{ $sectionUrl('shop') }}" class="{{ $sectionClasses('shop') }}"><i data-lucide="store" class="h-4 w-4"></i>Podsumowanie sklepu</a>
                                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="shopping-cart" class="h-4 w-4"></i>Zamówienia</a>
                                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="package" class="h-4 w-4"></i>Produkty</a>
                                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="tags" class="h-4 w-4"></i>Kategorie</a>
                                <a href="{{ route('admin.product-filters.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="sliders-horizontal" class="h-4 w-4"></i>Filtry sklepu</a>
                            </div>
                        </div>
                    </nav>

                    <div class="mt-auto border-t border-white/10 pt-5">
                        <a href="{{ $sectionUrl('account') }}" class="{{ $sectionClasses('account') }}"><i data-lucide="settings" class="h-4 w-4"></i>Profil</a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-slate-200 transition hover:bg-yellow-400 hover:text-black">
                                <i data-lucide="log-out" class="h-4 w-4"></i>
                                Wyloguj
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="bg-slate-100">
                <header class="sticky top-0 z-30 border-b border-slate-200 bg-slate-950 px-4 py-4 text-white shadow-sm sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <form class="relative w-full lg:max-w-md" role="search" @submit.prevent>
                            <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                            <input type="search" x-model="panelSearch" @input.debounce.150ms="searchPanel" placeholder="Szukaj w panelu admina..." class="w-full rounded-full border border-white/15 bg-white/5 py-2.5 pl-11 pr-4 text-sm text-white placeholder:text-slate-400 focus:border-yellow-400 focus:ring-yellow-400">
                        </form>
                        <div class="flex items-center gap-4">
                            <div class="relative" @click.outside="notificationsOpen = false">
                                <button type="button" class="relative inline-flex rounded-full p-2 transition hover:bg-white/10" @click="notificationsOpen = !notificationsOpen">
                                    <i data-lucide="bell" class="h-5 w-5 transition hover:animate-[admin-bell-ring_700ms_ease-in-out]"></i>
                                    <span x-show="unreadCount > 0" x-text="notificationBadge" class="absolute -right-1 -top-1 rounded-full bg-yellow-400 px-1.5 text-[10px] font-black text-black"></span>
                                </button>

                                <div x-show="notificationsOpen" x-cloak x-transition class="absolute right-0 z-50 mt-3 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-950 shadow-2xl">
                                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                        <div>
                                            <h3 class="font-black">Powiadomienia</h3>
                                            <p class="text-xs text-slate-500">Globalny dziennik zmian pracowników.</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-black text-yellow-900" x-text="notificationBadge"></span>
                                            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button title="Oznacz wszystkie jako przeczytane" @disabled($unreadNotificationsCount === 0) class="{{ $unreadNotificationsCount > 0 ? 'text-emerald-700 hover:bg-emerald-50' : 'cursor-not-allowed text-slate-300' }} rounded-lg border border-slate-200 bg-white p-2">
                                                    <i data-lucide="mail-check" class="h-4 w-4"></i>
                                                    <span class="sr-only">Oznacz wszystkie jako przeczytane</span>
                                                </button>
                                            </form>
                                            <a href="{{ $sectionUrl('notifications-history') }}" title="Historia zmian" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700 hover:bg-slate-50">
                                                <i data-lucide="history" class="h-4 w-4"></i>
                                                <span class="sr-only">Historia zmian</span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="max-h-[28rem] divide-y divide-slate-100 overflow-y-auto">
                                        @forelse ($adminNotifications as $notification)
                                            <article class="p-4 transition hover:bg-yellow-50">
                                                <div class="flex items-start justify-between gap-3">
                                                    <button type="button" class="min-w-0 text-left" @click="previewNotification = {{ Js::from([
                                                        'title' => $notification->subject_label,
                                                        'description' => $notification->description,
                                                        'actor' => $notification->actor?->name ?? 'System',
                                                        'date' => $notification->created_at?->format('d.m.Y H:i'),
                                                        'status' => $notification->isAccepted() ? 'Zaakceptowane' : 'Oczekuje',
                                                    ]) }}">
                                                        <p class="font-black">{{ $notification->subject_label }}</p>
                                                        <p class="mt-1 text-sm text-slate-600">{{ $notification->description }}</p>
                                                        <p class="mt-2 text-xs text-slate-400">{{ $notification->created_at?->format('d.m.Y H:i') }} · {{ $notification->actor?->name ?? 'System' }}</p>
                                                    </button>
                                                    <div class="flex shrink-0 gap-1">
                                                        <form method="POST" action="{{ route('admin.notifications.accept', $notification) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button title="Akceptuj" class="rounded-lg border border-emerald-200 bg-white p-2 text-emerald-700 hover:bg-emerald-50"><i data-lucide="check" class="h-4 w-4"></i></button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button title="Oznacz jako przeczytane" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700 hover:bg-slate-50"><i data-lucide="mail-check" class="h-4 w-4"></i></button>
                                                        </form>
                                                        @if ($isAdmin || $notification->actor_id === $user->id)
                                                            <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Usunąć to powiadomienie?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button title="Usuń" class="rounded-lg border border-red-200 bg-white p-2 text-red-700 hover:bg-red-50"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="p-4 text-sm text-slate-500">Brak powiadomień.</p>
                                        @endforelse
                                    </div>

                                    <div x-show="previewNotification" x-cloak class="border-t border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-yellow-700">Podgląd zmiany</p>
                                                <h4 class="mt-1 font-black" x-text="previewNotification?.title"></h4>
                                            </div>
                                            <button type="button" class="rounded p-1 hover:bg-slate-200" @click="previewNotification = null"><i data-lucide="x" class="h-4 w-4"></i></button>
                                        </div>
                                        <p class="mt-2 text-sm text-slate-700" x-text="previewNotification?.description"></p>
                                        <p class="mt-3 text-xs text-slate-500"><span x-text="previewNotification?.actor"></span> · <span x-text="previewNotification?.date"></span> · <span x-text="previewNotification?.status"></span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative" @click.outside="accountOpen = false">
                                <button type="button" class="flex items-center gap-2 rounded-full px-2 py-1 transition hover:bg-white/10" @click="accountOpen = !accountOpen">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-900">
                                        <i data-lucide="user-round" class="h-5 w-5"></i>
                                    </span>
                                    <span class="font-semibold">{{ $isAdmin ? 'Admin' : 'Pracownik' }}</span>
                                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                </button>

                                <div x-show="accountOpen" x-cloak x-transition class="absolute right-0 z-50 mt-3 w-72 overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-950 shadow-2xl">
                                    <div class="border-b border-slate-200 p-4">
                                        <p class="font-black">{{ $user->name }}</p>
                                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                                    </div>
                                    <div class="p-3">
                                        <p class="px-2 text-xs font-bold uppercase tracking-wide text-slate-500">Konta zapisane na tym urządzeniu</p>
                                        <template x-for="account in savedAccounts" :key="account.email">
                                            <button type="button" class="mt-2 flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left hover:bg-yellow-50" @click="switchAccount(account)">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white" x-text="account.name.slice(0,2).toUpperCase()"></span>
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-bold" x-text="account.name"></span>
                                                    <span class="block truncate text-xs text-slate-500" x-text="account.email"></span>
                                                </span>
                                            </button>
                                        </template>
                                        <button type="button" class="mt-3 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold hover:bg-slate-50" @click="saveCurrentAccount">Zapisz bieżące konto na urządzeniu</button>
                                        <p class="mt-2 text-xs text-slate-500">Przełączenie prowadzi do logowania z wybranym adresem. Hasła nie są zapisywane.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="dashboard" class="space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-semibold">Nie udało się zapisać formularza.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <section class="{{ $activeSection === 'dashboard' ? '' : 'hidden' }}">
                        <h1 class="text-3xl font-black text-slate-950">Pulpit administratora</h1>
                        <p class="mt-1 text-sm text-slate-600">Witaj ponownie! Oto najważniejsze informacje z systemu.</p>
                    </section>

                    <section class="{{ $activeSection === 'dashboard' ? '' : 'hidden' }} overflow-hidden rounded-lg border border-yellow-300 bg-yellow-50 p-5 shadow-sm">
                        <div class="grid gap-5 xl:grid-cols-[1.2fr_repeat(4,1fr)]">
                            <div class="flex items-center gap-5">
                                <x-site-logo :url="$adminLogoUrl" alt="Logo panelu admina" image-class="h-20 w-20 shrink-0 rounded-full bg-white object-contain p-2 ring-1 ring-yellow-300" fallback-class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-yellow-300 text-xl font-black text-slate-950" />
                                <div>
                                    <h2 class="text-lg font-black">Witaj ponownie, {{ $isAdmin ? 'Administratorze' : 'Pracowniku' }}!</h2>
                                    <p class="mt-1 text-sm text-slate-600">Miło Cię znowu widzieć w panelu zarządzania ETB.</p>
                                </div>
                            </div>

                            @foreach ([
                                ['icon' => 'users', 'value' => $usersCount, 'label' => 'Użytkowników', 'hint' => 'Wszystkich kont'],
                                ['icon' => 'calendar-days', 'value' => $matchesCount, 'label' => 'Mecze', 'hint' => 'W tym sezonie'],
                                ['icon' => 'newspaper', 'value' => $publishedNewsCount, 'label' => 'Aktualności', 'hint' => 'Opublikowanych'],
                                ['icon' => 'trophy', 'value' => $threeXThreeTournaments->count(), 'label' => 'Turnieje 3x3', 'hint' => 'Zaplanowanych i zakończonych'],
                                ['icon' => 'graduation-cap', 'value' => $academyGroups->count(), 'label' => 'Akademia', 'hint' => 'Aktywnych i ukrytych sekcji'],
                                ['icon' => 'handshake', 'value' => $sponsors->count(), 'label' => 'Sponsorzy', 'hint' => 'W bazie partnerów'],
                            ] as $stat)
                                <article class="rounded-lg bg-white/80 p-5 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <i data-lucide="{{ $stat['icon'] }}" class="mt-1 h-7 w-7 text-yellow-500"></i>
                                        <div>
                                            <p class="text-2xl font-black">{{ $stat['value'] }}</p>
                                            <p class="text-sm font-bold">{{ $stat['label'] }}</p>
                                            <p class="mt-2 text-sm text-slate-500">{{ $stat['hint'] }}</p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    @if ($isAdmin)
                        @php($managedLogos = [
                            [
                                'id' => 'club',
                                'title' => 'Logo ETB Łódź',
                                'description' => 'Logo widoczne po lewej stronie głównego nagłówka strony.',
                                'url' => $clubLogoUrl,
                                'path' => $clubLogoPath,
                                'fallback' => 'ETB',
                            ],
                            [
                                'id' => 'title-sponsor',
                                'title' => 'Logo sponsora tytularnego',
                                'description' => 'Logo wyświetlane po prawej stronie głównego nagłówka strony.',
                                'url' => $titleSponsorLogoUrl,
                                'path' => $titleSponsorLogoPath,
                                'fallback' => 'Sponsor tytularny',
                                'link' => $titleSponsorUrl,
                            ],
                            [
                                'id' => 'academy',
                                'title' => 'Logo akademii',
                                'description' => 'Logo widoczne obok tytułu na stronie akademii.',
                                'url' => $academyLogoUrl,
                                'path' => $academyLogoPath,
                                'fallback' => 'Akademia',
                            ],
                            [
                                'id' => 'shop',
                                'title' => 'Logo sklepu',
                                'description' => 'Logo widoczne w nagłówku strony sklepu.',
                                'url' => $shopLogoUrl,
                                'path' => $shopLogoPath,
                                'fallback' => 'Sklep',
                            ],
                            [
                                'id' => 'tickets',
                                'title' => 'Logo biletów',
                                'description' => 'Logo widoczne w nagłówku strony biletów.',
                                'url' => $ticketsLogoUrl,
                                'path' => $ticketsLogoPath,
                                'fallback' => 'Bilety',
                            ],
                            [
                                'id' => 'admin',
                                'title' => 'Logo panelu admina',
                                'description' => 'Logo widoczne w bocznym panelu admina i na pulpicie.',
                                'url' => $adminLogoUrl,
                                'path' => $adminLogoPath,
                                'fallback' => 'ETB',
                            ],
                            [
                                'id' => 'auth',
                                'title' => 'Logo panelu logowania',
                                'description' => 'Logo widoczne w dużym polu po lewej stronie ekranu logowania.',
                                'url' => $authLogoUrl,
                                'path' => $authLogoPath,
                                'fallback' => 'Logo logowania',
                            ],
                        ])
                        <section class="{{ $activeSection === 'dashboard' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-5">
                                <h2 class="text-xl font-black">Logotypy strony</h2>
                                <p class="mt-1 max-w-3xl text-sm text-slate-600">Każde wskazane miejsce ma osobny plik: nagłówek ETB Łódź, sponsor tytularny, akademia, sklep, bilety, panel admina oraz panel logowania.</p>
                            </div>
                            <div class="grid gap-4 xl:grid-cols-3 2xl:grid-cols-4">
                                @foreach ($managedLogos as $managedLogo)
                                    <article class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-20 w-24 shrink-0 items-center justify-center rounded-lg bg-white p-2 ring-1 ring-slate-200">
                                                <x-site-logo :url="$managedLogo['url']" :alt="$managedLogo['title']" image-class="max-h-full max-w-full object-contain" :fallback="$managedLogo['fallback']" fallback-class="text-center text-[10px] font-black uppercase tracking-wide text-slate-500" />
                                            </div>
                                            <div class="min-w-0">
                                                <h3 class="font-black">{{ $managedLogo['title'] }}</h3>
                                                <p class="mt-1 text-sm text-slate-600">{{ $managedLogo['description'] }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <form method="POST" action="{{ route('admin.site-logos.update', $managedLogo['id']) }}" enctype="multipart/form-data" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <input name="logo" type="file" accept="image/*" @required(! $managedLogo['path']) class="w-full rounded border border-slate-300 bg-white text-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                                                @if ($managedLogo['id'] === 'title-sponsor')
                                                    <input name="url" type="url" value="{{ old('url', $managedLogo['link'] ?? '') }}" placeholder="Link sponsora" class="w-full rounded border border-slate-300 bg-white text-sm">
                                                @endif
                                                <button class="w-full rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz logo</button>
                                            </form>
                                            @if ($managedLogo['path'])
                                                <form method="POST" action="{{ route('admin.site-logos.destroy', $managedLogo['id']) }}" onsubmit="return confirm('Usunąć ten logotyp?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-black text-red-700 hover:bg-red-100">Usuń logo</button>
                                                </form>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($isAdmin)
                        <section id="users" class="{{ $activeSection === 'users' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="adminUserSearch(@js(route('admin.users.search')), {
                            role: @js($userRoleFilter),
                            marketingConsent: @js($marketingConsentFilter),
                        })">
                            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-black">Zarządzanie użytkownikami</h2>
                                    <p class="text-sm text-slate-600">Ostatnio dodani użytkownicy w systemie.</p>
                                </div>
                                <div class="w-full space-y-3 sm:w-[34rem]">
                                    <form method="GET" action="{{ route('profile.edit') }}" class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                                        <input type="hidden" name="section" value="users">
                                        <select name="user_role" class="rounded-lg border-slate-300 text-sm">
                                            <option value="all" @selected($userRoleFilter === 'all')>Wszystkie role</option>
                                            @foreach ($availableRoles as $role)
                                                <option value="{{ $role }}" @selected($userRoleFilter === $role)>{{ $roleLabels[$role] ?? $role }}</option>
                                            @endforeach
                                        </select>
                                        <select name="marketing_consent" class="rounded-lg border-slate-300 text-sm">
                                            <option value="all" @selected($marketingConsentFilter === 'all')>Wszystkie zgody</option>
                                            <option value="yes" @selected($marketingConsentFilter === 'yes')>Zgoda marketingowa</option>
                                            <option value="no" @selected($marketingConsentFilter === 'no')>Brak zgody</option>
                                        </select>
                                        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-black hover:bg-yellow-50">Filtruj</button>
                                    </form>

                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <div class="relative flex-1">
                                            <input type="search" x-model="query" @input.debounce.350ms="search" placeholder="Szukaj po nazwie lub e-mailu" class="w-full rounded-lg border-slate-300 pr-10 text-sm">
                                            <div x-show="results.length" x-cloak class="absolute right-0 z-30 mt-2 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                                                <template x-for="user in results" :key="user.id">
                                                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-yellow-50" @click="focusUser(user)">
                                                        <span class="block font-semibold" x-text="user.name"></span>
                                                        <span class="block text-xs text-slate-500" x-text="user.email"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.users.emails.export', ['user_role' => $userRoleFilter, 'marketing_consent' => $marketingConsentFilter]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-950 px-4 py-2 text-sm font-black text-white hover:bg-slate-800">
                                            <i data-lucide="download" class="h-4 w-4"></i>
                                            Pobierz e-maile
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-scroll-list overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                        <tr>
                                            <th class="px-3 py-3">Użytkownik</th>
                                            <th class="px-3 py-3">E-mail</th>
                                            <th class="px-3 py-3">Rola</th>
                                            <th class="px-3 py-3">Marketing</th>
                                            <th class="px-3 py-3">Akcje</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($users as $managedUser)
                                            <tr id="managed-user-{{ $managedUser->id }}" data-admin-search class="etb-admin-card">
                                                <td class="px-3 py-3 font-semibold">
                                                    <span class="mr-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-xs font-black text-white">{{ str($managedUser->name)->substr(0, 2)->upper() }}</span>
                                                    {{ $managedUser->name }}
                                                </td>
                                                <td class="px-3 py-3 text-slate-600">{{ $managedUser->email }}</td>
                                                <td class="px-3 py-3">
                                                    <form method="POST" action="{{ route('admin.users.role.update', $managedUser) }}" class="flex flex-wrap items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="role" class="rounded-lg border-slate-300 text-sm">
                                                            @foreach ($availableRoles as $role)
                                                                <option value="{{ $role }}" @selected($managedUser->role === $role)>{{ $roleLabels[$role] ?? $role }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button class="rounded-lg bg-yellow-400 px-3 py-2 text-xs font-black text-black hover:bg-yellow-300">Zapisz</button>
                                                    </form>
                                                </td>
                                                <td class="px-3 py-3">
                                                    @if ($managedUser->role === \App\Models\User::ROLE_FAN)
                                                        <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $managedUser->fanProfile?->marketing_email_consent ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                            {{ $managedUser->fanProfile?->marketing_email_consent ? 'Tak' : 'Nie' }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-400">Nie dotyczy</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3">
                                                    <a href="#managed-user-{{ $managedUser->id }}" class="inline-flex rounded-lg border border-slate-200 bg-white p-2 hover:bg-yellow-50"><i data-lucide="eye" class="h-4 w-4"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5">{{ $users->links() }}</div>
                        </section>
                    @endif

                    <section id="matches" class="{{ $activeSection === 'matches' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="{ sectionQuery: '' }">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Mecze</h2>
                                <p class="text-sm text-slate-600">Nadchodzące i ostatnio zakończone mecze.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <input type="search" x-model="sectionQuery" placeholder="Szukaj meczu" class="rounded-lg border-slate-300 text-sm">
                                <select x-model="matchFilter" class="rounded-lg border-slate-300 text-sm">
                                    <option value="all">Wszystkie mecze</option>
                                    <option value="upcoming">Nadchodzące</option>
                                    <option value="finished">Zakończone</option>
                                </select>
                                <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = 'match-create'">Dodaj mecz</button>
                            </div>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-2">
                            <div x-show="matchFilter === 'all' || matchFilter === 'upcoming'">
                                <h3 class="mb-3 font-black">Nadchodzące mecze</h3>
                                <div class="admin-scroll-list space-y-3">
                                    @forelse ($upcomingMatches as $match)
                                        @include('profile.partials.match-card', ['match' => $match])
                                    @empty
                                        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak nadchodzących meczów.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div x-show="matchFilter === 'all' || matchFilter === 'finished'">
                                <h3 class="mb-3 font-black">Ostatnio zakończone mecze</h3>
                                <div class="admin-scroll-list space-y-3">
                                    @forelse ($finishedMatches as $match)
                                        @include('profile.partials.match-card', ['match' => $match])
                                    @empty
                                        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak zakończonych meczów.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="club-content" class="{{ $activeSection === 'club-content' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Klub</h2>
                                <p class="text-sm text-slate-600">Edytuj treści i zdjęcia widoczne w zakładce Klub oraz na osobnych podstronach.</p>
                            </div>
                            <a href="{{ route('club') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-800 hover:bg-yellow-50">
                                <i data-lucide="external-link" class="h-4 w-4"></i>
                                Podgląd
                            </a>
                        </div>

                        <div class="space-y-4">
                            @foreach ($clubSections as $clubSection)
                                <article data-admin-search class="etb-admin-card rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <form method="POST" action="{{ route('admin.club-sections.update', $clubSection->slug) }}" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 class="font-black">{{ $clubSection->title }}</h3>
                                                <a href="{{ route('club.'.$clubSection->slug) }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-yellow-700 hover:text-yellow-900">Otwórz podstronę</a>
                                            </div>
                                            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">
                                                <i data-lucide="save" class="h-4 w-4"></i>
                                                Zapisz sekcję
                                            </button>
                                        </div>

                                        <div>
                                            <label for="club-section-body-{{ $clubSection->slug }}" class="text-sm font-black text-slate-700">Pole tekstowe</label>
                                            <textarea id="club-section-body-{{ $clubSection->slug }}" name="body" rows="7" class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="Wpisz treść sekcji...">{{ old('body', $clubSection->body) }}</textarea>
                                        </div>

                                        <div>
                                            <label for="club-section-photos-{{ $clubSection->slug }}" class="text-sm font-black text-slate-700">Zdjęcia</label>
                                            <input id="club-section-photos-{{ $clubSection->slug }}" type="file" name="photos[]" multiple accept="image/*" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                            <p class="mt-1 text-xs text-slate-500">Możesz dodać jedno lub kilka zdjęć naraz.</p>
                                        </div>
                                    </form>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        @forelse ($clubSection->images as $image)
                                            <div class="rounded-lg border border-slate-200 bg-white p-2">
                                                <img src="{{ \App\Support\MediaStorage::url($image->image_path) }}" alt="{{ $image->alt ?? $clubSection->title }}" class="h-32 w-full rounded object-cover">
                                                <form method="POST" action="{{ route('admin.club-sections.images.update', [$clubSection, $image]) }}" class="mt-2 space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label for="club-image-caption-{{ $image->id }}" class="text-xs font-black text-slate-600">Podpis / źródło zdjęcia</label>
                                                    <textarea id="club-image-caption-{{ $image->id }}" name="caption" rows="2" class="w-full rounded-lg border-slate-300 text-xs" placeholder="np. Fot. Jan Kowalski albo link do źródła">{{ old('caption', $image->caption) }}</textarea>
                                                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:bg-yellow-50">
                                                        <i data-lucide="save" class="h-4 w-4"></i>
                                                        Zapisz podpis
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.club-sections.images.destroy', [$clubSection, $image]) }}" class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 hover:bg-red-100">
                                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                        Usuń zdjęcie
                                                    </button>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500">
                                                Brak zdjęć w tej sekcji.
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section id="league-table" class="{{ $activeSection === 'league-table' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Tabela ŁZKosz</h2>
                                <p class="text-sm text-slate-600">Pobierz tabelę 3 Ligi Mężczyzn i przypisz logotypy do drużyn.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.league-table.sync') }}">
                                @csrf
                                <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Pobierz tabelę z ŁZKosz</button>
                            </form>
                        </div>

                        @if ($defaultHomeLogo)
                            <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-950">
                                Domyślne logo ETB jest ustawione.
                                <img src="{{ \App\Support\MediaStorage::url($defaultHomeLogo) }}" alt="Logo ETB" class="mt-2 h-12 w-12 rounded bg-white object-contain p-1 ring-1 ring-yellow-200">
                            </div>
                        @endif

                        <div class="admin-scroll-list space-y-3">
                            @forelse ($leagueStandings as $standing)
                                <article data-admin-search class="etb-admin-card rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-center gap-4">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-950 text-sm font-black text-white">{{ $standing->position }}</span>
                                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-white p-2 ring-1 ring-slate-200">
                                                @if ($standing->opponent->logo_path)
                                                    <img src="{{ \App\Support\MediaStorage::url($standing->opponent->logo_path) }}" alt="{{ $standing->opponent->name }}" class="max-h-full max-w-full object-contain">
                                                @else
                                                    <span class="text-xs font-black text-slate-400">LOGO</span>
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="font-black">{{ $standing->opponent->name }}</h3>
                                                <p class="text-sm text-slate-600">{{ $standing->points }} pkt · {{ $standing->wins }}-{{ $standing->losses }} · kosze {{ $standing->points_for }}-{{ $standing->points_against }}</p>
                                                @if ($standing->source_team_url)
                                                    <a href="{{ $standing->source_team_url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-yellow-700 hover:text-yellow-900">Profil w ŁZKosz</a>
                                                @endif
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.opponents.update', $standing->opponent) }}" enctype="multipart/form-data" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input name="logo" type="file" accept="image/*" class="w-full rounded border border-slate-300 bg-white text-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold sm:w-64">
                                            <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold hover:bg-yellow-50">Zapisz logo</button>
                                        </form>
                                    </div>
                                </article>
                            @empty
                                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Tabela nie została jeszcze pobrana. Kliknij „Pobierz tabelę z ŁZKosz”.</p>
                            @endforelse
                        </div>
                    </section>

                    <section id="news" class="{{ $activeSection === 'news' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="newsLightbox()">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Aktualności</h2>
                                <p class="text-sm text-slate-600">Publikuj od razu albo ustaw datę przyszłej publikacji.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <input type="search" x-model="sectionQuery" placeholder="Szukaj aktualności" class="rounded-lg border-slate-300 text-sm">
                                <select x-model="newsFilter" class="rounded-lg border-slate-300 text-sm">
                                    <option value="all">Wszystkie</option>
                                    <option value="published">Opublikowane</option>
                                    <option value="scheduled">Zaplanowane</option>
                                </select>
                                <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = 'news-create'">Dodaj aktualność</button>
                            </div>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-2">
                            @foreach ([['items' => $publishedNews, 'type' => 'published', 'title' => 'Opublikowane'], ['items' => $scheduledNews, 'type' => 'scheduled', 'title' => 'Zaplanowane']] as $group)
                                <div x-show="newsFilter === 'all' || newsFilter === '{{ $group['type'] }}'">
                                    <h3 class="mb-3 font-black">{{ $group['title'] }}</h3>
                                    <div class="admin-scroll-list space-y-3">
                                        @forelse ($group['items'] as $item)
                                            <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="etb-admin-card rounded-lg border border-slate-200 bg-slate-50 p-4">
                                                <div class="flex gap-4">
                                                    @php($previewImage = $item->previewImagePath())
                                                    @if ($previewImage)
                                                        <img src="{{ \App\Support\MediaStorage::url($previewImage) }}" alt="{{ $item->title }}" class="h-20 w-24 rounded-lg object-cover">
                                                    @else
                                                        <div class="flex h-20 w-24 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-black uppercase text-slate-500">{{ $item->type === \App\Models\News::TYPE_VIDEO ? 'Wideo' : 'ETB' }}</div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <h4 class="font-black">{{ $item->title }}</h4>
                                                        <p class="text-sm text-slate-600">{{ $item->typeLabel() }} · {{ $item->publish_at?->format('d.m.Y H:i') ?? 'Publikacja natychmiastowa' }}</p>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <a href="{{ route('admin.news.preview', $item) }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50">Podgląd</a>
                                                            @if ($group['type'] === 'scheduled')
                                                                <button type="button" class="rounded-lg bg-yellow-400 px-3 py-1.5 text-sm font-black text-black hover:bg-yellow-300" @click="publishAction = '{{ route('admin.news.publish', $item) }}'; openModal = 'news-publish-confirm'">Opublikuj</button>
                                                            @endif
                                                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'news-edit-{{ $item->id }}'">Edytuj</button>
                                                            <form method="POST" action="{{ route('news.destroy', $item) }}" onsubmit="return confirm('Czy na pewno usunąć tę aktualność?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak aktualności.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    @foreach ([
                        ['id' => 'players', 'title' => 'Zawodnicy', 'subtitle' => 'Pełna edycja kart zawodników oraz zdjęć.', 'button' => 'Dodaj zawodnika', 'modal' => 'player-create', 'items' => $players, 'type' => 'player'],
                        ['id' => 'staff', 'title' => 'Sztab szkoleniowy', 'subtitle' => 'Karty publicznej sekcji sztabu.', 'button' => 'Dodaj osobę', 'modal' => 'staff-create', 'items' => $staff, 'type' => 'staff'],
                        ['id' => 'three-x-three', 'title' => 'Drużyna 3x3', 'subtitle' => 'Osobne karty zawodników i trenera 3x3.', 'button' => 'Dodaj osobę', 'modal' => '3x3-member-create', 'items' => $threeXThreeMembers, 'type' => 'member'],
                        ['id' => 'tournaments', 'title' => 'Turnieje 3x3', 'subtitle' => 'Dodawanie, edycja, usuwanie i filtrowanie turniejów.', 'button' => 'Dodaj turniej', 'modal' => '3x3-tournament-create', 'items' => $threeXThreeTournaments, 'type' => 'tournament'],
                    ] as $section)
                        <section id="{{ $section['id'] }}" class="{{ $activeSection === $section['id'] ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="{ sectionQuery: '' }">
                            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-black">{{ $section['title'] }}</h2>
                                    <p class="text-sm text-slate-600">{{ $section['subtitle'] }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <input type="search" x-model="sectionQuery" placeholder="Szukaj" class="rounded-lg border-slate-300 text-sm">
                                    <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = '{{ $section['modal'] }}'">{{ $section['button'] }}</button>
                                </div>
                            </div>

                            <div class="admin-scroll-list grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @forelse ($section['items'] as $item)
                                    <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="etb-admin-card rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        @if ($section['type'] === 'player')
                                            <div class="flex gap-4">
                                                @if ($item->photo_path)
                                                    <img src="{{ \App\Support\MediaStorage::url($item->photo_path) }}" alt="{{ $item->full_name }}" class="h-24 w-20 rounded-lg object-cover">
                                                @else
                                                    <div class="flex h-24 w-20 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-500">ETB</div>
                                                @endif
                                                <div>
                                                    <p class="text-2xl font-black">#{{ $item->number }}</p>
                                                    <h3 class="font-black">{{ $item->full_name }}</h3>
                                                    <p class="text-sm text-slate-600">{{ $item->positionLabel() }}</p>
                                                    <p class="text-sm text-slate-600">{{ $item->height }} cm · {{ $item->weight }} kg</p>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <a href="{{ route('players.show', $item) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50">Szczegóły</a>
                                                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'player-edit-{{ $item->id }}'">Edytuj</button>
                                                <form method="POST" action="{{ route('players.destroy', $item) }}" onsubmit="return confirm('Czy na pewno usunąć tego zawodnika?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                                                </form>
                                            </div>
                                        @elseif ($section['type'] === 'tournament')
                                            <h3 class="font-black">{{ $item->name }}</h3>
                                            <p class="mt-1 text-xs font-bold uppercase text-yellow-700">{{ $item->type === ThreeXThreeTournament::TYPE_ORGANIZED ? 'Organizowany przez ETB' : 'Turniej, w którym gramy' }}</p>
                                            @if ($item->type === ThreeXThreeTournament::TYPE_ORGANIZED)
                                                <p class="mt-2 text-sm text-slate-600">Drużyny: {{ $item->teams->count() }} / Grupy: {{ $item->groups->count() }} / Mecze: {{ $item->matches->count() }}</p>
                                            @endif
                                            <p class="text-sm text-slate-600">{{ $item->date?->format('d.m.Y') }} · {{ $item->location }}</p>
                                            <p class="mt-1 text-xs font-bold uppercase text-slate-500">{{ $item->status === ThreeXThreeTournament::STATUS_FINISHED ? 'Zakończony' : 'Nadchodzący' }}</p>
                                            @if ($item->categories->isNotEmpty())
                                                <div class="mt-3 flex flex-wrap gap-1.5">
                                                    @foreach ($item->categories as $category)
                                                        <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-bold text-yellow-900">{{ $category->category->label() }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <a href="{{ route('three-x-three.tournaments.show', $item) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50">Podgląd</a>
                                                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = '3x3-tournament-edit-{{ $item->id }}'">Edytuj</button>
                                                @if ($item->type === ThreeXThreeTournament::TYPE_ORGANIZED)
                                                    <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = '3x3-tournament-manage-{{ $item->id }}'">Przebieg</button>
                                                @endif
                                                <form method="POST" action="{{ route('tournaments.destroy', $item) }}" onsubmit="return confirm('Czy na pewno usunąć ten turniej?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="flex gap-4">
                                                @if ($item->photo_path)
                                                    <img src="{{ \App\Support\MediaStorage::url($item->photo_path) }}" alt="{{ $item->name }}" class="h-24 w-20 rounded-lg object-cover">
                                                @else
                                                    <div class="flex h-24 w-20 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-500">ETB</div>
                                                @endif
                                                <div>
                                                    <h3 class="font-black">{{ $item->name }}</h3>
                                                    <p class="text-sm text-slate-600">{{ $item->role }}</p>
                                                    @if (($section['type'] === 'member') && $item->is_coach)
                                                        <p class="mt-1 text-xs font-bold uppercase text-yellow-700">Trener</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = '{{ $section['type'] === 'staff' ? 'staff-edit' : '3x3-member-edit' }}-{{ $item->id }}'">Edytuj</button>
                                                <form method="POST" action="{{ $section['type'] === 'staff' ? route('staff.destroy', $item) : route('members.destroy', $item) }}" onsubmit="return confirm('Czy na pewno usunąć ten rekord?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                                                </form>
                                            </div>
                                        @endif
                                    </article>
                                @empty
                                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak rekordów w tej sekcji.</p>
                                @endforelse
                            </div>
                        </section>
                    @endforeach

                    @include('profile.partials.academy-admin')

                    @include('profile.partials.faq-admin')

                    <section id="sponsors" class="{{ $activeSection === 'sponsors' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Sponsorzy</h2>
                                <p class="text-sm text-slate-600">Logo, linki i kategorie partnerów widoczne w stopce każdej strony.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'sponsor-category-create'">Dodaj kategorię</button>
                                <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = 'sponsor-create'">Dodaj sponsora</button>
                            </div>
                        </div>

                        <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h3 class="font-black">Kategorie sponsorów</h3>
                                    <p class="text-sm text-slate-600">Podpis kategorii pokaże się publicznie tylko wtedy, gdy ma aktywnego sponsora.</p>
                                </div>
                                <p class="text-xs font-bold uppercase text-slate-500">{{ $sponsorCategories->count() }} kategorii</p>
                            </div>
                            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                @foreach ($sponsorCategories as $category)
                                    <article class="rounded-lg border border-slate-200 bg-white p-3">
                                        <form method="POST" action="{{ route('sponsor-categories.update', $category) }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_6rem_auto] sm:items-end">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500" for="sponsor-category-name-{{ $category->id }}">Nazwa</label>
                                                <input id="sponsor-category-name-{{ $category->id }}" name="name" value="{{ old('name', $category->name) }}" required class="w-full rounded-lg border-slate-300 text-sm">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500" for="sponsor-category-sort-{{ $category->id }}">Kolejność</label>
                                                <input id="sponsor-category-sort-{{ $category->id }}" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="w-full rounded-lg border-slate-300 text-sm">
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <label class="flex items-center gap-2 text-xs font-bold uppercase text-slate-600">
                                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
                                                    Aktywna
                                                </label>
                                                <button class="rounded-lg bg-yellow-400 px-3 py-2 text-xs font-black text-black hover:bg-yellow-300">Zapisz</button>
                                            </div>
                                        </form>
                                        <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
                                            <span>Sponsorzy: {{ $category->sponsors_count }}</span>
                                            @if ($category->sponsors_count === 0)
                                                <form method="POST" action="{{ route('sponsor-categories.destroy', $category) }}" onsubmit="return confirm('Czy na pewno usunąć tę kategorię?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-bold text-red-700 hover:text-red-900">Usuń kategorię</button>
                                                </form>
                                            @else
                                                <span class="font-bold text-slate-500">Najpierw przenieś sponsorów, aby usunąć</span>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="admin-scroll-list grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse ($sponsors as $sponsor)
                                <article data-admin-search class="etb-admin-card rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex gap-4">
                                        <div class="flex h-20 w-28 shrink-0 items-center justify-center rounded-lg bg-white p-3">
                                            <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="max-h-14 w-full object-contain">
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="truncate font-black">{{ $sponsor->name }}</h3>
                                            <p class="text-sm font-semibold text-yellow-700">{{ $sponsor->typeLabel() }}</p>
                                            <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="block truncate text-sm text-slate-600 hover:text-yellow-700">{{ $sponsor->url }}</a>
                                            <p class="mt-1 text-xs font-bold uppercase {{ $sponsor->is_active ? 'text-emerald-700' : 'text-slate-500' }}">{{ $sponsor->is_active ? 'Widoczny' : 'Ukryty' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'sponsor-edit-{{ $sponsor->id }}'">Edytuj</button>
                                        <form method="POST" action="{{ route('sponsors.destroy', $sponsor) }}" onsubmit="return confirm('Czy na pewno usunąć tego sponsora?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                                        </form>
                                    </div>
                                </article>
                            @empty
                                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600 md:col-span-2 xl:col-span-3">Brak sponsorów. Dodaj pierwszego partnera, aby pojawił się w stopce strony.</p>
                            @endforelse
                        </div>
                    </section>

                    <section id="notifications-history" class="{{ $activeSection === 'notifications-history' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Historia zmian</h2>
                                <p class="text-sm text-slate-600">Wszystkie zapisane operacje w systemie.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                                @csrf
                                @method('PATCH')
                                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-950 px-4 py-2 text-sm font-black text-white hover:bg-slate-800">
                                    <i data-lucide="mail-check" class="h-4 w-4"></i>
                                    Oznacz wszystkie jako przeczytane
                                </button>
                            </form>
                        </div>

                        <div class="admin-scroll-list overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-3 py-3">Data</th>
                                        <th class="px-3 py-3">Zmiana</th>
                                        <th class="px-3 py-3">Opis</th>
                                        <th class="px-3 py-3">Autor</th>
                                        <th class="px-3 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($notificationHistory as $notification)
                                        <tr data-admin-search class="align-top">
                                            <td class="whitespace-nowrap px-3 py-3 text-slate-500">{{ $notification->created_at?->format('d.m.Y H:i') }}</td>
                                            <td class="px-3 py-3">
                                                <p class="font-black text-slate-950">{{ $notification->subject_label }}</p>
                                                <span class="mt-1 inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-black text-yellow-900">
                                                    {{ $notificationActionLabels[$notification->action] ?? 'Zmieniono' }}
                                                </span>
                                            </td>
                                            <td class="max-w-xl px-3 py-3 text-slate-600">{{ $notification->description }}</td>
                                            <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-700">{{ $notification->actor?->name ?? 'System' }}</td>
                                            <td class="px-3 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $notification->isRead() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                                        {{ $notification->isRead() ? 'Przeczytane' : 'Nowe' }}
                                                    </span>
                                                    @if ($notification->isAccepted())
                                                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-black text-blue-800">
                                                            Zaakceptowane
                                                        </span>
                                                    @endif
                                                    @if ($notification->trashed())
                                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-black text-red-800">
                                                            Usunięte z listy
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">Brak zapisanych zmian.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($notificationHistory->hasPages())
                            <div class="mt-5">{{ $notificationHistory->links() }}</div>
                        @endif
                    </section>

                    <section id="shop" class="{{ $activeSection === 'shop' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black">Sklep i zarządzanie</h2>
                                <p class="text-sm text-slate-600">Produkty, zamówienia, kategorie oraz filtry sklepu w jednym miejscu.</p>
                            </div>
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-black hover:bg-yellow-50">
                                <i data-lucide="external-link" class="h-4 w-4"></i>
                                Zobacz sklep
                            </a>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            @foreach ([
                                ['icon' => 'shopping-cart', 'value' => $shopStats['orders'], 'label' => 'Zamówienia', 'route' => route('admin.orders.index')],
                                ['icon' => 'package', 'value' => $shopStats['products'], 'label' => 'Produkty', 'route' => route('admin.products.index')],
                                ['icon' => 'badge-check', 'value' => $shopStats['publishedProducts'], 'label' => 'Opublikowane', 'route' => route('admin.products.index')],
                                ['icon' => 'tags', 'value' => $shopStats['categories'], 'label' => 'Kategorie', 'route' => route('admin.categories.index')],
                                ['icon' => 'sliders-horizontal', 'value' => $shopStats['filterGroups'], 'label' => 'Grupy filtrów', 'route' => route('admin.product-filters.index')],
                            ] as $stat)
                                <a href="{{ $stat['route'] }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-yellow-400 hover:bg-yellow-50">
                                    <i data-lucide="{{ $stat['icon'] }}" class="h-6 w-6 text-yellow-600"></i>
                                    <p class="mt-3 text-2xl font-black">{{ $stat['value'] }}</p>
                                    <p class="text-sm font-bold text-slate-600">{{ $stat['label'] }}</p>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-4">
                            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-yellow-400 px-4 py-3 text-sm font-black text-black hover:bg-yellow-300"><i data-lucide="plus" class="h-4 w-4"></i>Dodaj produkt</a>
                            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-black hover:bg-slate-50"><i data-lucide="folder-plus" class="h-4 w-4"></i>Dodaj kategorię</a>
                            <a href="{{ route('admin.product-filters.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-black hover:bg-slate-50"><i data-lucide="sliders-horizontal" class="h-4 w-4"></i>Edytuj filtry</a>
                            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-black hover:bg-slate-50"><i data-lucide="clipboard-list" class="h-4 w-4"></i>Obsłuż zamówienia</a>
                        </div>
                    </section>

                    <section id="account" class="{{ $activeSection === 'account' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-xl font-black">Profil i konto</h2>
                        <div class="grid gap-6 xl:grid-cols-2">
                            @include('profile.partials.update-profile-information-form')
                            @include('profile.partials.update-password-form')
                        </div>
                    </section>
                </main>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-6 text-slate-950 shadow-sm">
                <h1 class="text-2xl font-black">Twoje konto</h1>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    @include('profile.partials.update-profile-information-form')
                    @include('profile.partials.update-password-form')
                </div>
            </section>
        </div>
    @endif

    @if ($isPanelUser)
        <div x-show="openModal === 'match-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj mecz</h4>
                <form method="POST" action="{{ route('matches.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.match-form-fields')
                    <div class="flex justify-between gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                        <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Dodaj mecz</button>
                    </div>
                </form>
            </div>
        </div>

        @foreach ($upcomingMatches->concat($finishedMatches) as $match)
            <div x-show="openModal === 'match-edit-{{ $match->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj mecz</h4>
                    <form method="POST" action="{{ route('matches.update', $match) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.match-form-fields', ['match' => $match])
                        <div class="flex justify-between gap-3 pt-2">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                            <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <div x-show="openModal === 'news-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj aktualność</h4>
                <form method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.news-form-fields')
                    <div class="flex justify-between">
                        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                        <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="openModal === 'news-publish-confirm'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="text-lg font-black">Publikacja artykułu</h4>
                <p class="mt-3 text-sm text-slate-600">Czy na pewno chcesz opublikować ten artykuł?</p>
                <form method="POST" :action="publishAction" class="mt-6 flex justify-between gap-3">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null; publishAction = null">Anuluj</button>
                    <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Opublikuj</button>
                </form>
            </div>
        </div>

        @foreach ($publishedNews->concat($scheduledNews) as $item)
            <div x-show="openModal === 'news-edit-{{ $item->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj aktualność</h4>
                    <form method="POST" action="{{ route('news.update', $item) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.news-form-fields', ['item' => $item])
                        <div class="flex justify-between">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                            <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <div x-show="openModal === 'player-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj zawodnika</h4>
                <form method="POST" action="{{ route('players.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.player-form-fields')
                    <div class="flex justify-between">
                        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                        <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button>
                    </div>
                </form>
            </div>
        </div>

        @foreach ($players as $player)
            <div x-show="openModal === 'player-edit-{{ $player->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj zawodnika</h4>
                    <form method="POST" action="{{ route('players.update', $player) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.player-form-fields', ['player' => $player])
                        <div class="flex justify-between">
                            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button>
                            <button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <div x-show="openModal === 'staff-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj osobę do sztabu</h4>
                <form method="POST" action="{{ route('staff.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.media-card-form-fields')
                    <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
                </form>
            </div>
        </div>

        @foreach ($staff as $person)
            <div x-show="openModal === 'staff-edit-{{ $person->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj osobę w sztabie</h4>
                    <form method="POST" action="{{ route('staff.update', $person) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.media-card-form-fields', ['item' => $person])
                        <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
                    </form>
                </div>
            </div>
        @endforeach

        <div x-show="openModal === '3x3-member-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj osobę do drużyny 3x3</h4>
                <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.media-card-form-fields', ['withCoach' => true])
                    <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
                </form>
            </div>
        </div>

        @foreach ($threeXThreeMembers as $member)
            <div x-show="openModal === '3x3-member-edit-{{ $member->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj osobę w drużynie 3x3</h4>
                    <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.media-card-form-fields', ['item' => $member, 'withCoach' => true])
                        <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
                    </form>
                </div>
            </div>
        @endforeach

        <div x-show="openModal === '3x3-tournament-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj turniej 3x3</h4>
                <form method="POST" action="{{ route('tournaments.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.tournament-form-fields')
                    <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
                </form>
            </div>
        </div>

        @foreach ($threeXThreeTournaments as $tournament)
            <div x-show="openModal === '3x3-tournament-edit-{{ $tournament->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj turniej 3x3</h4>
                    <form method="POST" action="{{ route('tournaments.update', $tournament) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.tournament-form-fields', ['tournament' => $tournament])
                        <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
                    </form>
                </div>
            </div>
            @if ($tournament->type === ThreeXThreeTournament::TYPE_ORGANIZED)
                <div x-show="openModal === '3x3-tournament-manage-{{ $tournament->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="max-h-[92vh] w-full max-w-7xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-yellow-700">Faza grupowa i drabinka</p>
                                <h4 class="mt-1 text-2xl font-black">Przebieg turnieju: {{ $tournament->name }}</h4>
                                <p class="mt-1 text-sm text-slate-600">Punkty FIBA: wygrana 2, porażka 1, walkower 0. Tabela sortuje po punktach, zwycięstwach, bilansie i koszach zdobytych.</p>
                            </div>
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50" @click="openModal = null">Zamknij</button>
                        </div>

                        <div class="mt-5 grid gap-4 xl:grid-cols-[22rem_1fr]">
                            <aside class="space-y-4">
                                <section class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <h5 class="font-black">Losowanie automatyczne</h5>
                                    <form method="POST" action="{{ route('admin.3x3.tournaments.draw', $tournament) }}" class="mt-3 space-y-3" onsubmit="return confirm('Losowanie usunie obecne grupy i niewynikowe mecze tego turnieju. Kontynuować?')">
                                        @csrf
                                        <div class="grid grid-cols-3 gap-2">
                                            <label class="block">
                                                <span class="text-xs font-bold text-slate-600">Grupy</span>
                                                <input name="groups_count" type="number" min="1" max="12" value="{{ max(1, $tournament->groups->count() ?: 4) }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-bold text-slate-600">Drużyn</span>
                                                <input name="teams_per_group" type="number" min="2" max="8" value="4" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-bold text-slate-600">Awans</span>
                                                <input name="qualifiers_per_group" type="number" min="1" max="4" value="2" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            </label>
                                        </div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                            <input name="generate_group_matches" type="checkbox" value="1" checked class="rounded border-gray-300 text-yellow-500">
                                            Utwórz mecze każdy z każdym
                                        </label>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                            <input name="generate_playoff" type="checkbox" value="1" checked class="rounded border-gray-300 text-yellow-500">
                                            Przygotuj drabinkę fazy pucharowej
                                        </label>
                                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300"><i data-lucide="shuffle" class="h-4 w-4"></i>Losuj grupy i mecze</button>
                                    </form>
                                </section>

                                <section class="rounded-lg border border-slate-200 p-4">
                                    <h5 class="flex items-center gap-2 font-black"><i data-lucide="users" class="h-4 w-4 text-yellow-600"></i>Zgłoszone drużyny</h5>
                                    <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-1">
                                        @forelse ($tournament->teams->sortBy('name') as $team)
                                            <a href="{{ route('three-x-three.teams.show', $team) }}" class="block rounded border border-slate-200 bg-slate-50 p-3 transition hover:border-yellow-400 hover:bg-yellow-50">
                                                <p class="font-bold">{{ $team->name }} <span class="text-xs uppercase text-yellow-700">{{ $team->category->label() }}</span></p>
                                                <p class="text-xs font-semibold text-slate-500">{{ $team->group?->name ?? 'Bez grupy' }}</p>
                                                <p class="mt-1 text-sm text-slate-600">{{ $team->players->pluck('name')->join(', ') }}</p>
                                            </a>
                                        @empty
                                            <p class="text-sm text-slate-500">Brak zgłoszonych drużyn.</p>
                                        @endforelse
                                    </div>
                                </section>
                            </aside>

                            <div class="space-y-5">
                                @forelse ($tournament->groups->sortBy('sort_order') as $group)
                                    @php($tableRows = $tournamentFlow->groupTable($group))
                                    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                                            <h5 class="font-black">{{ $group->name }}</h5>
                                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $group->teams->count() }} drużyn / {{ $group->matches->count() }} meczów</span>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-white text-xs uppercase tracking-wide text-slate-500">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">#</th>
                                                        <th class="px-3 py-2 text-left">Drużyna</th>
                                                        <th class="px-3 py-2 text-center">M</th>
                                                        <th class="px-3 py-2 text-center">W</th>
                                                        <th class="px-3 py-2 text-center">P</th>
                                                        <th class="px-3 py-2 text-center">KZ</th>
                                                        <th class="px-3 py-2 text-center">KS</th>
                                                        <th class="px-3 py-2 text-center">Bilans</th>
                                                        <th class="px-3 py-2 text-center">Pkt</th>
                                                        <th class="px-3 py-2 text-left">Forma</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @forelse ($tableRows as $index => $row)
                                                        <tr class="{{ $index < 2 ? 'bg-emerald-50/60' : '' }}">
                                                            <td class="border-l-4 {{ $index < 2 ? 'border-emerald-500' : 'border-slate-300' }} px-3 py-2 font-black">{{ $index + 1 }}</td>
                                                            <td class="px-3 py-2 font-bold"><a href="{{ route('three-x-three.teams.show', $row['team']) }}" class="hover:text-yellow-700">{{ $row['team']->name }}</a></td>
                                                            <td class="px-3 py-2 text-center">{{ $row['played'] }}</td>
                                                            <td class="px-3 py-2 text-center">{{ $row['wins'] }}</td>
                                                            <td class="px-3 py-2 text-center">{{ $row['losses'] }}</td>
                                                            <td class="px-3 py-2 text-center">{{ $row['points_for'] }}</td>
                                                            <td class="px-3 py-2 text-center">{{ $row['points_against'] }}</td>
                                                            <td class="px-3 py-2 text-center">{{ $row['point_diff'] }}</td>
                                                            <td class="px-3 py-2 text-center font-black">{{ $row['fiba_points'] }}</td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex gap-1">
                                                                    @forelse ($row['form'] as $form)
                                                                        <span class="h-2 w-2 rounded-full {{ $form === 'W' ? 'bg-emerald-500' : 'bg-rose-400' }}"></span>
                                                                    @empty
                                                                        <span class="text-xs text-slate-400">-</span>
                                                                    @endforelse
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="10" class="px-3 py-4 text-sm text-slate-500">Brak drużyn w grupie.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="space-y-2 border-t border-slate-200 bg-slate-50 p-3">
                                            @forelse ($group->matches->sortBy('sort_order') as $match)
                                                <form method="POST" action="{{ route('admin.3x3.tournaments.matches.update', [$tournament, $match]) }}" class="grid gap-2 rounded border border-slate-200 bg-white p-2 text-sm lg:grid-cols-[1fr_4rem_4rem_10rem_8rem_auto] lg:items-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="stage" value="{{ $match->stage }}">
                                                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                    <input type="hidden" name="team_one_id" value="{{ $match->team_one_id }}">
                                                    <input type="hidden" name="team_two_id" value="{{ $match->team_two_id }}">
                                                    <div class="font-bold">{{ $match->teamOne?->name ?? $match->team_one_placeholder }} <span class="text-slate-400">-</span> {{ $match->teamTwo?->name ?? $match->team_two_placeholder }}</div>
                                                    <input name="team_one_score" type="number" min="0" max="99" value="{{ $match->team_one_score }}" placeholder="Pkt 1" class="rounded border-gray-300 text-sm">
                                                    <input name="team_two_score" type="number" min="0" max="99" value="{{ $match->team_two_score }}" placeholder="Pkt 2" class="rounded border-gray-300 text-sm">
                                                    <input name="played_at" type="datetime-local" value="{{ $match->played_at?->format('Y-m-d\TH:i') }}" class="rounded border-gray-300 text-sm">
                                                    <input name="court" value="{{ $match->court }}" placeholder="Boisko" class="rounded border-gray-300 text-sm">
                                                    <button class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-black text-white hover:bg-yellow-400 hover:text-black">Zapisz</button>
                                                </form>
                                            @empty
                                                <p class="text-sm text-slate-500">Brak meczów w tej grupie.</p>
                                            @endforelse
                                        </div>
                                    </section>
                                @empty
                                    <section class="rounded-lg border border-dashed border-slate-300 p-6 text-sm text-slate-500">Nie ma jeszcze grup. Użyj losowania albo dodaj grupę ręcznie.</section>
                                @endforelse

                                <section class="rounded-lg border border-slate-200 p-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <h5 class="font-black">Drabinka fazy pucharowej</h5>
                                            <p class="text-sm text-slate-600">Po wpisaniu wyników grup odśwież drabinkę, aby podstawić aktualne miejsca z grup.</p>
                                        </div>
                                        <form method="POST" action="{{ route('admin.3x3.tournaments.playoff.refresh', $tournament) }}" class="flex gap-2">
                                            @csrf
                                            <input name="qualifiers_per_group" type="number" min="1" max="4" value="2" class="w-24 rounded border-gray-300 text-sm">
                                            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold hover:bg-yellow-50"><i data-lucide="refresh-cw" class="h-4 w-4"></i>Odśwież fazę pucharową</button>
                                        </form>
                                    </div>
                                    <div class="mt-4 grid gap-3 lg:grid-cols-4">
                                        @forelse ($tournament->matches->where('stage', 'playoff')->sortBy('sort_order')->groupBy('round_label') as $round => $matches)
                                            <div class="rounded border border-slate-200 bg-slate-50 p-3">
                                                <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-500">{{ $round ?: 'Faza pucharowa' }}</p>
                                                <div class="space-y-2">
                                                    @foreach ($matches as $match)
                                                        <form method="POST" action="{{ route('admin.3x3.tournaments.matches.update', [$tournament, $match]) }}" class="rounded border border-slate-200 bg-white p-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="stage" value="playoff">
                                                            <input type="hidden" name="round_label" value="{{ $match->round_label }}">
                                                            <input type="hidden" name="bracket_round_order" value="{{ $match->bracket_round_order }}">
                                                            <input type="hidden" name="bracket_position" value="{{ $match->bracket_position }}">
                                                            <input type="hidden" name="team_one_id" value="{{ $match->team_one_id }}">
                                                            <input type="hidden" name="team_two_id" value="{{ $match->team_two_id }}">
                                                            <input type="hidden" name="team_one_placeholder" value="{{ $match->team_one_placeholder }}">
                                                            <input type="hidden" name="team_two_placeholder" value="{{ $match->team_two_placeholder }}">
                                                            <div class="grid grid-cols-[1fr_3.5rem] gap-2">
                                                                <span class="truncate font-bold">{{ $match->teamOne?->name ?? $match->team_one_placeholder }}</span>
                                                                <input name="team_one_score" type="number" min="0" max="99" value="{{ $match->team_one_score }}" class="rounded border-gray-300 text-sm">
                                                                <span class="truncate font-bold">{{ $match->teamTwo?->name ?? $match->team_two_placeholder }}</span>
                                                                <input name="team_two_score" type="number" min="0" max="99" value="{{ $match->team_two_score }}" class="rounded border-gray-300 text-sm">
                                                            </div>
                                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                                <input name="played_at" type="datetime-local" value="{{ $match->played_at?->format('Y-m-d\TH:i') }}" class="rounded border-gray-300 text-xs">
                                                                <input name="court" value="{{ $match->court }}" placeholder="Boisko" class="rounded border-gray-300 text-xs">
                                                            </div>
                                                            <button class="mt-2 w-full rounded bg-slate-950 px-2 py-1.5 text-xs font-black text-white hover:bg-yellow-400 hover:text-black">Zapisz</button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-slate-500">Brak drabinki fazy pucharowej.</p>
                                        @endforelse
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div x-show="openModal === 'sponsor-category-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj kategorię sponsorów</h4>
                <form method="POST" action="{{ route('sponsor-categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-category-name-new">Nazwa kategorii</label>
                        <input id="sponsor-category-name-new" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-category-sort-new">Kolejność</label>
                        <input id="sponsor-category-sort-new" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
                        Aktywna
                    </label>
                    <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
                </form>
            </div>
        </div>

        <div x-show="openModal === 'sponsor-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                <h4 class="mb-4 text-lg font-black">Dodaj sponsora</h4>
                <form method="POST" action="{{ route('sponsors.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('profile.partials.sponsor-form-fields')
                    <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
                </form>
            </div>
        </div>

        @foreach ($sponsors as $sponsor)
            <div x-show="openModal === 'sponsor-edit-{{ $sponsor->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
                    <h4 class="mb-4 text-lg font-black">Edytuj sponsora</h4>
                    <form method="POST" action="{{ route('sponsors.update', $sponsor) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('profile.partials.sponsor-form-fields', ['sponsor' => $sponsor])
                        <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

