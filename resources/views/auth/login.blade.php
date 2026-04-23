<x-guest-layout>
    <x-auth-session-status class="mb-4 text-sm text-black" :status="session('status')" />

    <h1 class="text-2xl font-bold text-black">Zaloguj się</h1>
    <p class="mt-1 text-sm text-black/80">Nie masz konta? <a href="{{ route('register') }}" class="font-semibold underline">Zarejestruj się w tym samym panelu</a>.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" class="text-black font-semibold" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-700" />
        </div>

        <div>
            <x-input-label for="password" class="text-black font-semibold" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-700" />
        </div>

        <div class="block">
            <label for="remember_me" class="inline-flex items-center text-black">
                <input id="remember_me" type="checkbox" class="rounded border-black text-black shadow-sm focus:ring-black" name="remember">
                <span class="ms-2 text-sm">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-black hover:text-zinc-900 rounded-md focus:outline-none focus:ring-2 focus:ring-black" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
