<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel użytkownika
        </h2>
    </x-slot>

    <div class="py-12 bg-black min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 border border-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-zinc-200">
                <p class="mb-4">Dashboard został uproszczony — główne zarządzanie kontem znajduje się teraz w zakładce <strong>Konto</strong>.</p>
                <a href="{{ route('profile.edit') }}" class="inline-flex bg-yellow-400 text-black font-semibold px-4 py-2 rounded hover:bg-yellow-300 transition">Przejdź do konta</a>
            </div>
        </div>
    </div>
</x-app-layout>
