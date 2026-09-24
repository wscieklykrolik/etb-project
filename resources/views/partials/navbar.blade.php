<div class="etb-utility-bar flex flex-col gap-3 border-b border-zinc-300 bg-zinc-200 px-4 py-2 text-sm text-zinc-900 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
    <div class="font-semibold text-zinc-800">Eat The Ball - oficjalna strona</div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
        <a href="https://www.facebook.com/p/Eat-The-Ball-61572240317030/" target="_blank" class="hover:text-yellow-400 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2.3V12h2.3V9.8c0-2.3 1.4-3.6 3.5-3.6 1 0 2 .2 2 .2v2.2h-1.1c-1.1 0-1.5.7-1.5 1.4V12h2.6l-.4 2.9h-2.2v7A10 10 0 0 0 22 12"/>
            </svg>
            <span>FB</span>
        </a>

        <a href="https://www.instagram.com/eat_the_ball/" target="_blank" class="hover:text-yellow-400 flex items-center gap-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M7 2C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5H7zm5 5a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm6.5-.9a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0zM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
            </svg>
            <span>IG</span>
        </a>

        <a href="https://www.youtube.com/@EatTheBall3x3" target="_blank" class="hover:text-yellow-400 flex items-center gap-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M23.5 6.2s-.2-1.7-.9-2.4c-.9-.9-1.9-.9-2.3-1C17.2 2.5 12 2.5 12 2.5h0s-5.2 0-8.3.3c-.4.1-1.4.1-2.3 1C.7 4.5.5 6.2.5 6.2S.2 8.1.2 10v2c0 1.9.3 3.8.3 3.8s.2 1.7.9 2.4c.9.9 2.1.9 2.6 1 1.9.2 8 .3 8 .3s5.2 0 8.3-.3c.4-.1 1.4-.1 2.3-1 .7-.7.9-2.4.9-2.4s.3-1.9.3-3.8v-2c0-1.9-.3-3.8-.3-3.8zM9.5 14.5v-5l5 2.5-5 2.5z"/>
            </svg>
            <span>YT</span>
        </a>

        <a href="https://www.tiktok.com/@eattheball_lodz" target="_blank" class="hover:text-yellow-400 flex items-center gap-1 transition-colors">
            <i data-lucide="music-2" class="w-4 h-4"></i><span>TT</span>
        </a>

        <div class="w-px h-5 bg-zinc-700"></div>

        <button type="button" class="px-2 py-1 border border-zinc-600 rounded text-zinc-700 hover:bg-yellow-400 hover:text-black hover:border-yellow-400 transition-all" onclick="adjustFontSize(1)">A+</button>
        <button type="button" class="px-2 py-1 border border-zinc-600 rounded text-zinc-700 hover:bg-yellow-400 hover:text-black hover:border-yellow-400 transition-all" onclick="adjustFontSize(-1)">A-</button>

        <div class="w-px h-5 bg-zinc-700"></div>

        @auth
            <a href="{{ route('profile.edit', ['section' => 'account']) }}" class="hover:text-yellow-400 transition-colors">Konto</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="hover:text-yellow-400 text-sm transition-colors">Wyloguj</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-yellow-400 transition-colors">Zaloguj</a>
        @endauth
    </div>
</div>

<nav aria-label="Nawigacja główna" class="etb-navigation bg-zinc-100 text-zinc-900 shadow-md border-b border-zinc-300" x-data="{ open: null, menuOpen: false }" @click.outside="open = null" @keydown.escape.stop="if (open) { $refs[open + 'Toggle'].focus(); open = null } else { menuOpen = false; $refs.menuToggle.focus() }">
    <div class="mx-auto max-w-[108rem] px-4 py-4 sm:px-6">
        <div class="etb-header-grid">
            <a href="{{ route('home') }}" class="etb-header-logo ajax-link flex items-center justify-center p-1">
                <x-site-logo :url="$clubLogoUrl" image-class="max-h-full max-w-full object-contain" fallback="ETB" fallback-class="text-center text-2xl font-black text-zinc-800" />
            </a>

            <div class="etb-header-title">
                <a href="{{ route('home') }}" class="ajax-link text-xl font-extrabold sm:text-3xl">ETB Łódź</a>
            </div>
            <button type="button" x-ref="menuToggle" class="etb-menu-toggle rounded-lg border border-zinc-400 px-3 py-2 font-bold" @click="menuOpen = !menuOpen; open = null" :aria-expanded="menuOpen.toString()" aria-controls="etb-primary-menu">
                <i data-lucide="menu" class="h-5 w-5" aria-hidden="true"></i>
                <span x-text="menuOpen ? 'Zamknij' : 'Menu'">Menu</span>
            </button>
            <div id="etb-primary-menu" class="etb-primary-menu" :class="{ 'is-open': menuOpen }">
                <a href="{{ route('news.index') }}" class="etb-nav-link ajax-link">Aktualności</a>
                @php
                    $navigationGroups = [
                        'club' => ['Klub', [
                            ['club', 'O klubie'], ['club.history', 'Historia'], ['club.board', 'Władze klubu'],
                            ['club.venue', 'Obiekt'], ['club.business', 'Oferta biznesowa'],
                            ['club.success', 'Sukcesy'], ['club.sponsors', 'Sponsorzy'],
                        ]],
                        'schedule' => ['Rozgrywki', [
                            ['schedule', 'Terminarz'], ['schedule.third-league', 'III liga mężczyzn ŁZKosz'],
                            ['schedule.lzkosz', 'Terminarz ŁZKosz'], ['schedule.table', 'Tabela'],
                            ['schedule.3x3', 'Terminarz 3x3'], ['schedule.3x3.tournaments', 'Turnieje 3x3'],
                        ]],
                        'team' => ['Drużyna', [
                            ['team', 'O drużynie'], ['team.players', 'Zawodnicy'],
                            ['team.staff', 'Sztab szkoleniowy'], ['team.3x3', 'Drużyna 3x3'],
                        ]],
                        'contact' => ['Kontakt', [['contact', 'Kontakt'], ['contact', 'Marketing', '#marketing']]],
                    ];
                @endphp
                @foreach ($navigationGroups as $key => [$label, $links])
                    <div class="etb-nav-group" @focusout="if (!$el.contains($event.relatedTarget) && open === '{{ $key }}') open = null">
                        <button type="button" x-ref="{{ $key }}Toggle" class="etb-nav-link" @click="open = open === '{{ $key }}' ? null : '{{ $key }}'" :aria-expanded="(open === '{{ $key }}').toString()" aria-controls="etb-menu-{{ $key }}">
                            {{ $label }}
                            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open === '{{ $key }}' }" aria-hidden="true"></i>
                        </button>
                        <div id="etb-menu-{{ $key }}" x-cloak x-show="open === '{{ $key }}'" class="dropdown-panel">
                            @foreach ($links as $link)
                                <a class="ajax-link" href="{{ route($link[0]) }}{{ $link[2] ?? '' }}">{{ $link[1] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="etb-header-actions flex w-full min-w-0 flex-col gap-3">
                <form id="etb-site-search" class="relative flex w-full min-w-0 items-stretch gap-2" role="search" autocomplete="off" onsubmit="event.preventDefault(); etbSearch()">
                    <div class="relative min-w-0 flex-1 rounded border border-zinc-300 bg-white text-sm shadow-sm transition focus-within:border-yellow-400 focus-within:ring-2 focus-within:ring-yellow-400/70">
                        <div id="etb-search-ghost" class="etb-search-ghost pointer-events-none absolute inset-0 flex items-center overflow-hidden whitespace-pre px-3 text-zinc-400" aria-hidden="true"></div>
                        <input
                            id="etb-search"
                            type="search"
                            aria-label="Szukaj na stronie"
                            autocomplete="off"
                            placeholder="Szukaj na stronie..."
                            aria-autocomplete="list"
                            aria-controls="etb-search-panel"
                            aria-expanded="false"
                            class="relative z-10 w-full rounded border-0 bg-transparent px-3 py-2 text-sm text-zinc-950 placeholder:text-zinc-500 focus:outline-none focus:ring-0"
                        >
                    </div>
                    <div id="etb-search-panel" class="etb-search-panel absolute left-0 top-full z-50 mt-2 hidden w-full overflow-hidden rounded border border-zinc-800 bg-zinc-950 text-sm text-white shadow-xl sm:w-72" role="listbox"></div>
                    <button type="submit" class="inline-flex shrink-0 items-center gap-2 rounded border border-zinc-500 px-3 py-2 font-semibold text-black hover:bg-yellow-400">
                        <i data-lucide="search" class="w-4 h-4"></i> Szukaj
                    </button>
                </form>
                <div class="etb-quick-links grid w-full grid-cols-3 gap-2">
                    <a href="{{ route('tickets') }}" class="ajax-link inline-flex items-center justify-center gap-2 rounded border border-zinc-500 bg-yellow-400 px-3 py-2 text-sm font-semibold text-black">
                        <i data-lucide="ticket" class="w-4 h-4"></i> Bilety
                    </a>
                    <a href="{{ route('shop.index') }}" class="ajax-link relative inline-flex items-center justify-center gap-2 rounded border border-zinc-500 px-3 py-2 text-sm font-semibold text-black hover:bg-yellow-400">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Sklep
                        <span x-data="{ count: 0 }" x-init="fetch('{{ route('cart.badge') }}').then(r=>r.json()).then(d=>count=d.count); setInterval(()=>fetch('{{ route('cart.badge') }}').then(r=>r.json()).then(d=>count=d.count),30000)" x-show="count > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" x-text="count"></span>
                    </a>
                    <a href="{{ route('academy') }}" class="ajax-link inline-flex items-center justify-center gap-2 rounded border border-zinc-500 px-3 py-2 text-sm font-semibold text-black hover:bg-yellow-400 hover:border-yellow-400 transition-all">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i> Akademia
                    </a>
                </div>
            </div>

            @if ($titleSponsorLogoUrl)
            <div class="etb-header-sponsor flex items-center justify-center p-2">
                @if ($titleSponsorLogoUrl && $titleSponsorUrl)
                    <a href="{{ $titleSponsorUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex max-h-20 w-full items-center justify-end">
                        <x-site-logo :url="$titleSponsorLogoUrl" alt="Logo sponsora tytularnego" image-class="max-h-20 max-w-full object-contain" fallback="" />
                    </a>
                @else
                    <x-site-logo :url="$titleSponsorLogoUrl" alt="Logo sponsora tytularnego" image-class="max-h-20 max-w-full object-contain" fallback="" />
                @endif
            </div>
            @endif
        </div>
    </div>
</nav>
