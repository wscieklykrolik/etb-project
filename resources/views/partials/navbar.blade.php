<!-- GÓRNY PASEK -->
<div class="bg-black text-white text-sm py-2 px-6 flex justify-between items-center border-b border-yellow-500">

    <div class="font-semibold text-yellow-400">
        ETB - OFICJALNA STRONA
    </div>

    <div class="flex items-center space-x-4">
        <span class="hover:text-yellow-400 cursor-pointer">FB</span>
        <span class="hover:text-yellow-400 cursor-pointer">IG</span>
        <span class="hover:text-yellow-400 cursor-pointer">YT</span>

        @auth
            <a href="/dashboard" class="hover:text-yellow-400">Panel</a>
        @else
            <a href="/login" class="hover:text-yellow-400">Login</a>
        @endauth
    </div>
</div>

<!-- GŁÓWNY NAVBAR -->
<nav class="bg-black text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <!-- LOGO -->
        <div class="text-3xl font-bold text-yellow-400">
            ETB 🏀
        </div>

        <!-- MENU -->
        <div class="space-x-8 hidden md:flex text-lg">
            <a href="/" class="hover:text-yellow-400">Aktualności</a>
            <a href="/team" class="hover:text-yellow-400">Drużyna</a>
            <a href="/schedule" class="hover:text-yellow-400">Rozgrywki</a>
            <a href="/news" class="hover:text-yellow-400">Klub</a>
            <a href="/contact" class="hover:text-yellow-400">Kontakt</a>
        </div>

        <!-- CTA -->
        <div class="flex space-x-3">
            <a href="#" class="bg-yellow-400 text-black px-4 py-2 rounded font-semibold hover:bg-yellow-300 transition">
                Bilety
            </a>
            <a href="#" class="border border-yellow-400 text-yellow-400 px-4 py-2 rounded font-semibold hover:bg-yellow-400 hover:text-black transition">
                Sklep
            </a>
        </div>

    </div>
</nav>
