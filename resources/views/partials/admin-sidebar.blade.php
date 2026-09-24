@php
    $activeSection = $activeSection ?? '';
    $sectionUrl = fn (string $section): string => route('profile.edit', ['section' => $section]);
    $sectionClasses = fn (string $section): string => $activeSection === $section
        ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black'
        : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black';
@endphp
            <aside class="bg-slate-950 text-white" x-data="{ menuOpen: false }">
                <button type="button" class="flex w-full items-center gap-3 px-5 py-4 font-bold lg:hidden" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen"><i data-lucide="menu" class="h-5 w-5"></i>Menu panelu</button>
                <div class="top-0 hidden flex-col overflow-hidden px-5 py-6 lg:sticky lg:flex lg:h-screen" :class="{ '!flex': menuOpen }">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
                        <x-site-logo :url="$adminLogoUrl" alt="Logo panelu admina" image-class="h-11 w-11 rounded-full bg-white object-contain p-1 ring-1 ring-yellow-300" fallback-class="flex h-11 w-11 items-center justify-center rounded-full bg-yellow-400 text-xl font-black text-black" />
                        <span>
                            <span class="block text-xl font-black leading-5 text-yellow-400">ETB Łódź</span>
                            <span class="block text-xs font-bold uppercase tracking-[0.22em] text-white">Admin</span>
                        </span>
                    </a>

                    <nav class="admin-side-nav mt-8 min-h-0 flex-1 space-y-6 overflow-y-auto pr-1 text-sm">
                        <div>
                            <a href="{{ $sectionUrl('dashboard') }}" class="{{ $activeSection === 'dashboard' ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-4 py-3 font-black text-black' : 'flex items-center gap-3 rounded-lg px-4 py-3 font-black text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}">
                                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                                Pulpit
                            </a>
                            <div class="mt-3 space-y-1">
                                <a href="{{ $sectionUrl('notifications-history') }}" class="{{ $sectionClasses('notifications-history') }}"><i data-lucide="history" class="h-4 w-4"></i>Historia zmian</a>
                                <a href="{{ $sectionUrl('faq') }}" class="{{ $sectionClasses('faq') }}"><i data-lucide="circle-help" class="h-4 w-4"></i>Pytania i odpowiedzi</a>
                            </div>
                        </div>

                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Zarządzanie</p>
                            <div class="mt-3 space-y-1">
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ $sectionUrl('users') }}" class="{{ $sectionClasses('users') }}"><i data-lucide="users" class="h-4 w-4"></i>Użytkownicy</a>
                                @endif
                                <a href="{{ $sectionUrl('tickets') }}" class="{{ $sectionClasses('tickets') }}"><i data-lucide="ticket" class="h-4 w-4"></i>Bilety</a>
                                <a href="{{ $sectionUrl('club-content') }}" class="{{ $sectionClasses('club-content') }}"><i data-lucide="building-2" class="h-4 w-4"></i>Klub</a>
                                <a href="{{ $sectionUrl('important-links') }}" class="{{ $sectionClasses('important-links') }}"><i data-lucide="link" class="h-4 w-4"></i>Ważne linki</a>
                                <a href="{{ $sectionUrl('news') }}" class="{{ $sectionClasses('news') }}"><i data-lucide="newspaper" class="h-4 w-4"></i>Aktualności</a>
                                <a href="{{ $sectionUrl('academy') }}" class="{{ $sectionClasses('academy') }}"><i data-lucide="graduation-cap" class="h-4 w-4"></i>Akademia</a>
                                <a href="{{ $sectionUrl('sponsors') }}" class="{{ $sectionClasses('sponsors') }}"><i data-lucide="handshake" class="h-4 w-4"></i>Sponsorzy</a>
                            </div>
                        </div>

                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Terminarz</p>
                            <div class="mt-3 space-y-1">
                                <a href="{{ $sectionUrl('matches') }}" class="{{ $sectionClasses('matches') }}"><i data-lucide="calendar-days" class="h-4 w-4"></i>Mecze</a>
                                <a href="{{ route('schedule.lzkosz') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="calendar" class="h-4 w-4"></i>Terminarz ŁZKosz</a>
                                <a href="{{ $sectionUrl('league-table') }}" class="{{ $sectionClasses('league-table') }}"><i data-lucide="table-2" class="h-4 w-4"></i>Tabela ligi</a>
                                <a href="{{ route('schedule.3x3') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black"><i data-lucide="calendar-range" class="h-4 w-4"></i>Terminarz 3x3</a>
                                <a href="{{ $sectionUrl('tournaments') }}" class="{{ $sectionClasses('tournaments') }}"><i data-lucide="trophy" class="h-4 w-4"></i>Turnieje 3x3</a>
                            </div>
                        </div>

                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Skład</p>
                            <div class="mt-3 space-y-1">
                                <a href="{{ $sectionUrl('players') }}" class="{{ $sectionClasses('players') }}"><i data-lucide="user-round" class="h-4 w-4"></i>Zawodnicy</a>
                                <a href="{{ $sectionUrl('staff') }}" class="{{ $sectionClasses('staff') }}"><i data-lucide="user-cog" class="h-4 w-4"></i>Sztab szkoleniowy</a>
                                <a href="{{ $sectionUrl('three-x-three') }}" class="{{ $sectionClasses('three-x-three') }}"><i data-lucide="circle-dot" class="h-4 w-4"></i>Drużyna 3x3</a>
                            </div>
                        </div>
                        <div>
                            <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Sklep</p>
                            <div class="mt-3 space-y-1">
                                <a href="{{ $sectionUrl('shop') }}" class="{{ $sectionClasses('shop') }}"><i data-lucide="store" class="h-4 w-4"></i>Podsumowanie sklepu</a>
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('admin.shop-settings.edit') }}" class="{{ request()->routeIs('admin.shop-settings.*') ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black' : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}"><i data-lucide="settings" class="h-4 w-4"></i>Ustawienia zamówień</a>
                                @endif
                                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black' : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}"><i data-lucide="shopping-cart" class="h-4 w-4"></i>Zamówienia</a>
                                <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black' : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}"><i data-lucide="package" class="h-4 w-4"></i>Produkty</a>
                                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black' : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}"><i data-lucide="tags" class="h-4 w-4"></i>Kategorie</a>
                                <a href="{{ route('admin.product-filters.index') }}" class="{{ request()->routeIs('admin.product-filters.*') ? 'flex items-center gap-3 rounded-lg bg-yellow-400 px-3 py-2.5 font-black text-black' : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-slate-200 transition hover:bg-yellow-400 hover:text-black' }}"><i data-lucide="sliders-horizontal" class="h-4 w-4"></i>Filtry sklepu</a>
                            </div>
                        </div>
                    </nav>

                    <div class="shrink-0 border-t border-white/10 pt-5">
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
