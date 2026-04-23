<x-app-layout>
    @php
        use App\Models\User;

        $role = $user->role;
        $isAdmin = $role === User::ROLE_ADMIN;
        $isEmployee = $role === User::ROLE_EMPLOYEE;
        $isAthlete = $role === User::ROLE_ATHLETE;
        $isFan = $role === User::ROLE_FAN;
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Konto') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-black min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-4 gap-6">
                <aside class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 h-fit">
                    <p class="text-sm uppercase tracking-widest text-zinc-400">Konto</p>
                    <p class="text-lg font-semibold mt-2">{{ $user->name }}</p>
                    <p class="text-sm text-zinc-400">Rola: {{ strtoupper($role) }}</p>

                    <nav class="mt-6 space-y-2 text-sm">
                        <a href="#profil" class="block px-3 py-2 rounded bg-zinc-800 text-zinc-100">Informacje o koncie</a>
                        <a href="#bezpieczenstwo" class="block px-3 py-2 rounded hover:bg-zinc-800">Hasło i bezpieczeństwo</a>
                        <a href="#akcje" class="block px-3 py-2 rounded hover:bg-zinc-800">Interakcje roli</a>
                        <a href="#ustawienia" class="block px-3 py-2 rounded hover:bg-zinc-800">Ustawienia konta</a>
                    </nav>
                </aside>

                <div class="lg:col-span-3 space-y-6">
                    <section id="profil" class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-1">Informacje o koncie</h3>
                        <p class="text-sm text-zinc-400 mb-6">Dane profilu i adres e-mail.</p>
                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </section>

                    <section id="bezpieczenstwo" class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-1">Hasło i bezpieczeństwo</h3>
                        <p class="text-sm text-zinc-400 mb-6">Zmiana hasła konta użytkownika.</p>
                        <div class="max-w-2xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </section>

                    <section id="akcje" class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-4">Interakcje dostępne dla Twojej roli</h3>

                        @if ($isAdmin || $isEmployee)
                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <a href="{{ route('admin.matches.create') }}" class="rounded-lg border border-yellow-400 bg-yellow-400/10 p-4 hover:bg-yellow-400/20 transition">
                                    <p class="font-semibold">➕ Dodawanie meczów</p>
                                    <p class="text-sm text-zinc-300 mt-1">Panel dodawania meczów spójny z interfejsem serwisu.</p>
                                </a>
                                <div class="rounded-lg border border-zinc-700 p-4">
                                    <p class="font-semibold">📰 Dodawanie artykułów</p>
                                    <p class="text-sm text-zinc-400 mt-1">Miejsce pod moduł publikacji newsów (wdrożenie etapami).</p>
                                </div>
                                <div class="rounded-lg border border-zinc-700 p-4">
                                    <p class="font-semibold">📅 Zarządzanie terminarzem</p>
                                    <p class="text-sm text-zinc-400 mt-1">Sekcja przewidziana pod rozbudowę harmonogramu meczów.</p>
                                </div>
                                <div class="rounded-lg border border-zinc-700 p-4">
                                    <p class="font-semibold">🛒 Przyszłe elementy sklepu</p>
                                    <p class="text-sm text-zinc-400 mt-1">Placeholder pod przyszłe operacje modułu sklepu.</p>
                                </div>
                            </div>
                        @endif

                        @if ($isAdmin)
                            <div class="rounded-lg border border-zinc-700 p-4">
                                <p class="font-semibold">👥 Zarządzanie rolami użytkowników</p>
                                <p class="text-sm text-zinc-400 mt-1">Administrator może nadawać role graczom i użytkownikom (endpoint aktywny: <code>PATCH /admin/users/{'{user}'}/role</code>).</p>
                            </div>
                        @endif

                        @if ($isAthlete || $isFan)
                            <div class="rounded-lg border border-zinc-700 p-4 mb-4">
                                <p class="font-semibold">🙋 Konto osobiste</p>
                                <p class="text-sm text-zinc-400 mt-1">Dostępne akcje: zmiana imienia, nazwiska/nazwy, e-maila i hasła.</p>
                            </div>
                        @endif

                        @if (! $isAdmin && ! $isEmployee && ! $isAthlete && ! $isFan)
                            <p class="text-zinc-400">Brak dodatkowych akcji dla tej roli.</p>
                        @endif
                    </section>

                    <section id="ustawienia" class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-1">Podstawowe ustawienia konta</h3>
                        <p class="text-sm text-zinc-400">Sekcja celowo pozostawiona jako placeholder — gotowa pod kolejne ustawienia.</p>
                    </section>

                    <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-4">Usunięcie konta</h3>
                        <div class="max-w-2xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
