<!-- GÓRNY PASEK -->
<div class="bg-black text-white text-sm py-2 px-6 flex justify-between items-center border-b border-yellow-500 shadow-sm">

    <div class="font-semibold text-yellow-400">
        ETB - OFICJALNA STRONA
    </div>

    <div class="flex items-center space-x-4 text-white">

        <!-- FACEBOOK -->
        <a href="https://www.facebook.com/p/Eat-The-Ball-61572240317030/" target="_blank"
           class="hover:text-yellow-400 transition transform hover:scale-110">
            <!-- SVG zostaje -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.845c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.513c-1.491 0-1.955.926-1.955 1.874v2.249h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.063 24 12.073z"/>
            </svg>
        </a>

        <!-- INSTAGRAM -->
        <a href="https://www.instagram.com/eat_the_ball/" target="_blank"
           class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M7.75 2C4.574 2 2 4.574 2 7.75v8.5C2 19.426 4.574 22 7.75 22h8.5c3.176 0 5.75-2.574 5.75-5.75v-8.5C22 4.574 19.426 2 16.25 2h-8.5zm0 2h8.5c2.071 0 3.75 1.679 3.75 3.75v8.5c0 2.071-1.679 3.75-3.75 3.75h-8.5C5.679 20 4 18.321 4 16.25v-8.5C4 5.679 5.679 4 7.75 4zm4.25 2.5a5.75 5.75 0 100 11.5 5.75 5.75 0 000-11.5zm0 2a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5zm5.25-.75a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/>
            </svg>
        </a>

        <!-- YOUTUBE -->
        <a href="https://www.youtube.com/@EatTheBall3x3" target="_blank"
           class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                <path d="M23.498 6.186a2.997 2.997 0 00-2.11-2.12C19.54 3.5 12 3.5 12 3.5s-7.54 0-9.388.566a2.997 2.997 0 00-2.11 2.12C0 8.04 0 12 0 12s0 3.96.502 5.814a2.997 2.997 0 002.11 2.12C4.46 20.5 12 20.5 12 20.5s7.54 0 9.388-.566a2.997 2.997 0 002.11-2.12C24 15.96 24 12 24 12s0-3.96-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
        </a>

        <!-- TIKTOK -->
        <a href="https://www.tiktok.com/@eattheball_lodz" target="_blank"
           class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M12.75 2h2.25c.2 1.7 1.5 3 3.25 3.25v2.25c-1.3-.05-2.5-.4-3.5-1v7.75a5.5 5.5 0 11-5.5-5.5c.3 0 .6.03.9.08v2.3a3.25 3.25 0 102.65 3.17V2z"/>
            </svg>
        </a>

        <div class="w-px h-5 bg-yellow-400 opacity-50"></div>

        @auth
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 hover:text-yellow-400 transition font-medium">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">

                    <!-- głowa -->
                    <circle cx="12" cy="8" r="4" />

                    <!-- ramiona -->
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                </svg> Konto
            </a>
        @else
            <a href="{{ route('login') }}" class="flex items-center gap-2 hover:text-yellow-400 transition font-medium">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">

                    <!-- głowa -->
                    <circle cx="12" cy="8" r="4" />

                    <!-- ramiona -->
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                </svg> Zaloguj
            </a>
        @endauth

    </div>
</div>

<!-- NAVBAR -->
<nav class="bg-black text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <div class="text-3xl font-extrabold tracking-wider text-yellow-400">
            ETB
        </div>

        <!-- MENU -->
        <div class="space-x-8 hidden md:flex text-lg">

            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-yellow-400' : 'hover:text-yellow-400' }}">
                Aktualności
            </a>

            <a href="{{ route('team') }}"
               class="{{ request()->routeIs('team') ? 'text-yellow-400' : 'hover:text-yellow-400' }}">
                Drużyna
            </a>

            <a href="{{ route('schedule') }}"
               class="{{ request()->routeIs('schedule') ? 'text-yellow-400' : 'hover:text-yellow-400' }}">
                Rozgrywki
            </a>

            <a href="{{ route('news') }}"
               class="{{ request()->routeIs('news') ? 'text-yellow-400' : 'hover:text-yellow-400' }}">
                Klub
            </a>

            <a href="{{ route('contact') }}"
               class="{{ request()->routeIs('contact') ? 'text-yellow-400' : 'hover:text-yellow-400' }}">
                Kontakt
            </a>

        </div>

        <!-- CTA -->
        <div class="flex space-x-3">

            <a href="{{ route('tickets') }}"
               class="{{ request()->routeIs('tickets')
                   ? 'bg-yellow-400 text-black px-4 py-2 rounded font-semibold'
                   : 'bg-yellow-400 text-black px-4 py-2 rounded font-semibold hover:bg-yellow-300 transition' }}">
                <i data-lucide="ticket"></i> Bilety
            </a>

            <a href="{{ route('shop') }}"
               class="{{ request()->routeIs('shop')
                   ? 'bg-yellow-400 text-black px-4 py-2 rounded font-semibold'
                   : 'border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold hover:bg-yellow-400 hover:text-black transition' }}">
                <i data-lucide="shopping-cart"></i> Sklep
            </a>

            <a href="{{ route('academy') }}"
               class="{{ request()->routeIs('academy')
                   ? 'bg-yellow-400 text-black px-4 py-2 rounded font-semibold'
                   : 'border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold hover:bg-yellow-400 hover:text-black transition' }}">
                <i data-lucide="graduation-cap"></i> Akademia
            </a>

        </div>

    </div>
</nav>
