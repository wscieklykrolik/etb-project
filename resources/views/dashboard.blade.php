<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Konto użytkownika
        </h2>
    </x-slot>

    @php
        $roleLabels = [
            \App\Models\User::ROLE_FAN => 'Fan',
            \App\Models\User::ROLE_ADMIN => 'Admin',
            \App\Models\User::ROLE_EMPLOYEE => 'Pracownik',
            \App\Models\User::ROLE_ATHLETE => 'Zawodnik',
            \App\Models\User::ROLE_TRAINER => 'Trener',
        ];

        $currentUser = auth()->user();
    @endphp

    <div class="py-10 bg-black min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-4 gap-6">
            <aside class="bg-zinc-900 text-white rounded-2xl border border-zinc-800 p-5 lg:col-span-1 h-fit">
                <h3 class="text-xl font-bold mb-4">Konto</h3>
                <ul class="space-y-2 text-sm text-zinc-300">
                    <li class="bg-zinc-800 rounded px-3 py-2">Ustawienia</li>
                    <li class="px-3 py-2">Bezpieczeństwo</li>
                    <li class="px-3 py-2">Historia zgód</li>
                </ul>
            </aside>

            <section class="lg:col-span-3 space-y-6">
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 text-white">
                    <h1 class="text-3xl font-bold mb-2">Ustawienia konta</h1>
                    <p class="text-zinc-300">Zarządzaj danymi konta, uprawnieniami i bezpieczeństwem.</p>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 text-white grid md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <div class="h-56 bg-white text-zinc-500 border-2 border-dashed border-zinc-300 rounded-xl flex items-center justify-center text-center px-4">
                            Tu trzeba wstawić zdjęcie.<br>Tu będzie zdjęcie drużyny.
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-zinc-400">Rola w zespole</p>
                            <p class="text-lg font-semibold">{{ $roleLabels[$currentUser->role] ?? $currentUser->role }}</p>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-zinc-400">Imię</p>
                                <p>{{ $currentUser->first_name ?: 'Brak danych' }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-400">Nazwisko</p>
                                <p>{{ $currentUser->last_name ?: 'Brak danych' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-zinc-400">Adres e-mail</p>
                                <p>{{ $currentUser->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">Dozwolone akcje dla Twojej roli</h3>
                    @if(in_array($currentUser->role, [\App\Models\User::ROLE_FAN, \App\Models\User::ROLE_ATHLETE], true))
                        <ul class="list-disc ml-6 text-zinc-300 space-y-1">
                            <li>Podgląd danych konta i e-maila.</li>
                            <li>Edycja hasła oraz podstawowych danych profilu.</li>
                            <li>Tylko odczyt aktualności, galerii i treści klubowych.</li>
                            @if($currentUser->role === \App\Models\User::ROLE_ATHLETE)
                                <li>Dodatkowy dostęp do informacji akademii dedykowanych zawodnikom.</li>
                            @endif
                        </ul>
                    @elseif($currentUser->role === \App\Models\User::ROLE_TRAINER)
                        <ul class="list-disc ml-6 text-zinc-300 space-y-1">
                            <li>Dostęp do panelu akademii i treści szkoleniowych.</li>
                            <li>Podgląd danych konta i bezpieczeństwa.</li>
                        </ul>
                    @else
                        <ul class="list-disc ml-6 text-zinc-300 space-y-1">
                            <li>Zarządzanie treściami: aktualności, wideo, galerie, historia, władze klubu, obiekt i pozostałe sekcje.</li>
                            <li>Przygotowanie panelu sklepu pod zarządzanie produktami (ubrania i merchandising).</li>
                            <li>Pełny dostęp edycyjny do modułów administracyjnych zgodnie z rolą.</li>
                        </ul>
                    @endif
                </div>

                @if($currentUser->isAdmin())
                    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 text-white">
                        <h3 class="text-xl font-bold mb-4">Administracja rolami (po e-mailu)</h3>
                        <p class="text-zinc-300 mb-4">Admin może nadawać role użytkownikom. W tym: fan, admin, pracownik, zawodnik, trener.</p>
                        <div class="space-y-3">
                            @foreach($users as $user)
                                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="grid md:grid-cols-5 gap-3 items-center bg-zinc-800 rounded-lg p-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="md:col-span-2">
                                        <p class="font-semibold">{{ $user->email }}</p>
                                        <p class="text-xs text-zinc-400">{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Brak imienia i nazwiska' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <select name="role" class="w-full rounded-md border-zinc-600 bg-zinc-900 text-white">
                                            @foreach(\App\Models\User::roles() as $role)
                                                <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="bg-yellow-400 text-black rounded-md px-3 py-2 font-semibold">Zapisz</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
