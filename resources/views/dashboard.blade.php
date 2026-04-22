<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel użytkownika
        </h2>
    </x-slot>

    <div class="py-12 bg-black min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <p class="mb-6 text-gray-700">
                    Jesteś zalogowany.
                </p>

                @if(auth()->user()->is_admin)

                    <div class="grid md:grid-cols-2 gap-6">

                        <a href="/admin/matches/create"
                           class="bg-yellow-400 text-black p-6 rounded-xl font-bold text-lg hover:bg-yellow-300 transition shadow">
                            ➕ Dodaj nowy mecz
                        </a>

                    </div>

                @else

                    <p class="text-red-500 font-semibold">
                        Brak uprawnień administratora.
                    </p>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>
