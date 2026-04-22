<nav class="bg-black text-white sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <!-- LOGO -->
        <div class="text-2xl font-bold">
            ETB 🏀
        </div>

        <!-- MENU -->
        <div class="space-x-6 hidden md:flex">
            <a href="/" class="hover:text-gray-300">Home</a>
            <a href="/team" class="hover:text-gray-300">Klub</a>
            <a href="/schedule" class="hover:text-gray-300">Terminarz</a>
            <a href="/news" class="hover:text-gray-300">News</a>
            <a href="/contact" class="hover:text-gray-300">Kontakt</a>
        </div>

        <!-- PRAWA STRONA -->
        <div>
            @auth
                <a href="/dashboard" class="bg-white text-black px-4 py-2 rounded">
                    Panel
                </a>
            @else
                <a href="/login" class="bg-white text-black px-4 py-2 rounded">Zaloguj
                </a>
            @endauth
        </div>

    </div>
</nav>
