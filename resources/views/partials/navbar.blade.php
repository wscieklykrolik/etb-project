<!-- GŁÓRNY PASEK -->
<div class="bg-black text-white text-sm py-2 px-6 flex justify-between items-center border-b border-yellow-500 shadow-sm">

    <div class="font-semibold text-yellow-400">
        ETB - OFICJALNA STRONA
    </div>

    <div class="flex items-center space-x-4 text-white">

        <a href="#" class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.845c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.513c-1.491 0-1.955.926-1.955 1.874v2.249h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.063 24 12.073z"/>
            </svg>
        </a>

        <a href="#" class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M7.75 2C4.574 2 2 4.574 2 7.75v8.5C2 19.426 4.574 22 7.75 22h8.5c3.176 0 5.75-2.574 5.75-5.75v-8.5C22 4.574 19.426 2 16.25 2h-8.5zm0 2h8.5c2.071 0 3.75 1.679 3.75 3.75v8.5c0 2.071-1.679 3.75-3.75 3.75h-8.5C5.679 20 4 18.321 4 16.25v-8.5C4 5.679 5.679 4 7.75 4zm4.25 2.5a5.75 5.75 0 100 11.5 5.75 5.75 0 000-11.5zm0 2a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5zm5.25-.75a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/>
            </svg>
        </a>

        <a href="#" class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                <path d="M23.498 6.186a2.997 2.997 0 00-2.11-2.12C19.54 3.5 12 3.5 12 3.5s-7.54 0-9.388.566a2.997 2.997 0 00-2.11 2.12C0 8.04 0 12 0 12s0 3.96.502 5.814a2.997 2.997 0 002.11 2.12C4.46 20.5 12 20.5 12 20.5s7.54 0 9.388-.566a2.997 2.997 0 002.11-2.12C24 15.96 24 12 24 12s0-3.96-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
        </a>

        <a href="#" aria-label="TikTok"
           class="hover:text-yellow-400 transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5 fill-current"
                 viewBox="0 0 24 24">
                <path d="M12.75 2h2.25c.2 1.7 1.5 3 3.25 3.25v2.25c-1.3-.05-2.5-.4-3.5-1v7.75a5.5 5.5 0 11-5.5-5.5c.3 0 .6.03.9.08v2.3a3.25 3.25 0 102.65 3.17V2z"/>
            </svg>
        </a>

        <div class="w-px h-5 bg-yellow-400 opacity-50"></div>

        @auth
            <a href="/dashboard"
               class="flex items-center gap-2 hover:text-yellow-400 transition font-medium">
                <i data-lucide="user"></i>
                Panel
            </a>
        @else
            <a href="/login"
               class="flex items-center gap-2 hover:text-yellow-400 transition font-medium">
                <i data-lucide="user"></i>
                Zaloguj
            </a>
        @endauth

    </div>

    </div>

</div>

<nav class="bg-black text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <div class="text-3xl font-extrabold tracking-wider text-yellow-400">
            ETB
        </div>

        <div class="space-x-8 hidden md:flex text-lg">
            <a href="/" class="hover:text-yellow-400 transition">Aktualności</a>
            <a href="/team" class="hover:text-yellow-400 transition">Drużyna</a>
            <a href="/schedule" class="hover:text-yellow-400 transition">Rozgrywki</a>
            <a href="/news" class="hover:text-yellow-400 transition">Klub</a>
            <a href="/contact" class="hover:text-yellow-400 transition">Kontakt</a>
        </div>

        <div class="flex space-x-3">

            <a href="#" class="flex items-center gap-2 bg-yellow-400 text-black px-4 py-2 rounded font-semibold hover:bg-yellow-300 transition">
                <i data-lucide="ticket"></i>
                Bilety
            </a>

            <a href="#" class="flex items-center gap-2 border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold hover:bg-yellow-400 hover:text-black transition">
                <i data-lucide="shopping-cart"></i>
                Sklep
            </a>

            <a href="#" class="flex items-center gap-2 border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold hover:bg-yellow-400 hover:text-black transition">
                <i data-lucide="graduation-cap"></i>
                Akademia
            </a>

        </div>

    </div>
</nav>
