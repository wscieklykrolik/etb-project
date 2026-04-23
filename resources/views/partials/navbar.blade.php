<div class="bg-zinc-200 text-zinc-900 text-sm py-2 px-6 flex justify-between items-center border-b border-zinc-300 shadow-sm">
    <div class="font-semibold text-zinc-800">ETB - OFICJALNA STRONA</div>

    <div class="flex items-center space-x-4">
        <a href="https://www.facebook.com/p/Eat-The-Ball-61572240317030/" target="_blank" class="hover:text-yellow-400 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2.3V12h2.3V9.8c0-2.3 1.4-3.6 3.5-3.6 1 0 2 .2 2 .2v2.2h-1.1c-1.1 0-1.5.7-1.5 1.4V12h2.6l-.4 2.9h-2.2v7A10 10 0 0 0 22 12"/>
            </svg>
            <span>FB</span>
        </a>

        <a href="https://www.instagram.com/eat_the_ball/" target="_blank" class="hover:text-yellow-400 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M7 2C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5H7zm5 5a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm6.5-.9a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0zM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
            </svg>
            <span>IG</span>
        </a>

        <a href="https://www.youtube.com/@EatTheBall3x3" target="_blank" class="hover:text-yellow-400 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M23.5 6.2s-.2-1.7-.9-2.4c-.9-.9-1.9-.9-2.3-1C17.2 2.5 12 2.5 12 2.5h0s-5.2 0-8.3.3c-.4.1-1.4.1-2.3 1C.7 4.5.5 6.2.5 6.2S.2 8.1.2 10v2c0 1.9.3 3.8.3 3.8s.2 1.7.9 2.4c.9.9 2.1.9 2.6 1 1.9.2 8 .3 8 .3s5.2 0 8.3-.3c.4-.1 1.4-.1 2.3-1 .7-.7.9-2.4.9-2.4s.3-1.9.3-3.8v-2c0-1.9-.3-3.8-.3-3.8zM9.5 14.5v-5l5 2.5-5 2.5z"/>
            </svg>
            <span>YT</span>
        </a>

        <a href="https://www.tiktok.com/@eattheball_lodz" target="_blank" class="hover:text-yellow-400 flex items-center gap-1">
            <i data-lucide="music-2" class="w-4 h-4"></i><span>TT</span>
        </a>

        <div class="w-px h-5 bg-zinc-400"></div>

        <button class="px-2 py-1 border border-zinc-500 rounded text-zinc-700 hover:bg-yellow-400" onclick="adjustFontSize(0.1)">A+</button>
        <button class="px-2 py-1 border border-zinc-500 rounded text-zinc-700 hover:bg-yellow-400" onclick="adjustFontSize(-0.1)">A-</button>

        <div class="w-px h-5 bg-zinc-400"></div>

        @auth
            <a href="{{ route('profile.edit') }}" class="hover:text-yellow-400">Konto</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="hover:text-yellow-400 text-sm">Wyloguj</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-yellow-400">Zaloguj</a>
        @endauth
    </div>
</div>

<nav class="bg-zinc-100 text-zinc-900 shadow-md border-b border-zinc-300" x-data="{ open: null }">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <a href="{{ route('home') }}" class="text-3xl font-extrabold text-zinc-800 ajax-link">ETB</a>

            <form class="flex items-center gap-2 relative" role="search" onsubmit="event.preventDefault(); etbSearch()">
                <input id="etb-search" list="etb-search-suggestions" type="search" placeholder="Szukaj na stronie..." class="bg-white border border-zinc-300 rounded px-3 py-2 text-sm w-64">
                <datalist id="etb-search-suggestions"></datalist>
                <button type="submit" class="border border-zinc-500 text-black px-3 py-1.5 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-400">
                    <i data-lucide="search" class="w-4 h-4"></i> Szukaj
                </button>
            </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-6 text-lg items-center">
            <div class="relative" @mouseenter="open='news'" @mouseleave="open=null">
                <a href="{{ route('news') }}" class="ajax-link hover:text-yellow-400">Aktualności</a>
                <div x-show="open==='news'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('news.articles') }}">Artykuły</a>
                    <a class="ajax-link" href="{{ route('news.videos') }}">Wideo</a>
                    <a class="ajax-link" href="{{ route('news.galleries') }}">Galerie</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='club'" @mouseleave="open=null">
                <a href="{{ route('club') }}" class="ajax-link hover:text-yellow-400">Klub</a>
                <div x-show="open==='club'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('club.history') }}">Historia</a>
                    <a class="ajax-link" href="{{ route('club.board') }}">Władze klubu</a>
                    <a class="ajax-link" href="{{ route('club.venue') }}">Obiekt</a>
                    <a class="ajax-link" href="{{ route('club.business') }}">Oferta biznesowa</a>
                    <a class="ajax-link" href="{{ route('club.investors') }}">Inwestorzy</a>
                    <a class="ajax-link" href="{{ route('club.success') }}">Sukcesy</a>
                    <a class="ajax-link" href="{{ route('club.sponsors') }}">Sponsorzy</a>
                    <a class="ajax-link" href="{{ route('contact') }}">Kontakt</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='schedule'" @mouseleave="open=null">
                <a href="{{ route('schedule') }}" class="ajax-link hover:text-yellow-400">Rozgrywki</a>
                <div x-show="open==='schedule'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('schedule.third-league') }}">III liga mężczyzn ŁZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.lzkosz') }}">Terminarz ŁZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.table') }}">Tabela</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3') }}">Terminarz 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.tournaments') }}">Turnieje 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.team') }}">Zespół</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='team'" @mouseleave="open=null">
                <a href="{{ route('team') }}" class="ajax-link hover:text-yellow-400">Drużyna</a>
                <div x-show="open==='team'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('team.players') }}">Zawodnicy</a>
                    <a class="ajax-link" href="{{ route('team.staff') }}">Sztab szkoleniowy</a>
                    <a class="ajax-link" href="{{ route('team3x3.players') }}">Zawodnicy 3x3</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="ajax-link hover:text-yellow-400">Kontakt</a>

            <div class="ml-auto flex gap-2">
                <a href="{{ route('tickets') }}" class="ajax-link bg-yellow-400 border border-zinc-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2">
                    <i data-lucide="ticket" class="w-4 h-4"></i> Bilety
                </a>
                <a href="{{ route('shop') }}" class="ajax-link border border-zinc-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-400">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> Sklep
                </a>
                <a href="{{ route('academy') }}" class="ajax-link border border-zinc-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-400">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i> Akademia
                </a>
            </div>
        </div>
    </div>
</nav>
