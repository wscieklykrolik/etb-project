@extends('layouts.app')

@section('content')

    <div class="py-12 bg-gray-100 min-h-screen text-gray-900">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-12">

                <!-- SIDEBAR -->
                <aside class="lg:col-span-4">
                    <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Twoje konto</h3>

                        <p><strong>Imię:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Rola:</strong> {{ strtoupper($user->role) }}</p>
                    </div>
                </aside>

                <!-- MAIN -->
                <main class="lg:col-span-8 space-y-6">

                    <!-- PROFILE INFO -->
                    <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Dane profilu</h3>

                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <!-- PASSWORD -->
                    <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Zmiana hasła</h3>

                        <div class="max-w-2xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <!-- ADMIN -->
                    @if ($isAdmin)
                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Zarządzanie użytkownikami</h3>

                            @if (session('status') === 'role-updated')
                                <p class="text-green-600 mb-4">Rola została zaktualizowana</p>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm border">
                                    <thead class="bg-gray-200">
                                    <tr>
                                        <th class="p-2 text-left">Imię</th>
                                        <th class="p-2 text-left">Email</th>
                                        <th class="p-2 text-left">Rola</th>
                                        <th class="p-2 text-left">Akcja</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($users as $managedUser)
                                        <tr class="border-t">
                                            <td class="p-2">{{ $managedUser->name }}</td>
                                            <td class="p-2">{{ $managedUser->email }}</td>
                                            <td class="p-2">{{ strtoupper($managedUser->role) }}</td>
                                            <td class="p-2">
                                                <form method="POST" action="{{ route('admin.users.role.update', $managedUser) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <select name="role" class="border rounded px-2 py-1">
                                                        @foreach ($availableRoles as $role)
                                                            <option value="{{ $role }}" @selected($managedUser->role === $role)>
                                                                {{ strtoupper($role) }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <button type="submit" class="ml-2 px-3 py-1 bg-yellow-500 text-black rounded">
                                                        Zapisz
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- EMPLOYEE -->
                    @if ($isEmployee)
                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Panel pracownika</h3>

                            <div class="grid md:grid-cols-3 gap-4">
                                <a href="{{ route('players.create') }}" class="p-4 border rounded hover:bg-gray-100">
                                    ➕ Dodaj zawodnika
                                </a>
                                <a href="{{ route('news.create') }}" class="p-4 border rounded hover:bg-gray-100">
                                    📰 Dodaj news
                                </a>
                                <a href="{{ route('matches.create') }}" class="p-4 border rounded hover:bg-gray-100">
                                    ⚽ Dodaj mecz
                                </a>
                            </div>
                        </div>
                    @endif


                    @if ($isAdmin || $isEmployee)
                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Latest Matches</h3>

                            <div class="space-y-2 text-sm">
                                @forelse ($matches as $match)
                                    <div>
                                        {{ $match->home_team }} vs {{ $match->away_team }}
                                    </div>
                                @empty
                                    <div>Brak meczów.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Latest News</h3>

                            <div class="space-y-2 text-sm">
                                @forelse ($news as $article)
                                    <div>
                                        {{ $article->title }}
                                    </div>
                                @empty
                                    <div>Brak newsów.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Latest Players</h3>

                            <div class="space-y-2 text-sm">
                                @forelse ($players as $player)
                                    <div>
                                        {{ $player->first_name }} {{ $player->last_name }}
                                    </div>
                                @empty
                                    <div>Brak zawodników.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <!-- ATHLETE -->
                    @if ($isAthlete)
                        <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Panel zawodnika</h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Dane zawodnika:</p>

                                @if ($athleteProfile)
                                    <pre class="mt-2 text-xs bg-gray-100 p-3 rounded overflow-x-auto">
{{ json_encode($athleteProfile->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                </pre>
                                @else
                                    <p class="text-sm mt-2">Brak danych zawodnika</p>
                                @endif
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                <div class="border p-4 rounded">Treningi (w przyszłości)</div>
                                <div class="border p-4 rounded">Dostępność</div>
                                <div class="border p-4 rounded">Statystyki</div>
                            </div>
                        </div>
                    @endif

                    <!-- DELETE -->
                    <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Usuń konto</h3>

                        <div class="max-w-2xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                </main>
            </div>
        </div>
    </div>

@endsection
