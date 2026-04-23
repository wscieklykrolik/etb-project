<div class="bg-black text-white text-sm py-2 px-6 flex justify-between items-center border-b border-yellow-500 shadow-sm">
    <div class="font-semibold text-yellow-400">ETB - OFICJALNA STRONA</div>

    <div class="flex items-center space-x-4">
        <a href="https://www.facebook.com/p/Eat-The-Ball-61572240317030/" target="_blank" class="hover:text-yellow-400">FB</a>
        <a href="https://www.instagram.com/eat_the_ball/" target="_blank" class="hover:text-yellow-400">IG</a>
        <a href="https://www.youtube.com/@EatTheBall3x3" target="_blank" class="hover:text-yellow-400">YT</a>
        <a href="https://www.tiktok.com/@eattheball_lodz" target="_blank" class="hover:text-yellow-400">TT</a>
        <div class="w-px h-5 bg-yellow-400 opacity-50"></div>

        @auth
            <a href="{{ route('dashboard') }}" class="hover:text-yellow-400">Konto</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="hover:text-red-400 text-sm">Wyloguj</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-yellow-400">Zaloguj</a>
        @endauth
    </div>
</div>

<nav class="bg-black text-white shadow-md border-b border-zinc-800" x-data="{ open: null }">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <a href="{{ route('home') }}" class="text-3xl font-extrabold text-yellow-400 ajax-link">ETB</a>

            <div class="flex items-center gap-2">
                <button class="px-2 py-1 border border-yellow-400 rounded text-yellow-400" onclick="adjustFontSize(0.1)">A+</button>
                <button class="px-2 py-1 border border-yellow-400 rounded text-yellow-400" onclick="adjustFontSize(-0.1)">A-</button>
            </div>

            <form class="flex items-center gap-2" role="search" onsubmit="event.preventDefault(); etbSearch()">
                <input id="etb-search" type="search" placeholder="Szukaj na stronie..." class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2 text-sm w-56">
                <button class="bg-yellow-400 text-black px-3 py-2 rounded font-semibold">Szukaj</button>
            </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-6 text-lg">
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
                    <a class="ajax-link" href="{{ route('schedule.mzkosz') }}">Terminarz MZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.third-league') }}">III liga mężczyzn MZKosz</a>
                    <a class="ajax-link" href="{{ route('schedule.table') }}">Tabela</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3') }}">Terminarz 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.tournaments') }}">Turnieje 3x3</a>
                    <a class="ajax-link" href="{{ route('schedule.3x3.team') }}">Drużyna 3x3</a>
                </div>
            </div>

            <div class="relative" @mouseenter="open='team'" @mouseleave="open=null">
                <a href="{{ route('team') }}" class="ajax-link hover:text-yellow-400">Drużyna</a>
                <div x-show="open==='team'" x-transition class="dropdown-panel">
                    <a class="ajax-link" href="{{ route('team.players') }}">Zawodnicy</a>
                    <a class="ajax-link" href="{{ route('team.staff') }}">Sztab szkoleniowy</a>
                    <a class="ajax-link" href="{{ route('team3x3.players') }}">Drużyna 3x3 - zawodnicy</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="ajax-link hover:text-yellow-400">Kontakt</a>

            <div class="ml-auto flex gap-2">
                <a href="{{ route('tickets') }}" class="ajax-link bg-yellow-400 text-black px-4 py-2 rounded font-semibold">Bilety</a>
                <a href="{{ route('shop') }}" class="ajax-link border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold">Sklep</a>
                <a href="{{ route('academy') }}" class="ajax-link border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold">Akademia</a>
            </div>
        </div>
    </div>
</nav>
