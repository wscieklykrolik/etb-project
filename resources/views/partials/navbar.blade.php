<div class="bg-zinc-200 text-zinc-900 text-sm py-2 px-6 flex justify-between items-center border-b border-zinc-300 shadow-sm">
    <div class="font-semibold text-zinc-800">ETB - OFICJALNA STRONA</div>

    <div class="flex items-center space-x-4">
        <a href="https://www.facebook.com/p/Eat-The-Ball-61572240317030/" target="_blank" class="hover:text-yellow-500 flex items-center gap-1">
            <i data-lucide="facebook" class="w-4 h-4"></i><span>FB</span>
        </a>
        <a href="https://www.instagram.com/eat_the_ball/" target="_blank" class="hover:text-yellow-500 flex items-center gap-1">
            <i data-lucide="instagram" class="w-4 h-4"></i><span>IG</span>
        </a>
        <a href="https://www.youtube.com/@EatTheBall3x3" target="_blank" class="hover:text-yellow-500 flex items-center gap-1">
            <i data-lucide="youtube" class="w-4 h-4"></i><span>YT</span>
        </a>
        <a href="https://www.tiktok.com/@eattheball_lodz" target="_blank" class="hover:text-yellow-500 flex items-center gap-1">
            <i data-lucide="music-2" class="w-4 h-4"></i><span>TT</span>
        </a>

        <div class="w-px h-5 bg-zinc-400"></div>

        <button class="px-2 py-1 border border-zinc-500 rounded text-zinc-700 hover:bg-yellow-500" onclick="adjustFontSize(0.1)">A+</button>
        <button class="px-2 py-1 border border-zinc-500 rounded text-zinc-700 hover:bg-yellow-500" onclick="adjustFontSize(-0.1)">A-</button>

        <div class="w-px h-5 bg-zinc-400"></div>

        @auth
            <a href="{{ route('dashboard') }}" class="hover:text-yellow-500">Konto</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="hover:text-red-500 text-sm">Wyloguj</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-yellow-500">Zaloguj</a>
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
                <button type="submit" class="border border-zinc-500 text-black px-3 py-1.5 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-500">
                    <i data-lucide="search" class="w-4 h-4"></i> Szukaj
                </button>
                </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-6 text-lg items-center">
            <div class="relative" @mouseenter="open='news'" @mouseleave="open=null">
                <a href="{{ route('news') }}" class="ajax-link hover:text-yellow-500">Aktualności</a>
                <div x-show="open==='news'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('news.articles') }}">Artykuły</a>
                    <a class="ajax-link" href="{{ route('news.videos') }}">Wideo</a>
                    <a class="ajax-link" href="{{ route('news.galleries') }}">Galerie</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='club'" @mouseleave="open=null">
                <a href="{{ route('club') }}" class="ajax-link hover:text-yellow-500">Klub</a>
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
                <a href="{{ route('schedule') }}" class="ajax-link hover:text-yellow-500">Rozgrywki</a>
                <div x-show="open==='schedule'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('schedule.third-league') }}">III liga mężczyzn ŁZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.mzkosz') }}">Terminarz ŁZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.table') }}">Tabela</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3') }}">Terminarz 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.tournaments') }}">Turnieje 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.team') }}">Zespół</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='team'" @mouseleave="open=null">
                <a href="{{ route('team') }}" class="ajax-link hover:text-yellow-500">Drużyna</a>
                <div x-show="open==='team'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('team.players') }}">Zawodnicy</a>
                    <a class="ajax-link" href="{{ route('team.staff') }}">Sztab szkoleniowy</a>
                    <a class="ajax-link" href="{{ route('team3x3.players') }}">Zawodnicy 3x3</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="ajax-link hover:text-yellow-500">Kontakt</a>

            <div class="ml-auto flex gap-2">
                <a href="{{ route('tickets') }}" class="ajax-link bg-yellow-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2">
                    <i data-lucide="ticket" class="w-4 h-4"></i> Bilety
                </a>
                <a href="{{ route('shop') }}" class="ajax-link border border-zinc-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-500">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> Sklep
                </a>
                <a href="{{ route('academy') }}" class="ajax-link border border-zinc-500 text-black px-4 py-2 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-500">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i> Akademia
                </a>
            </div>
        </div>
    </div>
</nav>
