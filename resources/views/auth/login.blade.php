<x-guest-layout>
    <h1 class="text-2xl font-bold text-center mb-2">Logowanie</h1>
    <p class="text-sm text-gray-600 text-center mb-6">Zaloguj się lub utwórz konto, aby przejść do panelu /account.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Hasło" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Zapamiętaj mnie</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Nie pamiętasz hasła?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Zaloguj
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-gray-600 mt-4">
            Nie masz konta?
            <a href="{{ route('register') }}" class="underline hover:text-gray-900">Zarejestruj się</a>
        </p>
    </form>
</x-guest-layout>
