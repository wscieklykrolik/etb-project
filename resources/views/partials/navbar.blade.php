<div class="flex flex-col gap-3 border-b border-zinc-300 bg-zinc-200 px-4 py-2 text-sm text-zinc-900 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
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

<nav class="bg-zinc-100 text-zinc-900 shadow-md border-b border-zinc-300" x-data="{ open: null }">
    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_27rem_16rem] xl:items-center">
            <div class="min-w-0">
                <a href="{{ route('home') }}" class="ajax-link inline-flex min-h-14 items-center gap-3">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-white p-1 ring-1 ring-zinc-300">
                        <x-site-logo :url="$clubLogoUrl" image-class="max-h-full max-w-full object-contain" fallback-class="text-base font-black text-zinc-800" />
                    </span>
                    <span class="text-3xl font-extrabold text-zinc-800">ETB Łódź</span>
                </a>

                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-3 text-base sm:text-lg lg:gap-6">
                    <div class="relative" @mouseenter="open='news'" @mouseleave="open=null">
                        <a href="{{ route('news.index') }}" class="ajax-link font-semibold text-black transition-colors hover:text-yellow-400">Aktualności</a>
                    </div>

                    <div class="relative" @mouseenter="open='club'" @mouseleave="open=null">
                        <a href="{{ route('club') }}" class="ajax-link font-semibold text-black transition-colors hover:text-yellow-400">Klub</a>
                        <div x-show="open==='club'" x-transition class="dropdown-panel">
                            <a class="ajax-link" href="{{ route('club.history') }}">Historia</a>
                            <a class="ajax-link" href="{{ route('club.board') }}">Władze klubu</a>
                            <a class="ajax-link" href="{{ route('club.venue') }}">Obiekt</a>
                            <a class="ajax-link" href="{{ route('club.business') }}">Oferta biznesowa</a>
                            <a class="ajax-link" href="{{ route('club.success') }}">Sukcesy</a>
                            <a class="ajax-link" href="{{ route('club.sponsors') }}">Sponsorzy</a>
                            <a class="ajax-link" href="{{ route('club.contact') }}">Kontakt</a>
                        </div>
                    </div>

                    <div class="relative" @mouseenter="open='schedule'" @mouseleave="open=null">
                        <a href="{{ route('schedule') }}" class="ajax-link font-semibold text-black transition-colors hover:text-yellow-400">Rozgrywki</a>
                        <div x-show="open==='schedule'" x-transition class="dropdown-panel">
                            <a class="ajax-link" href="{{ route('schedule') }}">Terminarz</a>
                            <a class="ajax-link" href="{{ route('schedule.third-league') }}">III liga mężczyzn ŁZKosz</a>
                            <a class="ajax-link" href="{{ route('schedule.lzkosz') }}">Terminarz ŁZKosz</a>
                            <a class="ajax-link" href="{{ route('schedule.table') }}">Tabela</a>
                            <a class="ajax-link" href="{{ route('schedule.3x3') }}">Terminarz 3x3</a>
                            <a class="ajax-link" href="{{ route('schedule.3x3.tournaments') }}">Turnieje 3x3</a>
                            <a class="ajax-link" href="{{ route('schedule.3x3.team') }}">Zespół</a>
                        </div>
                    </div>

                    <div class="relative" @mouseenter="open='team'" @mouseleave="open=null">
                        <a href="{{ route('team') }}" class="ajax-link font-semibold text-black transition-colors hover:text-yellow-400">Drużyna</a>
                        <div x-show="open==='team'" x-transition class="dropdown-panel">
                            <a class="ajax-link" href="{{ route('team.players') }}">Zawodnicy</a>
                            <a class="ajax-link" href="{{ route('team.staff') }}">Sztab szkoleniowy</a>
                            <a class="ajax-link" href="{{ route('team.3x3') }}">Zawodnicy 3x3</a>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}" class="ajax-link font-semibold text-black transition-colors hover:text-yellow-400">Kontakt</a>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3">
                <form id="etb-site-search" class="relative flex w-full min-w-0 items-stretch gap-2" role="search" autocomplete="off" onsubmit="event.preventDefault(); etbSearch()">
                    <div class="relative min-w-0 flex-1 rounded border border-zinc-300 bg-white text-sm shadow-sm transition focus-within:border-yellow-400 focus-within:ring-2 focus-within:ring-yellow-400/70">
                        <div id="etb-search-ghost" class="etb-search-ghost pointer-events-none absolute inset-0 flex items-center overflow-hidden whitespace-pre px-3 text-zinc-400" aria-hidden="true"></div>
                        <input
                            id="etb-search"
                            type="search"
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
                <div class="grid w-full grid-cols-3 gap-2">
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

            <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-zinc-400 bg-white p-3 text-center">
                <x-site-logo :url="$titleSponsorLogoUrl" alt="Logo sponsora tytularnego" image-class="max-h-20 max-w-full object-contain" fallback="Sponsor tytularny" fallback-class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500" />
            </div>
        </div>
    </div>
</nav>
